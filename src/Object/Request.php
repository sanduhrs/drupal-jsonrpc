<?php

namespace Drupal\jsonrpc\Object;

class Request {

  /**
   * The JSON-RPC version.
   *
   * @var string
   */
  protected $version;

  /**
   * The RPC service method id.
   *
   * @var string
   */
  protected $method;

  /**
   * The request parameters, if any.
   *
   * @var \Drupal\jsonrpc\Object\ParameterBag|null
   */
  protected $params;

  /**
   * A string, number or NULL ID. False when an ID was not provided.
   *
   * @var mixed|FALSE
   */
  protected $id;

  /**
   * Indicates if the request is part of a batch or not.
   *
   * @var bool
   */
  protected $inBatch;

  /**
   * Request constructor.
   *
   * @param string $version
   *   The JSON-RPC version.
   * @param string $method
   *   The RPC service method id.
   * @param bool $in_batch
   *   Indicates if the request is part of a batch or not.
   * @param mixed|FALSE $id
   *   A string, number or NULL ID. FALSE for notification requests.
   * @param \Drupal\jsonrpc\Object\ParameterBag|null $params
   *   The request parameters, if any.
   */
  public function __construct($version, $method, $in_batch = FALSE, $id = FALSE, ParameterBag $params = NULL) {
    $this->assertValidRequest($version, $method, $id);
    $this->version = $version;
    $this->method = $method;
    $this->inBatch = $in_batch;
    $this->params = $params;
    $this->id = $id;
  }

  public function id() {
    return $this->id;
  }

  public function getMethod() {
    return $this->method;
  }

  public function getParams() {
    return $this->params;
  }

  public function isInBatch() {
    return $this->inBatch;
  }

  public function getParameter($key) {
    if ($this->hasParams() && $this->params->has($key)) {
      return $this->params->get($key);
    }
    return NULL;
  }

  public function hasParams() {
    return !(is_null($this->params) || $this->params->empty());
  }

  public function isNotification() {
    return $this->id === FALSE;
  }

  protected function assertValidRequest($version, $method, $id) {
    assert($version === "2.0", 'A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".');
    assert(strpos($method, 'rpc.') !== 0, 'Method names that begin with the word rpc followed by a period character (U+002E or ASCII 46) are reserved for rpc-internal methods and extensions and MUST NOT be used for anything else.');
    assert($id === FALSE || is_string($id) || is_numeric($id) || is_null($id), 'An identifier established by the Client that MUST contain a String, Number, or NULL value if included.');
  }

}
