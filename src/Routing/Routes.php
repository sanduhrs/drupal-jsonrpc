<?php

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic routes.
 *
 * @internal
 */
class Routes implements ContainerInjectionInterface {

  const PATH_PREFIX = '/jsonrpc';

  /**
   * The JSON-RPC plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $serviceManager;

  /**
   * Instantiates a Routes object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $service_manager
   *   The plugin manager.
   */
  public function __construct(PluginManagerInterface $service_manager) {
    $this->serviceManager = $service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.jsonrpc_service'));
  }


  /**
   * Defines JSON-RPC routes from JsonRpcService plugins.
   */
  public function routes() {
    foreach ($this->serviceManager->getDefinitions() as $service) {
      $defaults = [
        RouteObjectInterface::CONTROLLER_NAME => $service->getClass(),
        'resource_type' => $resource_type->getTypeName(),
      ];
    }
  }

}
