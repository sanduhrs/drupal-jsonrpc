<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides the JsonRpcService plugin plugin manager.
 *
 * @internal
 */
class JsonRpcServicePluginManager extends DefaultPluginManager {

  /**
   * Constructs a new HookPluginManager object.
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
      'Plugin/jsonrpc/Service',
      $namespaces,
      $module_handler,
      'Drupal\jsonrpc\Plugin\HookPluginInterface',
      'Drupal\jsonrpc\Annotation\JsonRpcService'
    );
    $this->alterInfo(FALSE);
    $this->setCacheBackend($cache_backend, 'jsonrpc_plugins');
  }

  protected function execute($jsonrpc_request) {
    list($service_id, $method) = explode('.', $jsonrpc_request);
    /* @var \Drupal\jsonrpc\Annotation\JsonRpcService $service_definition */
    $service_definition = $this->getDefinition($service_id);
    if (!in_array($method, $service_definition->getMethods())) {
      throw new \Exception('Method not found');
    }
    return $this->createInstance($service_id)->{$method}(new ParameterBag($jsonrpc_request['params']));
  }

}
