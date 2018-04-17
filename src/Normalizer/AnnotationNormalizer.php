<?php

namespace Drupal\jsonrpc\Normalizer;

use Drupal\Component\Annotation\AnnotationBase;
use Drupal\Component\Annotation\AnnotationInterface;
use Drupal\Component\Assertion\Inspector;
use Drupal\Component\Utility\NestedArray;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcMethodParameter;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\serialization\Normalizer\NormalizerBase;

class AnnotationNormalizer extends NormalizerBase {

  const DEPTH_KEY = __CLASS__ . '_depth';

  /**
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  protected $format = 'rpc_json';

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = [
    JsonRpcService::class,
    JsonRpcMethod::class,
    JsonRpcMethodParameter::class,
  ];

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $normalized = [];
    foreach ($object as $key => $value) {
      switch ($key) {
        case 'id':
          break;

        case 'access':
          break;

        default:
          if ($value instanceof AnnotationInterface || (is_array($value)) && Inspector::assertAllObjects($value, AnnotationInterface::class)) {
            if (isset($context[static::DEPTH_KEY]) && $context[static::DEPTH_KEY] > 0) {
              $context[static::DEPTH_KEY] -= 1;
              $normalized[$key] = $this->serializer->normalize($value->get(), $format, $context);
            }
          }
          else {
            $normalized[$key] = $this->serializer->normalize($value, $format, $context);
          }
      }
    }
    return [
      'type' => get_class($object),
      'id' => $object->getId(),
      'attributes' => array_filter($normalized),
    ];
  }

}