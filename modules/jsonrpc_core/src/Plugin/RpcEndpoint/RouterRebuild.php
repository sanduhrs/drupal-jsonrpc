<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\RpcEndpoint;
use Drupal\jsonrpc\Plugin\RpcEndpointBase;
use Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params\ServiceParam;

/**
 * Clears the cache.
 *
 * @RpcEndpoint(
 *   id = "router_rebuild",
 *   label = @Translation("Router Rebuild"),
 *   description = @Translation("Clears the caches for the routing system."),
 *   usage = "",
 *   permissions = {"administer site configuration"},
 * )
 *
 * @package Drupal\jsonrpc\Plugin\RpcEndpoint
 */
class RouterRebuild extends RpcEndpointBase {

  /**
   * {@inheritdoc}
   */
  protected function parameterFactory(array $raw_params) {
    return [
      'service' => new ServiceParam('router.builder'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    /** @var \Drupal\Core\Routing\RouteBuilder $routeBuilder */
    $routeBuilder = $this->parameters()['routeBuilder']->value();
    $routeBuilder->rebuild();
  }

}
