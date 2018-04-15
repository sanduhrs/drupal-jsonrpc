<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\jsonrpc\JsonRpcHandlerInterface;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\Request as RpcRequest;
use Drupal\jsonrpc\Object\Response as RpcResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class HttpController extends ControllerBase {

  /**
   * The RPC handler service.
   *
   * @var \Drupal\jsonrpc\JsonRpcHandlerInterface
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
   * @param \Drupal\jsonrpc\JsonRpcHandlerInterface $handler
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   */
  public function __construct(JsonRpcHandlerInterface $handler, SerializerInterface $serializer) {
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
      $rpc_request = $this->serializer->deserialize($request->getContent(FALSE), RpcRequest::class, 'rpc_json');
    }
    catch (\Exception $e) {
      $rpc_response = new RpcResponse(
        $this->handler->supportedVersion(),
        NULL,
        NULL,
        Error::parseError($e->getMessage())
      );
      return new Response($rpc_response, Response::HTTP_BAD_REQUEST);
    }
    if (is_array($rpc_request)) {
      $responses = $this->handler->batch($rpc_request);
    }
    else {
      $response = $this->handler->execute($rpc_request);
    }
    return empty($responses) && !isset($response)
      ? Response::create(NULL, Response::HTTP_NO_CONTENT)
      : JsonResponse::create(empty($responses) ? $response : $responses);
  }

}