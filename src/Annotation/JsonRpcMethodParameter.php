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
   * The parameter type name.
   *
   * Required if a schema is not provided or when the parameter should be
   * upcasted. If a schema is not provided, the type name must match a TypedData
   * data type name.
   *
   * @var string
   */
  public $type = NULL;

  /**
   * The parameter schema.
   *
   * Required if a type name is not provided to the type name does not match a
   * TypedData data type name.
   *
   * @var array
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
   * @var string|FALSE
   */
  public $denormalization_class = FALSE;

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
