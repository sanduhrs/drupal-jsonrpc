<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\jsonrpc\JsonRpcHandlerInterface;
use Drupal\jsonrpc\ParameterBag;

/**
 * Provides the JsonRpcService plugin plugin manager.
 *
 * @internal
 */
class JsonRpcServicePluginManager extends DefaultPluginManager implements JsonRpcHandlerInterface {

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

  public function execute($jsonrpc_request) {
    $executor = $this->getExecutor($jsonrpc_request);
    return isset($jsonrpc_request->id)
      ? $this->getResponse($executor, $jsonrpc_request->id)
      : $this->getResponse($executor);
  }

  public function batch($jsonrpc_requests) {
    $jsonrpc_responses = [];
    foreach ($jsonrpc_requests as $jsonrpc_request) {
      $jsonrpc_responses[] = $this->execute($jsonrpc_request);
    }
    return array_filter($jsonrpc_responses);
  }

  protected function getExecutor($jsonrpc_request) {
    list($service_id, $method) = explode('.', $jsonrpc_request);
    /* @var \Drupal\jsonrpc\Annotation\JsonRpcService $service_definition */
    $service_definition = $this->getDefinition($service_id);
    if (!in_array($method, $service_definition->getMethods())) {
      return function () {
        throw new \Exception('Method not found');
      };
    }
    $params = new ParameterBag($jsonrpc_request['params']);
    return function () use ($service_id, $method, $params) {
      return $this->createInstance($service_id)->{$method}($params);
    };
  }

  protected function getResponse($executor, $id = NULL) {
    try {
      $result = $executor();
      if (is_null($id)) {
        return NULL;
      }
    }
    catch (\Exception $e) {
      // @TODO Changing the data array will be a BC break. Consider this
      // structure more.
      $error = [
        'code' => -32603,
        'message' => 'Server error',
        'data' => ['detail' => $e->getMessage()],
      ];
    }
    $jsonrpc_response = [
      'jsonrpc' => '2.0',
      'id' => $id,
    ];
    return array_merge($jsonrpc_response, (isset($result) && !isset($error))
      ? ['result' => $result]
      : ['error' => $error]
    );
  }

}
