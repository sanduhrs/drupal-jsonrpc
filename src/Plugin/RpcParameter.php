<?php

namespace Drupal\jsonrpc\Plugin;

/**
 * Class RpcParameter
 *
 * @package Drupal\jsonrpc\Plugin
 */
abstract class RpcParameter implements RpcParameterInterface {

  /**
   * The raw value of the parameter.
   *
   * @var array|string|integer|float|null|bool
   */
  protected $raw;

  /**
   * Local cache for the upcasted parameter.
   *
   * @var mixed
   */
  protected $value;

  /**
   * The parameter type name.
   *
   * Required if a schema is not provided or when the parameter should be
   * upcasted. If a schema is not provided, the type name must match a TypedData
   * data type name.
   *
   * @var string
   */
  protected $type = NULL;

  /**
   * The parameter schema.
   *
   * Required if a type name is not provided to the type name does not match a
   * TypedData data type name.
   *
   * @var array
   */
  protected $schema = NULL;

  /**
   * RpcParam constructor.
   *
   * @param mixed $raw
   *   The raw value for the parameter.
   */
  public function __construct($raw) {
    $this->raw = $raw;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldBeUpcasted() {
    return FALSE;
  }

  /**
   * Upcasts the raw value into a typed object.
   *
   * Override this method in the parameter class to provide proper upcasting.
   *
   * @return mixed
   */
  protected function upcast() {
    // Default to the Null pattern.
    return $this->raw;
  }

  /**
   * {@inheritdoc}
   */
  public function value() {
    if ($this->value) {
      return $this->value;
    }
    $this->value = $this->shouldBeUpcasted() ? $this->upcast() : $this->raw;
    return  $this->value;
  }

}
