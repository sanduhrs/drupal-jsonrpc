<?php

namespace Drupal\jsonrpc\Plugin\jsonrpc\Service;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcMethodParameter;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Plugin\JsonRpcPluginBase;
use Drupal\jsonrpc\Plugin\JsonRpcServiceBase;

/**
 * Class CacheService
 *
 * @JsonRpcService(
 *   id = "cache",
 *   usage = @Translation("Perform operations on the site cache system."),
 *   access = {"administer site configuration"},
 *   methods = {
 *     @JsonRpcMethod(
 *       name = "rebuild",
 *       usage = @Translation("Rebuild the site cache."),
 *     ),
 *   },
 * )
 */
class Cache extends JsonRpcServiceBase {

  public function rebuild() {
    drupal_flush_all_caches();
    return TRUE;
  }

}