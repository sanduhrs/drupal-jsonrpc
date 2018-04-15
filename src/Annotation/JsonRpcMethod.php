<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonrpc\MethodInterface;
use Drupal\jsonrpc\MethodParameterInterface;

/**
 * Defines a JsonRpcMethodParameter annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServiceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcMethod implements MethodInterface {

  /**
   * The method name.
   *
   * @var string
   */
  public $name;

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
   * @var \Drupal\jsonrpc\Annotation\JsonRpcMethodParameter[]
   */
  public $params;

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
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsage() {
    $this->usage;
  }

  /**
   * {@inheritdoc}
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * {@inheritdoc}
   */
  public function areParamsPositional() {
    return array_reduce(array_keys($this->getParams()), function ($positional, $key) {
      return $positional ? !is_string($key) : $positional;
    }, TRUE);
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
        break;

      case 'view':
        $access_result = AccessResult::allowedIfHasPermission($account, 'use jsonrpc services');
        break;

      default:
        $access_result = AccessResult::neutral();
        break;
    }
    return $return_as_object ? $access_result : $access_result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->getName();
  }

}
