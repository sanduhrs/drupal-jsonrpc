<?php

namespace Drupal\jsonrpc\Exception;

use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\Response;

class JsonRpcException extends \Exception {

  /**
   * The JSON-RPC error response for the exception.
   *
   * @param \Drupal\jsonrpc\Object\Response $response
   */
  protected $response;

  /**
   * JsonRpcException constructor.
   *
   * @param \Drupal\jsonrpc\Object\Response $response
   *   The JSON-RPC error response object for the exception.
   */
  public function __construct(Response $response, \Throwable $previous = NULL) {
    $this->response = $response;
    $error = $response->getError();
    parent::__construct($error->getMessage(), $error->getCode(), $previous);
  }

  /**
   * The appropriate JSON-RPC error response for the exception.
   *
   * @return \Drupal\jsonrpc\Object\Response $response
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Constructs a JsonRpcException from an arbitrary exception.
   *
   * @param \Exception $previous
   *   An arbitrary exception.
   * @param mixed $id
   *   The request ID, if available.
   * @param string $version
   *   (optional) The JSON-RPC version.
   *
   * @return static
   */
  public static function fromPrevious(\Exception $previous, $id = FALSE, $version = NULL) {
    if ($previous instanceof JsonRpcException) {
      return $previous;
    }
    $error = Error::internalError($previous->getMessage());
    $response = static::buildResponse($error, $id, $version);
    return new static($response, $previous);
  }

  /**
   * Constructs a JsonRpcException from an arbitrary error object.
   *
   * @param \Drupal\jsonrpc\Object\Error $error
   *   The error which caused the exception.
   * @param mixed
   *   The request ID, if available.
   * @param string $version
   *   (optional) The JSON-RPC version.
   *
   * @return static
   */
  public static function fromError(Error $error, $id = FALSE, $version = NULL) {
    return new static(static::buildResponse($error, $id, $version));
  }

  /**
   * Helper to build a JSON-RPC response object.
   *
   * @param \Drupal\jsonrpc\Object\Error $error
   * @param mixed $id
   * @param string $version
   *
   * @return \Drupal\jsonrpc\Object\Response
   */
  protected static function buildResponse(Error $error, $id = FALSE, $version = NULL) {
    $supported_version = $version ?: \Drupal::service('jsonrpc.handler')->supportedVersion();
    return new Response($supported_version, $id ? $id : NULL, NULL, $error);
  }

}
