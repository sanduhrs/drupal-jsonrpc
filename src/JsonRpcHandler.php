<?php

namespace Drupal\jsonrpc;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\Request;
use Drupal\jsonrpc\Object\Response;

class JsonRpcHandler implements HandlerInterface {

  /**
   * The support JSON-RPC version.
   *
   * @var string
   */
  const SUPPORTED_VERSION = '2.0';

  /**
   * The JSON-RPC method plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $methodManager;

  public function __construct(PluginManagerInterface $method_manager) {
    $this->methodManager = $method_manager;
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
  public function supportedMethods() {
    return $this->methodManager->getDefinitions();
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
    return $this->methodManager->getDefinition($name, FALSE);
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
    } catch (\Exception $e) {
      if (!$e instanceof JsonRpcException) {
        $e = JsonRpcException::fromPrevious($e, $request->isNotification() ? FALSE : $request->id());
      }
      return $e->getResponse();
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
      $configuration = [HandlerInterface::JSONRPC_REQUEST_KEY => $request];
      $executable = $this->getExecutable($method, $configuration);
      return $request->hasParams()
        ? $executable->{$method->call()}($request->getParams())
        : $executable->{$method->call()}();
    }
    else {
      throw JsonRpcException::fromError(Error::methodNotFound($method->id()));
    }
  }

  /**
   * Gets an executable instance of an RPC method.
   *
   * @param \Drupal\jsonrpc\MethodInterface $method
   *   The method definition.
   * @param array $configuration
   *   Method configuration.
   *
   * @return object
   *   The executable method.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function getExecutable(MethodInterface $method, $configuration) {
    try {
      return $this->methodManager->createInstance($method->id(), $configuration);
    }
    catch (PluginException $e) {
      throw JsonRpcException::fromError(Error::methodNotFound($method->id()));
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

}