<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\Normalizer\RequestNormalizer;
use Drupal\jsonrpc\Normalizer\ResponseNormalizer;
use Drupal\jsonrpc\Object\Request as RpcRequest;
use Drupal\jsonrpc\Object\Response as RpcResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
      $rpc_requests = $this->getRpcRequests($http_request);
    } catch (JsonRpcException $e) {
      return $this->exceptionResponse($e, Response::HTTP_BAD_REQUEST);
    }

    // Execute the RPC request and get the RPC response.
    try {
      $rpc_responses = $this->getRpcResponses($rpc_requests);

      // If no RPC response(s) were generated (happens if all of the request(s)
      // were notifications), then return a 204 HTTP response.
      if (empty($rpc_responses)) {
        return CacheableJsonResponse::create(NULL, Response::HTTP_NO_CONTENT);
      }

      // Map the RPC response(s) to an HTTP response.
      $is_batched_response = count($rpc_requests) !== 1 || $rpc_requests[0]->isInBatch();
      return $this->getHttpResponse($rpc_responses, $is_batched_response);
    }
    catch (JsonRpcException $e) {
      return $this->exceptionResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $http_request
   *
   * @return \Drupal\jsonrpc\Object\Request[]
   *   The JSON-RPC request or requests.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function getRpcRequests(Request $http_request) {
    try {
      $content = $http_request->getContent(FALSE);
      $context = [
        RequestNormalizer::REQUEST_VERSION_KEY => $this->handler->supportedVersion(),
        'service_definition' => $this->handler,
      ];
      /* @var \Drupal\jsonrpc\Object\Request[] $deserialized */
      return $this->serializer->deserialize($content, RpcRequest::class, 'json', $context);
      return $deserialized;
    }
    catch (\Exception $e) {
      $id = (isset($content) && is_object($content) && isset($content->id)) ? $content->id : FALSE;
      throw JsonRpcException::fromPrevious($e, $id, $this->handler->supportedVersion());
    }
  }


  /**
   * @param \Drupal\jsonrpc\Object\Request[] $rpc_requests
   *
   * @return \Drupal\jsonrpc\Object\Response[]|null
   *   The JSON-RPC response(s). NULL when the RPC request contains only
   *   notifications.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function getRpcResponses($rpc_requests) {
    $rpc_responses = $this->handler->batch($rpc_requests);
    return empty($rpc_responses)
      ? NULL
      : $rpc_responses;
  }

  /**
   * Map RPC response(s) to an HTTP response.
   *
   * @param \Drupal\jsonrpc\Object\Response[] $rpc_responses
   * @param bool $is_batched_response
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   *   The cacheable HTTP version of the RPC response(s).
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function getHttpResponse($rpc_responses, $is_batched_response) {
    try {
      $serialized = $this->serializeRpcResponse($rpc_responses, $is_batched_response);
      $http_response = CacheableJsonResponse::fromJsonString($serialized, Response::HTTP_OK);
      // Adds the cacheability information of the RPC response(s) to the HTTP
      // response.
      return array_reduce($rpc_responses, function (CacheableResponseInterface $http_response, $response) {
        return $http_response->addCacheableDependency($response);
      }, $http_response);
    }
    catch (\Exception $e) {
      throw JsonRpcException::fromPrevious($e, FALSE, $this->handler->supportedVersion());
    }
  }

  /**
   * @param \Drupal\jsonrpc\Object\Response[] $rpc_responses
   * @param bool $is_batched_response
   *
   * @return string
   *   The serialized JSON-RPC response body.
   */
  protected function serializeRpcResponse($rpc_responses, $is_batched_response) {
    $context = [
      ResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler->supportedVersion(),
    ];
    // This following is needed to prevent the serializer from using array
    // indices as JSON object keys like {"0": "foo", "1": "bar"}.
    $data = $is_batched_response ? array_values($rpc_responses) : $rpc_responses[0];
    return $this->serializer->serialize($data, 'json', $context);
  }

  /**
   * @param \Drupal\jsonrpc\Exception\JsonRpcException $e
   * @param int $status
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   */
  protected function exceptionResponse(JsonRpcException $e, $status = Response::HTTP_INTERNAL_SERVER_ERROR) {
    $context = [
      ResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler->supportedVersion(),
    ];
    $serialized = $this->serializer->serialize($e->getResponse(), 'json', $context);
    $response = CacheableJsonResponse::fromJsonString($serialized, $status);
    return $response->addCacheableDependency($e->getResponse());
  }

}
