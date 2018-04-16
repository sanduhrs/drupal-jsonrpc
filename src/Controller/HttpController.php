<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\Normalizer\ResponseNormalizer;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\Request as RpcRequest;
use Drupal\jsonrpc\Object\Response as RpcResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class HttpController extends ControllerBase {

  /**
   * The RPC handler service.
   *
   * @var \Drupal\jsonrpc\HandlerInterface
   */
  protected $handler;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * HttpController constructor.
   *
   * @param \Drupal\jsonrpc\HandlerInterface $handler
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   */
  public function __construct(HandlerInterface $handler, SerializerInterface $serializer) {
    $this->handler = $handler;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('jsonrpc.handler'), $container->get('serializer'));
  }

  /**
   * Resolves an RPC request over HTTP.
   *
   * @param \Symfony\Component\HttpFoundation\Request $http_request
   *   The HTTP request.
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   *   The HTTP response.
   */
  public function resolve(Request $http_request) {
    // Map the HTTP request to an RPC request.
    try {
      $rpc_request = $this->getRpcRequest($http_request);
    } catch (JsonRpcException $e) {
      return $this->exceptionResponse($e, Response::HTTP_BAD_REQUEST);
    }

    // Execute the RPC request and get the RPC response.
    try {
      $rpc_response = $this->getRpcResponse($rpc_request);
    } catch (JsonRpcException $e) {
      return $this->exceptionResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // If no RPC response(s) were generated (happens if all of the request(s)
    // were notifications), then return a 204 HTTP response.
    if (is_null($rpc_response) || empty($rpc_response)) {
      return CacheableJsonResponse::create(NULL, Response::HTTP_NO_CONTENT);
    }

    // Map the RPC response(s) to an HTTP response.
    return $this->getHttpResponse($rpc_response);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $http_request
   *
   * @return \Drupal\jsonrpc\Object\Request|\Drupal\jsonrpc\Object\Request[]
   *   The JSON-RPC request or requests.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function getRpcRequest(Request $http_request) {
    try {
      $content = $http_request->getContent(FALSE);
      $context = [
        'jsonrpc' => $this->handler::supportedVersion(),
        'service_definition' => $this->handler,
      ];
      /* @var \Drupal\jsonrpc\Object\Request|\Drupal\jsonrpc\Object\Request[] $deserialized */
      $deserialized = $this->serializer->deserialize($content, RpcRequest::class, 'rpc_json', $context);
      return $deserialized;
    }
    catch (\Exception $e) {
      if (!$e instanceof JsonRpcException) {
        $id = (isset($content) && is_object($content) && isset($content->id)) ? $content->id : FALSE;
        throw JsonRpcException::fromPrevious($e, $id);
      }
      throw $e;
    }
  }


  /**
   * @param \Drupal\jsonrpc\Object\Request $rpc_request
   *
   * @return \Drupal\jsonrpc\Object\Response|\Drupal\jsonrpc\Object\Response[]|null $rpc_response
   *   The JSON-RPC response(s). NULL when the RPC request contains only
   *   notifications.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function getRpcResponse($rpc_request) {
    if (is_array($rpc_request)) {
      return ($rpc_responses = $this->handler->batch($rpc_request)) && !empty($rpc_responses)
        ? $rpc_responses
        : NULL;
    }
    return ($rpc_response = $this->handler->execute($rpc_request))
      ? $rpc_response
      : NULL;
  }

  /**
   * Map RPC response(s) to an HTTP response.
   *
   * @param \Drupal\jsonrpc\Object\Response|\Drupal\jsonrpc\Object\Response[] $rpc_response
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   *   The cacheable HTTP version of the RPC response(s).
   */
  protected function getHttpResponse($rpc_response) {
    $serialized = $this->serializeRpcResponse($rpc_response);
    $http_response = CacheableJsonResponse::fromJsonString($serialized, Response::HTTP_OK);
    // Adds the cacheability information of the RPC response(s) to the HTTP
    // response.
    return is_array($rpc_response)
      ? array_reduce($rpc_response, function (CacheableResponseInterface $http_response, $response) {
        return $http_response->addCacheableDependency($response);
      }, $http_response)
      : $http_response->addCacheableDependency($rpc_response);
  }

  /**
   * @param \Drupal\jsonrpc\Object\Response|\Drupal\jsonrpc\Object\Response[] $rpc_response
   *
   * @return string
   *   The serialized JSON-RPC response body.
   */
  protected function serializeRpcResponse($rpc_response) {
    $context = [
      ResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler::supportedVersion(),
    ];
    // This following is needed to prevent the serializer from using array
    // indices as JSON object keys like {"0": "foo", "1": "bar"}.
    $data = is_array($rpc_response) ? array_values($rpc_response) : $rpc_response;
    return $this->serializer->serialize($data, 'rpc_json', $context);
  }

  /**
   * @param \Drupal\jsonrpc\Exception\JsonRpcException $e
   * @param int $status
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   */
  protected function exceptionResponse(JsonRpcException $e, $status = Response::HTTP_INTERNAL_SERVER_ERROR) {
    $context = [
      ResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler::supportedVersion(),
    ];
    $serialized = $this->serializer->serialize($e->getResponse(), 'rpc_json', $context);
    $response = CacheableJsonResponse::fromJsonString($serialized, $status);
    return $response->addCacheableDependency($e->getResponse());
  }

}