<?php

namespace Drupal\jsonrpc\Plugin\jsonrpc\Service;

use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcMethodParameter;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Plugin\JsonRpcPluginBase;

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
 *       params = {@JsonRpcMethodParameter(data_type="string")}
 *     ),
 *   },
 * )
 */
class Cache extends JsonRpcPluginBase {

  public function rebuild() {
    //drupal_flush_all_caches();
    \Drupal::logger('jsonrpc')->info('Rebuilt cache.');
    return TRUE;
  }

}