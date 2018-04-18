<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Method;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcMethodParameter;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Plugin\JsonRpcPluginBase;
use Drupal\jsonrpc\Plugin\JsonRpcMethodBase;

/**
 * Class CacheService
 *
 * @JsonRpcMethod(
 *   id = "cache.rebuild",
 *   call = "rebuild",
 *   usage = @Translation("Rebuilds the system cache."),
 *   access = {"administer site configuration"},
 * ),
 */
class Cache extends JsonRpcMethodBase {

  public function rebuild() {
    drupal_flush_all_caches();
    return TRUE;
  }

}