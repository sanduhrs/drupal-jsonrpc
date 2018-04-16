<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\jsonrpc\MethodParameterInterface;

/**
 * Defines a JsonRpcMethodParameter annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServiceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcMethodParameter implements MethodParameterInterface {

  /**
   * The parameter data type name.
   *
   * Required if a schema is not provided. If a schema is not provided, the type
   * name must match a TypedData data type name and the received parameter will
   * be cast to an instance of that type.
   *
   * @var string
   */
  public $data_type = NULL;

  /**
   * The parameter schema.
   *
   * Required if a type name is not provided to the type name does not match a
   * TypedData data type name.
   *
   * @var array
   *
   * @todo enforce this requirement.
   */
  public $schema = NULL;

  /**
   * A description of the parameter.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * A denormalization target class.
   *
   * If the parameter should be denormalized to an object, this class should be
   * provided.
   *
   * @var string
   */
  public $denormalization_class = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getDataType() {
    return $this->data_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema ?: [];
  }

  /**
   * {@inheritdoc}
   */
  public function shouldBeDenormalized() {
    return !($this->denormalization_class === FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getDenormalizationClass() {
    return $this->shouldBeDenormalized() ? $this->denormalization_class : NULL;
  }

}
