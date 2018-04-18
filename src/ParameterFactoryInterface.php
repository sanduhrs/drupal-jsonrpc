<?php

namespace Drupal\jsonrpc;

interface ParameterFactoryInterface {

  /**
   * An array representing the JSON Schema for acceptable input to the factory.
   *
   * @param \Drupal\jsonrpc\ParameterInterface $parameter
   *   A parameter definition for the method parameter being constructed.
   *
   * @return array
   */
  public static function schema(ParameterInterface $parameter);

  /**
   * Returns an object to be passed to the JSON-RPC method in place of raw data.
   *
   * @param mixed $input
   *   A raw value to be converted to a parameter for a JSON-RPC request. The
   *   raw value must conform to the schema returned by the schema method.
   * @param \Drupal\jsonrpc\ParameterInterface $parameter
   *   A parameter definition for the method parameter being constructed.
   *
   * @return mixed
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public function convert($input, ParameterInterface $parameter);

}