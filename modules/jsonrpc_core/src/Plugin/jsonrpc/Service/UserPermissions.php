<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcMethodParameter;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Plugin\JsonRpcServiceBase;
use Drupal\jsonrpc\Plugin\RpcEndpointBase;
use Drupal\jsonrpc\ServiceInterface;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\PaginationParam;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\ServiceParam;
use Drupal\user\PermissionHandler;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lists the plugin definitions of a given type.
 *
 * @RpcEndpoint(
 *   id = "user_permissions",
 *   usage = @Translation("Introspect and modify user permissions."),
 *   access = {"administer permissions"},
 *   methods = {
 *     @JsonRpcMethod(
 *       name = "list",
 *       params = {
 *         "page" = @JsonRpcMethodParameter(
 *           description = @Translation("The pagination options for the listing. Fewer than `limit` permissions may be returned.")
 *           schema = {
 *             "title": "Pagination",
 *             "type": "object",
 *             "properties": {
 *               "offset": {"type": "integer", "minimum": 0}
 *               "limit": {"type": "integer", "minimum": 0}
 *             }
 *           }
 *         ))
 *       }
 *     )
 *   }
 * )
 *
 * @package Drupal\jsonrpc\Plugin\RpcEndpoint
 */
class UserPermissions extends JsonRpcServiceBase {

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

  public function list(ParameterBag $params) {
    $page = $params->get('page');
    return array_slice($this->permissions->getPermissions(), $page->offset, $page->limit);
  }

}
