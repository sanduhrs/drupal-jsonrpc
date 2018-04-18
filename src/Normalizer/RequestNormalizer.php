<?php

namespace Drupal\jsonrpc\Normalizer;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Object\Request;
use Drupal\jsonrpc\ParameterFactory\TypedDataParameterFactory;
use Drupal\jsonrpc\ParameterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class RequestNormalizer implements DenormalizerInterface, SerializerAwareInterface {

  use SerializerAwareTrait;

  /**
   * The parent serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface|\Symfony\Component\Serializer\Normalizer\DenormalizerInterface
   */
  protected $serializer;

  const REQUEST_ID_KEY = 'jsonrpc_request_id';

  const REQUEST_VERSION_KEY = 'jsonrpc_request_version';

  /**
   * The JSON-RPC handler.
   *
   * @var \Drupal\jsonrpc\HandlerInterface
   */
  protected $handler;

  /**
   * The TypedData manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedData;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function __construct(HandlerInterface $handler, TypedDataManagerInterface $typed_data_manager, ContainerInterface $container) {
    $this->handler = $handler;
    $this->typedData = $typed_data_manager;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return $type === Request::class && $format === 'json';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    if ($this->isBatchRequest($data)) {
      return array_map(function ($item) use ($context) {
        return $this->denormalizeRequest($item, $context);
      }, $data);
    }
    return $this->denormalizeRequest($data, $context);
  }

  /**
   * Denormalizes a single JSON-RPC request object.
   *
   * @param object $data
   *   The decoded JSON-RPC request to be denormalized.
   * @param array $context
   *   The denormalized JSON-RPC request.
   *
   * @return \Drupal\jsonrpc\Object\Request
   *   The JSON-RPC request.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function denormalizeRequest($data, array $context) {
    $id = isset($data['id']) ? $data['id'] : FALSE;
    $context[static::REQUEST_ID_KEY] = $id;
    $context[static::REQUEST_VERSION_KEY] = $this->handler::supportedVersion();
    return ($params = $this->denormalizeParams($data, $context))
      ? new Request($data['jsonrpc'], $data['method'], $id, $params)
      : new Request($data['jsonrpc'], $data['method'], $id);
  }

  /**
   * Denormalizes a JSON-RPC request object's parameters.
   *
   * @param object $data
   *   The decoded JSON-RPC request to be denormalized.
   * @param array $context
   *   The denormalized JSON-RPC request.
   *
   * @return \Drupal\jsonrpc\Object\ParameterBag|null
   *   The denormalized parameters or NULL if none were provided.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function denormalizeParams($data, array $context) {
    if (!$this->handler->supportsMethod($data['method'])) {
      throw $this->newException(Error::methodNotFound($data['method']), $context);
    }
    $method = $this->handler->getMethod($data['method']);
    $params = $method->getParams();
    if (is_null($params)) {
      if (isset($data['params'])) {
        $error = Error::invalidParams("The ${data['method']} method does not accept parameters.");
        throw $this->newException($error, $context);
      }
      return NULL;
    }
    $arguments = [];
    $positional = $method->areParamsPositional();
    foreach ($params as $key => $param) {
      if (!isset($data['params'][$key])) {
        throw $this->newException(Error::invalidParams("Missing parameter: $key"), $context);
      }
      $arguments[$key] = $this->denormalizeParam($data['params'][$key], $param);
    }
    return new ParameterBag($arguments, $positional);
  }

  /**
   * Denormalizes a single JSON-RPC request object parameter.
   *
   * @param mixed $argument
   *   The decoded JSON-RPC request parameter to be denormalized.
   * @param \Drupal\jsonrpc\ParameterInterface $parameter
   *   The JSON-RPC request's parameter definition.
   *
   * @return mixed
   *   The denormalized parameter.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function denormalizeParam($argument, ParameterInterface $parameter) {
    if ($data_type = $parameter->getDataType()) {
      $factory_class = TypedDataParameterFactory::class;
    }
    else {
      $factory_class = $parameter->getFactory();
    }
    $container_injection = in_array(ContainerInjectionInterface::class, class_implements($factory_class));
    $factory = $container_injection ? $factory_class::create($this->container) : new $factory_class;
    if ($factory instanceof TypedDataParameterFactory) {
      $factory->setDataType($data_type);
    }
    return $factory->convert($argument, $parameter);
  }

  /**
   * Determine if the request is a batch request.
   *
   * @param array $data
   *   The raw HTTP request data.
   *
   * @return bool
   *   Whether the HTTP request contains more than one RPC request.
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   *   Thrown if the request contains RPC requests without a 'jsonrpc' member.
   */
  protected function isBatchRequest(array $data) {
    if (isset($data['jsonrpc'])) {
      return FALSE;
    }
    $supported_version = $this->handler::supportedVersion();
    $filter = function ($version) use ($supported_version) {
      return $version === $supported_version;
    };
    if (count(array_filter(array_column($data, 'jsonrpc'), $filter)) === count($data)) {
      return TRUE;
    }
    throw JsonRpcException::fromError(Error::invalidRequest("Every request must include a 'jsonrpc' member with a value of $supported_version."));
  }

  /**
   * Helper for creating an error RPC response exception.
   *
   * @param \Drupal\jsonrpc\Object\Error $error
   *   The JSON-RPC Error.
   * @param array $context
   *   The JSON-RPC request context.
   *
   * @return \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function newException(Error $error, array $context) {
    return JsonRpcException::fromError($error, $context[static::REQUEST_ID_KEY], $context[static::REQUEST_VERSION_KEY]);
  }

}
