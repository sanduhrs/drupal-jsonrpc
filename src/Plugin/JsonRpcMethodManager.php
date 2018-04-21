<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\MethodInterface;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\Request;
use Drupal\jsonrpc\Object\Response;
use Drupal\jsonrpc\ParameterFactoryInterface;

/**
 * Provides the JsonRpcMethod plugin plugin manager.
 *
 * @internal
 */
class JsonRpcMethodManager extends DefaultPluginManager {

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
    // The following two lines prevent other modules from implementing RPC
    // methods. For now, all implementations should remain internal until the
    // plugin API is finalized.
    $namespaces = $this->getWhitelistedNamespaces($module_handler);
    $this->alterInfo(FALSE);
    parent::__construct('Plugin/jsonrpc/Method', $namespaces, $module_handler, NULL, JsonRpcMethod::class);
    $this->setCacheBackend($cache_backend, 'jsonrpc_plugins');
  }


  /**
   * {@inheritdoc}
   */
  public function alterDefinitions(&$definitions) {
    /* @var \Drupal\jsonrpc\Annotation\JsonRpcMethod $method */
    foreach ($definitions as &$method) {
      $this->assertValidJsonRpcMethodPlugin($method);
      if (isset($method->params)) {
        foreach ($method->params as $key => &$param) {
          $param->setId($key);
        }
      }
    }
    parent::alterDefinitions($definitions);
  }

  /**
   * Asserts that the plugin class is valid.
   *
   * @param \Drupal\jsonrpc\Annotation\JsonRpcMethod $method
   *   The JSON-RPC method definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function assertValidJsonRpcMethodPlugin($method) {
    foreach ($method->params as $param) {
      if (!$param->factory && !$param->schema) {
        throw new InvalidPluginDefinitionException($method->id(), "Every JsonRpcParameter must define either a factory or a schema.");
      }
      if ($param->factory && !is_subclass_of($param->factory, ParameterFactoryInterface::class)) {
        throw new InvalidPluginDefinitionException($method->id(), "Parameter factories must implement ParameterFactoryInterface.");
      }
    }
  }


  /**
   * Gets a traversable list of namespaces to look for plugins.
   *
   * Until the API is finalized, sites need to specifically opt-in modules using
   * these experimental APIs to acknowledge the high risk of failure.
   *
   * Contrib modules which are found to automatically add themselves to this
   * list without site administrator approval will trigger warnings.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *
   * @return \Traversable
   */
  protected function getWhitelistedNamespaces(ModuleHandlerInterface $module_handler) {
    $config = \Drupal::config('jsonrpc')->get('experimental_modules.whitelist') ?: [];
    $modules_whitelist = ['jsonrpc_core'] + $config;
    $namespaces = array_reduce($modules_whitelist, function ($whitelist, $module) use ($module_handler) {
      $module_directory = $module_handler->getModule($module)->getPath();
      $whitelist["Drupal\\$module"] = "$module_directory/src";
      return $whitelist;
    }, []);
    return new \ArrayIterator($namespaces);
  }

}
