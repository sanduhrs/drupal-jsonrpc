<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\jsonrpc\ParameterInterface;

/**
 * Class RawParameterFactory just returns the raw parameter.
 *
 * @package Drupal\jsonrpc\ParameterFactory
 */
class RawParameterFactory extends ParameterFactoryBase {

  public static function schema(ParameterInterface $parameter) {
    return $parameter->getSchema();
  }

  public function convert($input, ParameterInterface $parameter) {
    return $input;
  }

}