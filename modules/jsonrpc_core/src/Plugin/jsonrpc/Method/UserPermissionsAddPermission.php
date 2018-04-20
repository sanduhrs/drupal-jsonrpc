<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Method;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcParameter;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Plugin\JsonRpcMethodBase;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @JsonRpcMethod(
 *   id = "user_permissions.add_permission_to_role",
 *   usage = @Translation("Add the given permission to the specified role."),
 *   access = {"administer permissions"},
 *   params = {
 *     "permission" = @JsonRpcParameter(schema = {"type": "string"}),
 *     "role" = @JsonRpcParameter(factory = "\Drupal\jsonrpc\ParameterFactory\EntityParameterFactory"),
 *   }
 * )
 */
class UserPermissionsAddPermission extends UserPermissionsBase {

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

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public function execute(ParameterBag $params) {
    $permission = $params->get('permission')->getValue();
    /* @var \Drupal\user\RoleInterface $role */
    $role = $params->get('role');
    try {
      $role->grantPermission($permission);
      $violations = $role->getTypedData()->validate();
      if ($violations->count() !== 0) {
        $error = Error::invalidParams(array_map(function (ConstraintViolationInterface $violation) {
          return $violation->getMessage();
        }, iterator_to_array($violations)));
        throw JsonRpcException::fromError($error);
      }
      return $role->save();
    }
    catch (EntityStorageException $e) {
      $error = Error::internalError('Unable to save the user role. Error: ' . $e->getMessage(), $role);
      throw JsonRpcException::fromError($error);
    }
  }

}
