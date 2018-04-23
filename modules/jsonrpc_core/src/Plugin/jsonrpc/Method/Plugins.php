<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Method;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\HandlerInterface;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Plugin\JsonRpcMethodBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Lists the plugin definitions of a given type.
 *
 * @JsonRpcMethod(
 *   id = "plugins.list",
 *   usage = @Translation("List defined plugins."),
 *   access = {"administer site configuration"},
 *   params = {
 *     "page" = @JsonRpcParameterDefinition(factory = "\Drupal\jsonrpc\ParameterFactory\PaginationParameterFactory"),
 *     "service" = @JsonRpcParameterDefinition(schema={"type"="string"}),
 *   }
 * )
 */
class Plugins extends JsonRpcMethodBase {

  /**
   * A plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Plugins constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pluginManager = $plugin_manager;
  }

  /**
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /* @var \Drupal\jsonrpc\Object\Request $request */
    $request = $configuration[HandlerInterface::JSONRPC_REQUEST_KEY];
    /* @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
    try {
      $plugin_manager = $container->get($request->getParameter('service'));
    }
    catch (ServiceNotFoundException $e) {
      throw JsonRpcException::fromError(Error::invalidParams($e->getMessage()));
    }
    return new static($configuration, $plugin_id, $plugin_definition, $plugin_manager);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  public function execute(ParameterBag $params) {
    $paginator = $params->get('page');
    $definitions = $this->pluginManager->getDefinitions();
    foreach ($definitions as $definition) {
      if (!is_array($definition)) {
        throw JsonRpcException::fromError(Error::invalidParams('Object-based plugin definitions are not yet supported.'));
      }
    }
    return array_slice($definitions, $paginator['offset'], $paginator['limit']);
  }

  /**
   * {@inheritdoc}
   */
  public static function outputSchema() {
    return [
      'type' => 'object',
      'patternProperties' => [
        '.{1,}' => [
          'class' => [ 'type' => 'string' ],
          'uri' => [ 'type' => 'string' ],
          'description' => [ 'type' => 'string' ],
          'provider' => [ 'type' => 'string' ],
          'id' => [ 'type' => 'string' ],
        ],
      ],
    ];
  }

}
