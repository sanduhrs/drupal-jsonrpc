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
  public function convert($input, ParameterInterface $parameter) {
    $this->parameterDefinition = $parameter;
    static::doValidation($input, $parameter);
    return $this->doConvert($input, $parameter);
  }

  /**
   * Performs the actual conversion of the input.
   *
   * @param mixed $input
   *   A raw value to be converted to a parameter for a JSON-RPC request. The
   *   value will have already been validated against the parameter's schema.
   * @param \Drupal\jsonrpc\ParameterInterface $parameter
   *   A parameter definition for the method parameter being constructed.
   *
   * @return mixed
   */
  abstract protected function doConvert($input, ParameterInterface $parameter);

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Validate the input value using the declared schema.
   *
   * @param mixed $input
   *   A raw value to be converted to a parameter for a JSON-RPC request. The
   *   raw value must conform to the schema returned by the schema method.
   * @param \Drupal\jsonrpc\ParameterInterface $parameter
   *   A parameter definition for the method parameter being constructed.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   *   Throws an exception if the input fails to validate.
   */
  protected static function doValidation($input, ParameterInterface $parameter) {
    // @todo: actually do validation.
    $valid = TRUE;
    if (!$valid) {
      $message = "The {$parameter->getId()} parameter does not conform to the parameter schema.";
      throw JsonRpcException::fromError(Error::invalidParams($message));
    }
  }

}