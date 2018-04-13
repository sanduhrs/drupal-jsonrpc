<?php

namespace Drupal\jsonrpc;

interface JsonRpcHandlerInterface {

  /**
   * Executes a remote procedure call.
   *
   * @param array $request
   *   The JSON-RPC request.
   *
   * @return array
   *   The JSON-RPC response.
   */
  public function execute(array $request);

  /**
   * Executes a batch of remote procedure calls.
   *
   * @param array $requests
   *   The JSON-RPC requests.
   *
   * @return array
   *   The JSON-RPC responses, if any.
   */
  public function execute(array $request);

}