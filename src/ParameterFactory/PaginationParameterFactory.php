<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\jsonrpc\ParameterDefinitionInterface;
use Shaper\Util\Context;

class PaginationParameterFactory extends ParameterFactoryBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(ParameterDefinitionInterface $parameter_definition) {
    return [
      'type' => 'object',
      'properties' => [
        'limit' => [
          'type' => 'integer',
          'minimum' => 0,
        ],
        'offset' => [
          'type' => 'integer',
          'minimum' => 0,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function convert($input, ParameterInterface $parameter) {
    return $input;
  }

}
