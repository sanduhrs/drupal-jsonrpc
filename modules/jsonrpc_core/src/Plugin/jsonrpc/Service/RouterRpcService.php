<?php

namespace Drupal\jsonrpc_core\Plugin\RpcEndpoint;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Plugin\JsonRpcServiceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @JsonRpcService(
 *   id = "router",
 *   access = {"administer site configuration"},
 *   methods = {@JsonRpcMethod(
 *     name = "rebuild"
 *     usage = @Translation("Rebuilds the application's router. Result is TRUE if the rebuild succeeded, FALSE otherwise"),
 *   )}
 * )
 */
class RouterRpcService extends JsonRpcServiceBase {

  /**
   * The route builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * RouterRpcService constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteBuilderInterface $route_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeBuilder = $route_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild() {
    return $this->routeBuilder->rebuild();
  }

}
