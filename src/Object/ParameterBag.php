<?php

namespace Drupal\jsonrpc\Object;

class ParameterBag {

  protected $positional;

  protected $parameters;

  public function __construct(array $parameters, $positional = FALSE) {
    $this->positional = $positional;
    $this->parameters = $positional ? array_values($parameters) : $parameters;
  }

  public function get($key) {
    $this->ensure($key);
    return $this->parameters[$key];
  }

  public function has($key) {
    $this->checkKeyIsValid($key);
    return isset($this->parameters[$key]);
  }

  public function empty() {
    return empty($this->parameters);
  }

  protected function ensure($key) {
    if (!$this->has($key)) {
      throw new \InvalidArgumentException('The parameter does not exist.');
    }
  }

  protected function checkKeyIsValid($key) {
    if ($this->positional && !is_int($key) && $key >= 0) {
      throw new \InvalidArgumentException('The parameters are by-position. Integer key required.');
    }
    elseif (!is_string($key)) {
      throw new \InvalidArgumentException('The parameters are by-name. String key required.');
    }
  }

}