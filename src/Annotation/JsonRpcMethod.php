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
class JsonRpcMethod {

  /**
   * The method name.
   *
   * @var string
   */
  public $name;

  /**
   * The access required to use this method.
   *
   * Optional. Works in *addition* to the parent service's access controls.
   * Can be either a callable or an array of permissions.
   *
   * @var string|string[]
   */
  public $access;

  /**
   * How to use this method.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public $usage;

  /**
   * The parameters for this method.
   *
   * Can be a keyed array where the parameter names are the keys or an indexed
   * array for positional parameters.
   *
   * @var \Drupal\jsonrpc\Annotation\JsonRpcParam[]
   */
  public $params;

}
