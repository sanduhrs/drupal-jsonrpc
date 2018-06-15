<?php

namespace Drupal\jsonrpc\Object;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

class Response implements CacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The JSON-RPC version.
   *
   * @var string
   */
  protected $version;

  /**
   * A string, number or NULL ID.
   *
   * @var mixed
   */
  protected $id;

  /**
   * The result.
   *
   * @var mixed
   */
  protected $result;

  /**
   * The schema for the result.
   *
   * @var null|array
   */
  protected $resultSchema;

  /**
   * The error.
   *
   * @var \Drupal\jsonrpc\Object\Error
   */
  protected $error;

  /**
   * Response constructor.
   *
   * @param string $version
   *   The JSON-RPC version.
   * @param mixed $id
   *   The response ID. Must match the ID of the generating request.
   * @param mixed $result
   *   A result value. Must not be provided if an error is to be provided.
   * @param \Drupal\jsonrpc\Object\Error $error
   *   An error object if the response resulted in an error. Must not be
   *   provided if a result was provided.
   */
  public function __construct($version, $id, $result = NULL, Error $error = NULL) {
    $this->assertValidResponse($version, $id, $result, $error);
    $this->version = $version;
    $this->id = $id;
    if (!is_null($result)) {
      $this->result = $result;
    }
    else {
      $this->error = $error;
      $this->setCacheability($error);
    }
  }

  public function id() {
    return $this->id;
  }

  public function version() {
    return $this->version;
  }

  public function getResult() {
    return $this->result;
  }

  public function getError() {
    return $this->error;
  }

  public function isResultResponse() {
    return !$this->isErrorResponse();
  }

  public function isErrorResponse() {
    return isset($this->error);
  }

  protected function assertValidResponse($version, $id, $result, $error) {
    assert(!is_null($result) xor !is_null($error), 'Either the result member or error member MUST be included, but both members MUST NOT be included.');
    assert($version === "2.0", 'A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".');
    assert(is_string($id) || is_numeric($id) || is_null($id), 'An identifier established by the Client that MUST contain a String, Number, or NULL value if included.');
  }

  /**
   * @return array|null
   */
  public function getResultSchema() {
    return $this->resultSchema;
  }

  /**
   * @param array|null $resultSchema
   */
  public function setResultSchema($result_schema) {
    $this->resultSchema = $result_schema;
  }

}
