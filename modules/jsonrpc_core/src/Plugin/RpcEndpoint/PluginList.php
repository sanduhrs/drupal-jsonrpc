<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\jsonrpc\Plugin\RpcEndpointBase;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\PaginationParam;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\PluginManagerParam;

/**
 * Lists the plugin definitions of a given type.
 *
 * @RpcEndpoint(
 *   id = "plugin_definitions_list",
 *   label = @Translation("Plugins Definition List"),
 *   description = @Translation("Provides a list of the serialized plugin definitions."),
 *   usage = "",
 *   permissions = {"administer site configuration"},
 * )
 *
 * @package Drupal\jsonrpc\Plugin\RpcEndpoint
 */
class PluginList extends RpcEndpointBase {

  /**
   * {@inheritdoc}
   */
  protected function parameterFactory(array $raw_params) {
    return [
      'plugin_manager' => new PluginManagerParam($raw_params['plugin_manager']),
      'page' => new PaginationParam($raw_params['page']),
    ];
  }

  public function execute() {
    $parameters = $this->parameters();
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
    $plugin_manager = $parameters['plugin_manager']->value();
    $offset = $parameters['page']->value()['offset'];
    $limit = $parameters['page']->value()['limit'];
    return array_slice($plugin_manager->getDefinitions(), $offset, $limit);
  }

}
