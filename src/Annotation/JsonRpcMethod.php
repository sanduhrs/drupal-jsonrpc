<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines a JsonRpcParam annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServicePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcMethod implements AccessibleInterface {

  /**
   * The method name.
   *
   * @var string
   */
  public $name;

  /**
   * The access required to use this method.
   *
   * Optional. Works in *addition* to the parent service's access controls.
   * Can be either a callable or an array of permissions.
   *
   * @var string|string[]
   */
  public $access;

  /**
   * How to use this method.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public $usage;

  /**
   * The parameters for this method.
   *
   * Can be a keyed array where the parameter names are the keys or an indexed
   * array for positional parameters.
   *
   * @var \Drupal\jsonrpc\Annotation\JsonRpcParam[]
   */
  public $params;

  /**
   * Gets the method name.
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'execute', AccountInterface $account = NULL, $return_as_object = FALSE) {
    $account = $account ?: \Drupal::currentUser();
    switch ($operation) {
      case 'execute':
        if (is_callable($this->access)) {
          return call_user_func_array($this->access, [$operation, $account, $return_as_object]);
        }
        $access_result = AccessResult::neutral();
        foreach ($this->access as $permission) {
          $access_result = $access_result->andIf(AccessResult::allowedIfHasPermission($account, $permission));
        }
        return $access_result;

      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'use jsonrpc services');

      default:
        return AccessResult::neutral();
    }
  }

}
