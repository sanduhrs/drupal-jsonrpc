<?php

namespace Drupal\jsonrpc;


use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles all the RPC endpoints.
 */
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
    // TODO: Allow passing the JSON object encoded in the URL as a query string parameter. Then make sure we can leverage page cache in the appropriate situations.
    if ($request->getMethod() !== Request::METHOD_POST) {
      return new Response('{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Invalid Request"}, "id": null}');
    }
    $body = $request->getContent();
    if (!$body || !($parsed_body = Json::decode($body))) {
      return new Response('{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Invalid Request"}, "id": null}');
    }
    // TODO: Inject plugin manager properly.
    $rpc_request = RpcRequest::create($parsed_body, \Drupal::service('plugin.manager.rpc_endpoint'));
    $result = $rpc_request->getEndpoint()->execute();
    $output = [
      'jsonrpc' => '2.0',
      'result' => $result,
      'id' => $rpc_request->getId(),
    ];

    // If there is no ID we don't provide a response. This is called
    // "notification" in the JSON-RPC spec.
    return $rpc_request->getId()
      ? new JsonResponse($output)
      : new JsonResponse();
  }

}
