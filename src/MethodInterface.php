<?php

namespace Drupal\jsonrpc;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Access\AccessibleInterface;

interface MethodInterface extends AccessibleInterface, PluginDefinitionInterface {

  /**
   * The class method to call.
   *
   * @return string
   */
  public function call();

  /**
   * How to use this method.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function getUsage();

  /**
   * The parameters for this method.
   *
   * Can be a keyed array where the parameter names are the keys or an indexed
   * array for positional parameters.
   *
   * @return \Drupal\jsonrpc\MethodParameterInterface[]|null
   *   The method params or NULL if none are accepted.
   */
  public function getParams();

  /**
   * Whether the parameters are by-position.
   *
   * @return bool
   */
  public function areParamsPositional();

}