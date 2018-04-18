<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Method;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcParameter;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Plugin\JsonRpcMethodBase;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @JsonRpcMethod(
 *   id = "user_permissions.list",
 *   call = "listing",
 *   usage = @Translation("List user permissions."),
 *   access = {"administer permissions"},
 *   params = {
 *     "page" = @JsonRpcParameter(data_type = "offset_limit_paginator"),
 *   }
 * )
 */
class UserPermissions extends JsonRpcMethodBase {

  /**
   * The permissions handler service.
   *
   * @var \Drupal\user\PermissionHandlerInterface
   */
  protected $permissions;

  /**
   * UserPermissions constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, PermissionHandlerInterface $user_permissions) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->permissions = $user_permissions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('user.permissions')
    );
  }

  public function listing(ParameterBag $params) {
    $page = $params->get('page');
    return array_slice($this->permissions->getPermissions(), $page->getOffset(), $page->getLimit());
  }

}
