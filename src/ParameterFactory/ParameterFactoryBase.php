<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\ParameterFactoryInterface;
use Drupal\jsonrpc\ParameterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ParameterFactoryBase implements ParameterFactoryInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\jsonrpc\ParameterInterface
   */
  protected $parameterDefinition;

  /**
   * {@inheritdoc}
   */
  abstract public static function schema(ParameterInterface $parameter);

  /**
   * {@inheritdoc}
   */
  abstract public function convert($input, ParameterInterface $parameter);

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

}