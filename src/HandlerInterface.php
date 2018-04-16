<?php

namespace Drupal\jsonrpc;

use Drupal\jsonrpc\Object\Request;

interface HandlerInterface {

  /**
   * Executes a remote procedure call.
   *
   * @param \Drupal\jsonrpc\Object\Request $request
   *   The JSON-RPC request.
   *
   * @return array|NULL
   *   The JSON-RPC response, if any. Notifications have no response.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
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
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public function batch(array $requests);

  /**
   * The supported JSON-RPC version.
   *
   * @return string
   */
  public static function supportedVersion();

  /**
   * The methods supported by the handler.
   *
   * @return \Drupal\jsonrpc\MethodInterface[]
   */
  public function supportedMethods();

  /**
   * Whether the given method is supported.
   *
   * @param string $name
   *   The method name for which support should be determined.
   *
   * @return bool
   *   Whether the handler supports the given method name.
   */
  public function supportsMethod($name);

  /**
   * Gets a method definition by method name.
   *
   * @param string $name
   *   The method name for which support should be determined.
   *
   * @return \Drupal\jsonrpc\MethodInterface|null
   *   The method definition.
   */
  public function getMethod($name);

}