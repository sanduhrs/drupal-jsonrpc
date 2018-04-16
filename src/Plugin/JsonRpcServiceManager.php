<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\MethodInterface;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Object\Request;
use Drupal\jsonrpc\Object\Response;
use Drupal\jsonrpc\ServiceInterface;

/**
 * Provides the JsonRpcService plugin plugin manager.
 *
 * @internal
 */
class JsonRpcServiceManager extends DefaultPluginManager implements HandlerInterface {

  /**
   * The support JSON-RPC version.
   *
   * @var string
   */
  const SUPPORTED_VERSION = '2.0';

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
    // services. For now, all implementations should remain internal until the
    // plugin API is finalized.
    //$namespaces = new \ArrayIterator([$module_handler->getModule('jsonrpc')->getPath() => '\Drupal\jsonrpc']);
    //$this->alterInfo(FALSE);
    parent::__construct('Plugin/jsonrpc/Service', $namespaces, $module_handler, NULL, JsonRpcService::class);
    $this->setCacheBackend($cache_backend, 'jsonrpc_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(Request $request) {
    return $this->doRequest($request);
  }

  /**
   * {@inheritdoc}
   */
  public function batch(array $requests) {
    return array_filter(array_map(function (Request $request) {
      return $this->doRequest($request);
    }, $requests));
  }

  /**
   * {@inheritdoc}
   */
  public static function supportedVersion() {
    return static::SUPPORTED_VERSION;
  }

  /**
   * {@inheritdoc}
   */
  public function supportedMethods() {
    return array_reduce($this->getDefinitions(), function ($methods, ServiceInterface $service) {
      return array_merge($methods, $service->getMethods());
    }, []);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsMethod($name) {
    return !is_null($this->getMethod($name));
  }

  /**
   * {@inheritdoc}
   */
  public function getMethod($name) {
    list($service_id, $method_name) = explode('.', $name);
    /* @var \Drupal\jsonrpc\ServiceInterface|null $service_definition */
    $service_definition = $this->getDefinition($service_id);
    if (!$service_definition) {
      return NULL;
    }
    foreach ($service_definition->getMethods() as $method) {
      if ($method->getName() === $method_name) {
        return $method;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function alterDefinitions(&$definitions) {
    if (PHP_MAJOR_VERSION >= 7 || assert_options(ASSERT_ACTIVE)) {
      foreach ($definitions as $definition) {
        $this->assertValidJsonRpcServicePlugin($definition);
      }
    }
    parent::alterDefinitions($definitions);
  }

  /**
   * Asserts that the plugin class is valid.
   *
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|\Drupal\jsonrpc\ServiceInterface $service
   *   The JSON-RPC service definition.
   */
  protected function assertValidJsonRpcServicePlugin($service) {
    $reflection = new \ReflectionClass($service->getClass());
    foreach ($service->getMethods() as $method) {
      $method_name = $method->getName();
      assert($reflection->hasMethod($method->getName()), "JSON-RPC method names must match a public method name on the plugin class. Missing the '$method_name' method.");
    }
  }

  /**
   * Gets an anonymous function which executes the RPC method.
   *
   * @param \Drupal\jsonrpc\Object\Request $request
   *   The JSON-RPC request.
   *
   * @return \Drupal\jsonrpc\Object\Response|null
   *   The JSON-RPC response.
   */
  protected function doExecution(Request $request) {
    list($service_id, $method) = explode('.', $request->getMethod());
    return $request->hasParams()
      ? $this->createInstance($service_id)->{$method}($request->getParams())
      : $this->createInstance($service_id)->{$method}();
  }

  /**
   * Executes an RPC call and returns a JSON-RPC response.
   *
   * @param \Drupal\jsonrpc\Object\Request $request
   *   The JSON-RPC request.
   *
   * @return \Drupal\jsonrpc\Object\Response|null
   *   The JSON-RPC response.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function doRequest(Request $request) {
    try {
      if ($request->isNotification()) {
        $this->doExecution($request);
        return NULL;
      }
      else {
        $result = $this->doExecution($request);
        return $result instanceof Response
          ? $result
          : new Response(static::SUPPORTED_VERSION, $request->id(), $result);
      }
    }
    catch (\Exception $e) {
      throw JsonRpcException::fromPrevious($e, $request->isNotification() ? FALSE : $request->id());
    }
  }

}
