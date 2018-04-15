<?php

namespace Drupal\jsonrpc;

use Drupal\jsonrpc\Object\Request;

interface JsonRpcHandlerInterface {

  /**
   * Executes a remote procedure call.
   *
   * @param \Drupal\jsonrpc\Object\Request $request
   *   The JSON-RPC request.
   *
   * @return array|NULL
   *   The JSON-RPC response, if any. Notifications have no response.
   */
  public function execute(Request $request);

  /**
   * Executes a batch of remote procedure calls.
   *
   * @param \Drupal\jsonrpc\Object\Request[] $requests
   *   The JSON-RPC requests.
   *
   * @return array
   *   The JSON-RPC responses, if any. Notifications are not returned.
   */
  public function batch(array $requests);

}