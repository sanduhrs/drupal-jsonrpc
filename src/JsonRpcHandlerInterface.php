<?php

namespace Drupal\jsonrpc;

interface JsonRpcHandlerInterface {

  /**
   * Executes a remote procedure call.
   *
   * @param array $request
   *   The JSON-RPC request.
   *
   * @return array|NULL
   *   The JSON-RPC response, if any. Notifications have no response.
   */
  public function execute($request);

  /**
   * Executes a batch of remote procedure calls.
   *
   * @param array $requests
   *   The JSON-RPC requests.
   *
   * @return array
   *   The JSON-RPC responses, if any. Notifications are not returned.
   */
  public function batch(array $requests);

}