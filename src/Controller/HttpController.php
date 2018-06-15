<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\Shaper\RpcRequestFactory;
use Drupal\jsonrpc\Shaper\RpcResponseNormalizer;
use Shaper\Util\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpController extends ControllerBase {

  /**
   * The RPC handler service.
   *
   * @var \Drupal\jsonrpc\HandlerInterface
   */
  protected $handler;

  /**
   * The JSON Schema validator service.
   *
   * @var \JsonSchema\Validator
   */
  protected $validator;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * HttpController constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface
   */
  public function __construct(ContainerInterface $container) {
    $this->handler = $container->get('jsonrpc.handler');
    $this->validator = $container->get('jsonrpc.schema_validator');
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
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
    $version = $this->handler->supportedVersion();
    try {
      if ($http_request->getMethod() === Request::METHOD_POST) {
        $content = Json::decode($http_request->getContent(FALSE));
      }
      else if ($http_request->getMethod() === Request::METHOD_GET) {
        $content = Json::decode($http_request->query->get('query'));
      }
      $context = new Context([
        RpcRequestFactory::REQUEST_VERSION_KEY => $version,
      ]);
      $factory = new RpcRequestFactory($this->handler, $this->container, $this->validator);
      return $factory->transform($content, $context);
    }
    catch (\Exception $e) {
      $id = (isset($content) && is_object($content) && isset($content->id)) ? $content->id : FALSE;
      throw JsonRpcException::fromPrevious($e, $id, $version);
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
    $context = new Context([
      RpcResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler->supportedVersion(),
      RpcRequestFactory::REQUEST_IS_BATCH_REQUEST => $is_batched_response,
    ]);
    // This following is needed to prevent the serializer from using array
    // indices as JSON object keys like {"0": "foo", "1": "bar"}.
    $data = array_values($rpc_responses);
    $normalizer = new RpcResponseNormalizer($this->validator);
    return Json::encode($normalizer->transform($data, $context));
  }

  /**
   * @param \Drupal\jsonrpc\Exception\JsonRpcException $e
   * @param int $status
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   */
  protected function exceptionResponse(JsonRpcException $e, $status = Response::HTTP_INTERNAL_SERVER_ERROR) {
    $context = new Context([
      RpcResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler->supportedVersion(),
      RpcRequestFactory::REQUEST_IS_BATCH_REQUEST => FALSE,
    ]);
    $normalizer = new RpcResponseNormalizer($this->validator);
    $rpc_response = $e->getResponse();
    $serialized = Json::encode($normalizer->transform([$rpc_response], $context));
    $response = CacheableJsonResponse::fromJsonString($serialized, $status);
    return $response->addCacheableDependency($rpc_response);
  }

}
