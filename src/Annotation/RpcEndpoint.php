<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a RPC Endpoint item annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\RpcEndpointManager
 * @see plugin_api
 *
 * @Annotation
 */
class RpcEndpoint extends Plugin {

  /**
   * The method for the RPC endpoint.
   *
   * @var string
   */
  public $id;

  /**
   * The permissions necessary to operate this endpoint.
   *
   * @var string[]
   */
  public $permissions = [];

  /**
   * Description of the endpoint.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * How to use this method.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $usage;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
