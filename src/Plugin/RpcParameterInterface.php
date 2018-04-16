<?php

namespace Drupal\jsonrpc\Plugin;

interface RpcParameterInterface {

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
   * Whether the parameter should be upcasted.
   *
   * @return bool
   */
  public function shouldBeUpcasted();

  /**
   * The upcasted (if necessary) value..
   *
   * @return mixed
   */
  public function value();
}
