<?php

namespace Drupal\jsonrpc;


use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController {

  /**
   * Processes the RPC.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function handle(Request $request) {
    // TODO: Create custom exceptions for errors handled by the subscriber.
    if ($request->getMethod() !== Request::METHOD_POST) {
      return new Response('{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Invalid Request"}, "id": null}');
    }
    $body = $request->getContent();
    if (!$body || !($parsed_body = Json::decode($body))) {
      return new Response('{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Invalid Request"}, "id": null}');
    }
    // TODO: Inject plugin manager properly.
    $rpc_request = RpcRequest::create($parsed_body, \Drupal::service('plugin.manager.rpc_endpoint'));
    return new Response($rpc_request->getEndpoint()->execute());
  }

}
