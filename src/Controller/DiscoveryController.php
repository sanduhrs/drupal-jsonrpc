<?php

namespace Drupal\jsonrpc\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Http\Exception\CacheableNotFoundHttpException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\MethodInterface;
use Drupal\jsonrpc\Normalizer\AnnotationNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DiscoveryController extends ControllerBase {

  /**
   * The JSON-RPC handler.
   *
   * @var \Drupal\jsonrpc\HandlerInterface
   */
  protected $handler;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * DiscoveryController constructor.
   */
  public function __construct(HandlerInterface $handler, SerializerInterface $serializer) {
    $this->handler = $handler;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('jsonrpc.handler'), $container->get('serializer'));
  }

  public function services() {
    $cacheability = new CacheableMetadata();
    $services = ['data' => $this->collectServices($cacheability)];
    $serialized = $this->serializer->serialize($services, 'rpc_json', [AnnotationNormalizer::DEPTH_KEY => 0]);
    return CacheableJsonResponse::fromJsonString($serialized)->addCacheableDependency($cacheability);
  }

  public function service($service_id) {
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['url.path']);
    $methods = $this->groupByService($this->getAvailableMethods($cacheability), $cacheability);
    if (!isset($methods[$service_id])) {
      throw new CacheableNotFoundHttpException($cacheability);
    }
    $serialized = $this->serializer->serialize($methods[$service_id], 'rpc_json');
    return CacheableJsonResponse::fromJsonString($serialized)->addCacheableDependency($cacheability);
  }

  /**
   * Groups methods be their member service keys.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *   The cacheability information for the current request.
   *
   * @return array
   *   An array of methods, keyed by the service IDs to which they belong.
   *   A method may appear more than once.
   */
  protected function groupByService(array $methods, RefinableCacheableDependencyInterface $cacheability) {
    return array_reduce($this->getAvailableMethods($cacheability), function ($groups, MethodInterface $method) use ($cacheability) {
      foreach ($method->getServices() as $service) {
        $access_result = $service->access('view', NULL, TRUE);
        $cacheability->addCacheableDependency($access_result);
        if ($access_result->isAllowed()) {
          $groups[$service->id()] = $method;
        }
      }
      return $groups;
    }, []);
  }

  /**
   * Collect all the available services.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *   The cacheability information for the current request.
   *
   * @return \Drupal\jsonrpc\ServiceInterface[]
   *   An array of services, with accessible methods.
   */
  protected function collectServices(RefinableCacheableDependencyInterface $cacheability) {
    return array_reduce($this->getAvailableMethods($cacheability), function ($services, MethodInterface $method) use ($cacheability) {
      foreach ($method->getServices() as $service) {
        if (!isset($services[$service->id()])) {
          $access_result = $service->access('view', NULL, TRUE);
          $cacheability->addCacheableDependency($access_result);
          if ($access_result->isAllowed()) {
            $services[$service->id()] = $service;
          }
        }
      }
      return $services;
    }, []);
  }

  /**
   * Gets all accessible methods for the RPC handler.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *   The cacheability information for the current request.
   *
   * @return \Drupal\jsonrpc\MethodInterface[] $methods
   */
  protected function getAvailableMethods(RefinableCacheableDependencyInterface $cacheability) {
    return array_filter($this->handler->supportedMethods(), function (MethodInterface $method) use ($cacheability) {
      $access_result = $method->access('view', NULL, TRUE);
      $cacheability->addCacheableDependency($access_result);
      return $access_result->isAllowed();
    });
  }

}