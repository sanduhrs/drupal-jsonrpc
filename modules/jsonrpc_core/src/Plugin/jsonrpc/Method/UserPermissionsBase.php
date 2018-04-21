<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Method;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcParameter;
use Drupal\jsonrpc\Plugin\JsonRpcMethodBase;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @JsonRpcMethod(
 *   id = "user_permissions.add_permission_to_role",
 *   call = "addPermissionToRole",
 *   usage = @Translation("Add the given permission to the specified role."),
 *   access = {"administer permissions"},
 *   params = {
 *     "permission" = @JsonRpcParameter(schema = {"type": "string"}),
 *     "role" = @JsonRpcParameter(factory = "\Drupal\jsonrpc\ParameterFactory\EntityParameterFactory"),
 *   }
 * )
 */
abstract class UserPermissionsBase extends JsonRpcMethodBase {

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

}
