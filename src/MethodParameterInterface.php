<?php

namespace Drupal\jsonrpc;

interface MethodParameterInterface {

  /**
   * Gets the parameter's TypedData data type if one was provided.
   *
   * @return string|null
   *   The TypedData data type name. NULL if one was not provided.
   */
  public function getDataType();

  /**
   * Gets the parameter description.
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
   * Whether the parameter should be denormalized.
   *
   * @return bool
   */
  public function shouldBeDenormalized();

  /**
   * Whether the parameter should be denormalized.
   *
   * @return bool
   */
  public function getDenormalizationClass();

}
