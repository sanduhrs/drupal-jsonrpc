<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Method;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcParameterDefinition;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Plugin\JsonRpcMethodBase;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @JsonRpcMethod(
 *   id = "user_permissions.list",
 *   usage = @Translation("List all the permissions available in the site."),
 *   access = {"administer permissions"},
 *   params = {
 *     "page" = @JsonRpcParameterDefinition(factory = "\Drupal\jsonrpc\ParameterFactory\PaginationParameterFactory"),
 *   }
 * )
 */
class ListPermissions extends UserPermissionsBase {

  public function execute(ParameterBag $params) {
    $page = $params->get('page');
    return array_slice(
      $this->permissions->getPermissions(),
      $page['offset'],
      $page['limit']
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function outputSchema() {
    // TODO: Fix the schema.
    return ['type' => 'foo'];
  }

}
