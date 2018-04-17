<?php

namespace Drupal\jsonrpc_core\Plugin\DataType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * Provides a data type definition for defining pagination options.
 *
 * @DataType(
 *   id = "offset_limit_paginator",
 *   label = @Translation("Offset Limit Paginator"),
 *   description = @Translation("Pagination options for listings. Fewer than `limit` items may be returned."),
 *   definition_class = "\Drupal\jsonrpc_core\TypedData\OffsetLimitPaginatorDefinition"
 * )
 */
class OffsetLimitPaginator extends Map {

  /**
   * @return integer
   */
  public function getOffset() {
    return $this->get('offset')->getValue();
  }

  /**
   * @return integer
   */
  public function getLimit() {
    return $this->get('limit')->getValue();
  }

}
