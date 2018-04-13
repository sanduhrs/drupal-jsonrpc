<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the RPC Endpoint plugin manager.
 */
class RpcEndpointManager extends DefaultPluginManager {

  /**
   * Constructs a new RpcEndpointManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/RpcEndpoint',
      $namespaces,
      $module_handler,
      'Drupal\jsonrpc\Plugin\RpcEndpointInterface',
      'Drupal\jsonrpc\Annotation\RpcEndpoint'
    );

    $this->alterInfo('jsonrpc_rpc_endpoint_info');
    $this->setCacheBackend($cache_backend, 'jsonrpc_rpc_endpoint_plugins');
  }

}
