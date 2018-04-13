<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\jsonrpc\Plugin\RpcEndpointBase;
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
    $plugin_manager_service_id = $raw_params['plugin_manager'];
    return [
      'plugin_manager' => new PluginManagerParam($plugin_manager_service_id),
    ];
  }

  public function execute() {
    $parameters = $this->parameters();
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
    $plugin_manager = $parameters['plugin_manager']->value();
    return $plugin_manager->getDefinitions();
  }

}
