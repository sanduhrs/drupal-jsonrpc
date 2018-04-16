<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\RpcEndpoint;
use Drupal\jsonrpc\Plugin\RpcEndpointBase;

/**
 * Clears the cache.
 *
 * @RpcEndpoint(
 *   id = "cache_rebuild",
 *   label = @Translation("Cache Rebuild"),
 *   description = @Translation("Clears the caches in the backend."),
 *   usage = "",
 *   permissions = {"administer site configuration"},
 * )
 *
 * @package Drupal\jsonrpc\Plugin\RpcEndpoint
 */
class CacheRebuild extends RpcEndpointBase {

  /**
   * {@inheritdoc}
   */
  protected function parameterFactory(array $raw_params) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    drupal_flush_all_caches();
  }

}
