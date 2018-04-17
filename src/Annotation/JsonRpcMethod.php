<?php

namespace Drupal\jsonrpc\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Drupal\Component\Annotation\AnnotationBase;
use Drupal\Component\Annotation\AnnotationInterface;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonrpc\MethodInterface;
use Drupal\jsonrpc\MethodParameterInterface;
use Drupal\jsonrpc\ServiceInterface;

/**
 * Defines a JsonRpcMethodParameter annotation object.
 *
 * @see \Drupal\jsonrpc\Plugin\JsonRpcServiceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonRpcMethod extends AnnotationBase implements MethodInterface {

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
   * The service(s) to which this method belongs.
   *
   * @var \Drupal\jsonrpc\ServiceInterface[]
   */
  protected $services = [];

  /**
   * Gets the unique ID for this annotation.
   */
  public function id() {
    return $this->name;
  }

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
   * List the service(s) to which this method belongs.
   *
   * @return \Drupal\jsonrpc\ServiceInterface[]
   */
  public function getServices() {
    return $this->services;
  }

  /**
   * {@inheritdoc}
   */
  public function addToService(ServiceInterface $service) {
    foreach ($this->services as $existing) {
      if ($existing->id() === $service->id()) {
        return;
      }
    }
    $this->services[] = $service;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this;
  }

}
