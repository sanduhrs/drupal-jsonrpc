<?php

namespace Drupal\jsonrpc\Normalizer;

use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Object\Request;
use Drupal\jsonrpc\Object\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RequestNormalizer implements DenormalizerInterface {

  use DenormalizerAwareTrait;

  /**
   * The JSON-RPC handler.
   *
   * @var \Drupal\jsonrpc\HandlerInterface
   */
  protected $handler;

  /**
   * {@inheritdoc}
   */
  public function __construct(HandlerInterface $handler) {
    $this->handler = $handler;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return $type === Request::class && $format === 'rpc_json';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $decoded = json_decode($data);
    if (is_array($decoded)) {
      return array_map(function ($item) use ($context) {
        return $this->denormalizeRequest($item, $context);
      }, $decoded);
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
    return ($params = $this->denormalizeParams($data, $context))
      ? new Request($data->jsonrpc, $data->method, isset($data->id) ? $data->id : FALSE, $params)
      : new Request($data->jsonrpc, $data->method, isset($data->id) ? $data->id : FALSE);
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
    if (!$this->handler->supportsMethod($data->method)) {
      throw new JsonRpcException(new Response(
        $this->handler::supportedVersion(),
        isset($data->id) ? $data->id : NULL,
        NULL,
        Error::methodNotFound()
      ));
    }
    if (isset($data->params)) {
      $method = $this->handler->getMethod($data->method);
      $arguments = [];
      $positional = $method->areParamsPositional();
      foreach ($method->getParams() as $key => $param) {
        $arguments[$key] = $this->denormalizer->denormalize($positional ? $data[$key] : $data->{$name}, $param->getDenormalizationClass(), 'rpc_json', $context);
      }
      return new ParameterBag($arguments, $positional);
    }
    return NULL;
  }

}
