<?php

namespace Drupal\jsonrpc;

use Drupal\jsonrpc\Plugin\RpcEndpointManager;

class RpcRequest {

  /**
   * @var \Drupal\jsonrpc\Plugin\RpcEndpointManager
   */
  protected $endpointManager;

  /**
   * @var \Drupal\jsonrpc\Plugin\RpcEndpointInterface
   */
  protected $endpoint;

  /**
   * @var string
   */
  protected $version;

  /**
   * @var string
   */
  protected $method;

  /**
   * @var \Drupal\jsonrpc\Plugin\RpcParameter[]
   */
  protected $params;

  /**
   * @var string
   */
  protected $id;

  public function __construct(RpcEndpointManager $endpoint_manager, $version, $method, $raw_params = [], $id = NULL) {
    $this->endpointManager = $endpoint_manager;
    $this->version = $version;
    $this->method = $method;
    // TODO: Create RpcParameter objects!
    $this->params = $raw_params;
    $this->id = $id;
  }

  /**
   * Request factory class.
   *
   * @param array $raw_body
   *   The parsed raw JSON.
   *
   * @return \Drupal\jsonrpc\RpcRequest
   *   The request object.
   *
   * @throws
   *   When there is an invalid request.
   */
  public static function create(array $raw_body, RpcEndpointManager $endpoint_manager) {
    // Validate the input request document against the schema for JSON RPC spec.
    // TODO: Validate against the schema.
    return new static(
      $endpoint_manager,
      $raw_body['jsonrpc'],
      $raw_body['method'],
      empty($raw_body['params']) ? [] : $raw_body['params'],
      empty($raw_body['id']) ? NULL : $raw_body['id']
    );
  }

  public function getEndpoint() {
    if (!$this->endpoint) {
      // TODO: Handle the plugin not found exception.
      $this->endpoint = $this->endpointManager->createInstance($this->method, ['params' => $this->params]);
    }
    return $this->endpoint;
  }

}
