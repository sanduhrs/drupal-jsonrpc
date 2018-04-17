<?php

namespace Drupal\jsonrpc\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonrpc\MethodInterface;
use Drupal\jsonrpc\MethodParameterInterface;

/**
 * Defines a JsonRpcMethodParameter annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServiceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcMethod implements MethodInterface {

  use AccessibleTrait;

  /**
   * The method name.
   *
   *
   * @var string
   */
  public $name;

  /**
   * How to use this method.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $usage;

  /**
   * The parameters for this method.
   *
   * Can be a keyed array where the parameter names are the keys or an indexed
   * array for positional parameters.
   *
   * @var \Drupal\jsonrpc\Annotation\JsonRpcMethodParameter[]
   */
  public $params;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsage() {
    $this->usage;
  }

  /**
   * {@inheritdoc}
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * {@inheritdoc}
   */
  public function areParamsPositional() {
    return array_reduce(array_keys($this->getParams()), function ($positional, $key) {
      return $positional ? !is_string($key) : $positional;
    }, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->getName();
  }

}
