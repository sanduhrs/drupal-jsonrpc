<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a JsonRpcService plugin item annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServicePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcService extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The method to call when the hook is invoked.
   *
   * @var \Drupal\jsonrpc\Annotation\JsonRpcMethod[]
   */
  public $methods;

  /**
   * The access required to use this method.
   *
   * Required. Can be either a callable or an array of permissions.
   *
   * @var string|string[]
   */
  public $access;

  /**
   * How to use this service.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public $usage;

  /**
   * The service methods.
   *
   * @return \Drupal\jsonrpc\Annotation\JsonRpcMethod[]
   *   The service methods.
   */
  public function getMethods() {
    return $this->methods;
  }

  /**
   * The service methods which are available to the current user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The account for which to get accessible methods. The current
   *   account will be used if one is not provided.
   *
   * @return \Drupal\jsonrpc\Annotation\JsonRpcMethod[]
   *   The available methods.
   */
  public function availableMethods($account = NULL) {
    return array_filter($this->getMethods(), function (JsonRpcMethod $method) use ($account) {
      return $method->access('execute', $account);
    });
  }

}
