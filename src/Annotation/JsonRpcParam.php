<?php

namespace Drupal\jsonrpc\Annotation;

/**
 * Defines a JsonRpcParam annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServicePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcParam {

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
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup
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
   * Gets the parameter description.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Gets the parameter schema.
   *
   * Can be derived from the type when the schema property is not defined.
   *
   * @return array
   */
  public function getSchema() {
    return $this->schema ?: [];
  }

  /**
   * Whether the parameter should be denormalized.
   *
   * @return bool
   */
  public function shouldBeDenormalized() {
    return !($this->denormalization_class === FALSE);
  }

  /**
   * Whether the parameter should be denormalized.
   *
   * @return bool
   */
  public function getDenormalizationClass() {
    return $this->shouldBeDenormalized() ? $this->denormalization_class : NULL;
  }

}
