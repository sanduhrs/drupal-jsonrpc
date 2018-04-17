<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

trait AccessibleTrait {

  /**
   * The access required to use this RPC operation.
   *
   * Optional. Method and service level access checks are additive.
   *
   * @var mixed
   */
  public $access;

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
        $access_result = AccessResult::allowed();
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

}