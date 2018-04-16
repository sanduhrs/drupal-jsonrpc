<?php

namespace Drupal\jsonrpc\Normalizer;

use Drupal\jsonrpc\Object\Response;
use Drupal\serialization\Normalizer\NormalizerBase;

class ResponseNormalizer extends NormalizerBase {

  const RESPONSE_VERSION_KEY = RequestNormalizer::REQUEST_VERSION_KEY;

  /**
   * The parent serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface|\Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected $serializer;

  protected $supportedInterfaceOrClass = Response::class;

  protected $format = 'rpc_json';

  public function normalize($object, $format = NULL, array $context = []) {
    /* @var \Drupal\jsonrpc\Object\Response $object */
    $normalized = [
      'jsonrpc' => $context[static::RESPONSE_VERSION_KEY],
      'id' => $object->id(),
    ];
    if ($object->isResultResponse()) {
      $normalized['result'] = $this->serializer->normalize($object->getResult(), $format, $context);
    }
    if ($object->isErrorResponse()) {
      $normalized['error'] = $this->serializer->normalize($object->getError(), $format, $context);
    }
    return $normalized;
  }

}