<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\jsonrpc\Plugin\RpcParameter;

/**
 * Pagination parameter to not overwhelm the consumer with long lists.
 */
class PaginationParam extends RpcParameter {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return new TranslatableMarkup('The pagination options for long lists of elements.');
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return [
      'type' => 'object',
      'properties' => [
        'limit' => 'integer',
        'offset' => 'integer',
      ],
    ];
  }

  public function value() {
    $value = parent::value();
    return NestedArray::mergeDeep(['offset' => 0, 'limit' => NULL], $value);
  }

}
