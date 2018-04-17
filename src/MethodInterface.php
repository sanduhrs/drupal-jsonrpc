<?php

namespace Drupal\jsonrpc;

use Drupal\Core\Access\AccessibleInterface;

interface MethodInterface extends AccessibleInterface {

  /**
   * The method name.
   *
   * @return string
   */
  public function getName();

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
   * List the service(s) to which this method belongs.
   *
   * @return \Drupal\jsonrpc\ServiceInterface[]
   */
  public function getServices();

  /**
   * Whether the parameters are by-position.
   *
   * @return bool
   */
  public function areParamsPositional();

}