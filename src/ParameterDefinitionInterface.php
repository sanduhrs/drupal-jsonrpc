<?php

namespace Drupal\jsonrpc;

interface ParameterDefinitionInterface {

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

}
