<?php

namespace Drupal\jsonrpc;

interface ParameterInterface {

  /**
   * The name of the parameter if the params are by-name, an offset otherwise.
   *
   * @return string|integer
   */
  public function getId();

  /**
   * The description of the parameter for the method.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function getDescription();

  /**
   * Gets the parameter schema.
   *
   * Can be derived from the type when the schema property is not defined.
   *
   * @return array
   */
  public function getSchema();

  /**
   * Get the parameter factory class.
   *
   * @return string
   */
  public function getFactory();

  /**
   * Gets the parameter's TypedData data type if one was provided.
   *
   * @return string|null
   *   The TypedData data type name. NULL if one was not provided.
   */
  public function getDataType();

}