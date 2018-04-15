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
   * @param \Exception
   *   An arbitrary exception.
   * @param mixed
   *   The request ID, if available.
   *
   * @return static
   */
  public static function fromPrevious(\Exception $previous, $id = FALSE) {
    return new static(new Response(
      \Drupal::service('jsonrpc.handler')->supportedVersion(),
      $id ? $id : NULL,
      NULL,
      Error::internalError($previous->getMessage())
    ), $previous);
  }

}
