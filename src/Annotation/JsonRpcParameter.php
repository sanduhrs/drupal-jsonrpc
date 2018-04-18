<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Component\Annotation\AnnotationBase;
use Drupal\jsonrpc\ParameterFactory\TypedDataParameterFactory;
use Drupal\jsonrpc\ParameterFactoryInterface;
use Drupal\jsonrpc\ParameterInterface;

/**
 * Defines a JsonRpcParameter annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServiceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcParameter implements ParameterInterface {

  /**
   * The name of the parameter if the params are by-name, an offset otherwise.
   *
   * @var string|integer
   */
  protected $id;

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
   * The parameter factory.
   *
   * @var string
   */
  public $factory;

  /**
   * {@inheritdoc}
   */
  public function getDataType() {
    return $this->data_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getFactory() {
    return $this->factory;
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
    if (!isset($this->schema) && isset($this->factory)) {
      $this->schema = $this->factory::schema($this);
    }
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this;
  }

  /**
   * Sets the parameter ID.
   *
   * @param string|integer
   *   The ID to set.
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

}