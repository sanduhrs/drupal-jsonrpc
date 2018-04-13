<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint\Params;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\jsonrpc\Plugin\RpcParameter;

class PluginManagerParam extends RpcParameter {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return new TranslatableMarkup('The service ID for the plugin manager that contains the list of plugin definitions.');
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    // TODO: Validate the schema, making this Shaper should be good enough.
    return ['type' => 'string'];
  }

  /**
   * {@inheritdoc}
   */
  public function shouldBeUpcasted() {
    // TODO: Upcasting is a trasnformation that Shaper should take on.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected function upcast() {
    return \Drupal::service($this->raw);
  }

}
