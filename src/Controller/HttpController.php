<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\jsonrpc\JsonRpcHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpController extends ControllerBase {

  protected $handler;

  public function __construct(JsonRpcHandlerInterface $handler) {
    $this->handler = $handler;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('jsonrpc.handler'));
  }

  public function resolve(Request $request) {
    // @todo Actually denormalize this well.
    $decoded = json_decode((string) $request->getContent());
    if (is_array($decoded)) {
      $responses = $this->handler->batch($decoded);
    }
    else {
      $response = $this->handler->execute($decoded);
    }
    return empty($responses) && !isset($response)
      ? Response::create(NULL, Response::HTTP_NO_CONTENT)
      : JsonResponse::create(empty($responses) ? $response : $responses);
  }

}