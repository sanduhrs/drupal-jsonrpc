<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\jsonrpc\Plugin\RpcEndpointBase;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\PaginationParam;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\ServiceParam;

/**
 * Lists the plugin definitions of a given type.
 *
 * @RpcEndpoint(
 *   id = "user_permissions",
 *   label = @Translation("User Permissions List"),
 *   description = @Translation("Provides a list of permissions."),
 *   usage = "",
 *   permissions = {"administer site configuration"},
 * )
 *
 * @package Drupal\jsonrpc\Plugin\RpcEndpoint
 */
class PermissionsList extends RpcEndpointBase {

  /**
   * {@inheritdoc}
   */
  protected function parameterFactory(array $raw_params) {
    return [
      // Hard code the service to 'user.permissions'.
      'service' => new ServiceParam('user.permissions'),
      'page' => new PaginationParam($raw_params['page']),
    ];
  }

  public function execute() {
    $parameters = $this->parameters();
    /** @var \Drupal\user\PermissionHandler $service */
    $service = $parameters['service']->value();
    $offset = $parameters['page']->value()['offset'];
    $limit = $parameters['page']->value()['limit'];
    return array_slice($service->getPermissions(), $offset, $limit);
  }

}
