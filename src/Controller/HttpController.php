<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class HttpController extends ControllerBase {

  protected $handler;

  public function __construct(JsonRpcHandlerInterface $handler) {
    $this->handler = $handler;
  }

  public static function create(ContainerInterface $container) {
    return parent::create($container->get('jsonrpc.handler'));
  }

  public function resolve(Request $request) {
    // @todo Actually denormalize this well.
    $jsonrpc_requests = json_decode((string) $request->getContent());
    if (is_array($jsonrpc_requests)) {
      $jsonrpc_responses = array_filter(array_map(function ($jsonrpc_request) {
        return $this->getJsonRpcResponse($jsonrpc_request);
      }, $jsonrpc_requests));
      return empty($jsonrpc_responses)
        ? Response::create(NULL, Response::HTTP_NO_CONTENT)
        : JsonResponse::create($jsonrpc_responses);
    }
    else {
      return ($jsonrpc_response = $this->getJsonRpcResponse($jsonrpc_requests))
        ? Response::create(NULL, Response::HTTP_NO_CONTENT)
        : JsonResponse::create($jsonrpc_response);
    }
  }

  protected function getJsonRpcResponse($jsonrpc_request) {
    $jsonrpc_response = ['jsonrpc' => '2.0'];
    try {
      $jsonrpc_response['result'] = $this->execute($jsonrpc_request);
    }
    catch (\Exception $e) {
      $jsonrpc_response['error'] = [
        'code' => -32603,
        'message' => $e->getMessage(),
      ];
    }
    if (isset($request->id)) {
      $jsonrpc_response['id'] = $jsonrpc_request->id;
      return $jsonrpc_response;
    }
    return NULL;
  }

}