<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Access\AccessibleInterface;

/**
 * Defines an interface for RPC Endpoint plugins.
 */
interface RpcEndpointInterface extends PluginInspectionInterface, AccessibleInterface {

  /**
   * Executes the procedure.
   */
  public function execute();

}
