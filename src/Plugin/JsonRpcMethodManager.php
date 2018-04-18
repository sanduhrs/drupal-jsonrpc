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

/**
 * Provides the JsonRpcMethod plugin plugin manager.
 *
 * @internal
 */
class JsonRpcMethodManager extends DefaultPluginManager implements HandlerInterface {

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
    return $this->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function supportsMethod($name) {
    return !is_null($this->getMethod($name));
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface|NULL $account
   *
   * @return \Drupal\jsonrpc\MethodInterface[]
   */
  public function availableMethods(AccountInterface $account = NULL) {
    return array_filter($this->supportedMethods(), function (MethodInterface $method) {
      return $method->access('execute');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getMethod($name) {
    return $this->getDefinition($name, FALSE);
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
    $reflection = new \ReflectionClass($method->getClass());
    if (!$reflection->hasMethod($method->call())) {
      throw new InvalidPluginDefinitionException($method->id(), "JSON-RPC method names must match a public method name on the plugin class. Missing the '{$method->call()}' method.");
    }
    foreach ($method->params as $param) {
      if (!($param->factory xor $param->data_type)) {
        throw new InvalidPluginDefinitionException($method->id(), "Every JsonRpcParameter must define either a factory_class or a data_type, but not both.");
      }
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
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function doExecution(Request $request) {
    if ($method = $this->getMethod($request->getMethod())) {
      $this->checkAccess($method);
      $configuration = [
        HandlerInterface::JSONRPC_REQUEST_KEY => $request,
      ];
      return $request->hasParams()
        ? $this->createInstance($method->id(), $configuration)->{$method->call()}($request->getParams())
        : $this->createInstance($method->id(), $configuration)->{$method->call()}();
    }
    else {
      JsonRpcException::fromError(Error::methodNotFound($method->id()));
    }
  }

  /**
   * Executes an RPC call and returns a JSON-RPC response.
   *
   * @param \Drupal\jsonrpc\Object\Request $request
   *   The JSON-RPC request.
   *
   * @return \Drupal\jsonrpc\Object\Response|null
   *   The JSON-RPC response.
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
      if (!$e instanceof JsonRpcException) {
        $e = JsonRpcException::fromPrevious($e, $request->isNotification() ? FALSE : $request->id());
      }
      return $e->getResponse();
    }
  }

  /**
   * Check execution access.
   *
   * @param \Drupal\jsonrpc\MethodInterface $method
   *   The method for which to check access.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function checkAccess($method) {
    /* @var \Drupal\jsonrpc\MethodInterface $method_definition */
    $access_result = $method->access('execute', NULL, TRUE);
    if (!$access_result->isAllowed()) {
      $reason = 'Access Denied';
      if ($access_result instanceof AccessResultReasonInterface && ($detail = $access_result->getReason())) {
        $reason .= ': ' . $detail;
      }
      throw JsonRpcException::fromError(Error::invalidRequest($reason, $access_result));
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
