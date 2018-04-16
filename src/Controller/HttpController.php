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
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response.
   */
  public function resolve(Request $request) {
    /* @var \Drupal\jsonrpc\Object\Request $rpc_request */
    try {
      $content = $request->getContent(FALSE);
      $context = [
        'jsonrpc' => $this->handler::supportedVersion(),
        'service_definition' => $this->handler,
      ];
      $rpc_request = $this->serializer->deserialize($content, RpcRequest::class, 'rpc_json', $context);
    }
    catch (JsonRpcException|\Exception $e) {
      if (!$e instanceof JsonRpcException) {
        $id = (isset($content) && is_object($content) && isset($content->id)) ? $content->id : FALSE;
        $e = JsonRpcException::fromPrevious($e, $id);
      }
      return CacheableJsonResponse::create($e->getResponse(), Response::HTTP_BAD_REQUEST)->addCacheableDependency($e->getResponse());
    }
    try {
      if (is_array($rpc_request)) {
        $responses = $this->handler->batch($rpc_request);
      }
      else {
        $response = $this->handler->execute($rpc_request);
      }
    }
    catch (JsonRpcException $e) {
      return CacheableJsonResponse::create($e->getResponse(), Response::HTTP_INTERNAL_SERVER_ERROR)->addCacheableDependency($e->getResponse());
    }
    if (empty($responses) && !isset($response)) {
      return CacheableJsonResponse::create(NULL, Response::HTTP_NO_CONTENT);
    }
    $serialized = $this->serializer->serialize(empty($responses) ? $response : $responses, 'rpc_json', [
      ResponseNormalizer::RESPONSE_VERSION_KEY => $this->handler::supportedVersion(),
    ]);
    return empty($responses)
      ? CacheableJsonResponse::fromJsonString($serialized)->addCacheableDependency($response)
      : array_reduce($responses, function (CacheableResponseInterface $http_response, $response) {
        return $http_response->addCacheableDependency($response);
      }, CacheableJsonResponse::fromJsonString($serialized));
  }

}