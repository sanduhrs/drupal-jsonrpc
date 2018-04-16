<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\jsonrpc\ServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JsonRpcServiceBase extends PluginBase implements ContainerFactoryPluginInterface {

  /**
   * The RPC request for the current invocation.
   *
   * @var \Drupal\jsonrpc\Object\Request
   */
  private $rpcRequest;

  /**
   * The RPC method definition for the current invocation.
   *
   * @var \Drupal\jsonrpc\Object\Request
   */
  private $methodDefinition;

  /**
   * JsonRpcPluginBase constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   */
  public function __construct(array $configuration, string $plugin_id, ServiceInterface $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->rpcRequest = $configuration[JsonRpcServiceManager::JSONRPC_REQUEST_KEY];
    $this->methodDefinition = $configuration[JsonRpcServiceManager::JSONRPC_REQUEST_METHOD_KEY];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * The RPC request for the current invocation.
   *
   * @return \Drupal\jsonrpc\Object\Request
   */
  protected function currentRequest() {
    return $this->rpcRequest;
  }

  /**
   * The RPC method definition for the current invocation.
   *
   * @return \Drupal\jsonrpc\MethodInterface
   */
  protected function methodDefinition() {
    return $this->rpcRequest;
  }

}