<?php

use Drupal\Core\Plugin\PluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInjectionInterface;

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
   * @var \Drupal\Core\Plugin\PluginManagerInterface
   */
  protected $serviceManager;

  /**
   * Instantiates a Routes object.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $resource_type_repository
   *   The JSON API resource type repository.
   * @param \Drupal\Core\Authentication\AuthenticationCollectorInterface $auth_collector
   *   The authentication provider collector.
   */
  public function __construct(PluginManagerInterface $service_manager) {
    $this->serviceManager = $plugin_manager;
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
