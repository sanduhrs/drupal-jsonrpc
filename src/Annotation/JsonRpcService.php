<?php

namespace Drupal\jsonrpc\Annotation;

use Drupal\Component\Annotation\AnnotationBase;
use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\jsonrpc\ServiceInterface;

/**
 * Defines a JsonRpcService plugin item annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServiceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcService extends AnnotationBase implements PluginDefinitionInterface, ServiceInterface {

  use AccessibleTrait;

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The method to call when the hook is invoked.
   *
   * @var \Drupal\jsonrpc\Annotation\JsonRpcMethod[]
   */
  public $methods;

  /**
   * How to use this service.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $usage;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return $this->methods;
  }

  /**
   * {@inheritdoc}
   */
  public function getMethod($name) {
    foreach ($this->methods as $method) {
      if ($method->getName() === $name) {
        return $method;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function availableMethods($account = NULL) {
    return array_filter($this->getMethods(), function (JsonRpcMethod $method) use ($account) {
      return $method->access('execute', $account);
    });
  }

  public function get() {
    return $this;
  }

}
