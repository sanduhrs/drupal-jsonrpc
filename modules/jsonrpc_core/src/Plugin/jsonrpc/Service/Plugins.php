<?php

namespace Drupal\jsonrpc_core\Plugin\jsonrpc\Service;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Annotation\Translation;
use Drupal\jsonrpc\Annotation\JsonRpcMethod;
use Drupal\jsonrpc\Annotation\JsonRpcMethodParameter;
use Drupal\jsonrpc\Annotation\JsonRpcService;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\ParameterBag;
use Drupal\jsonrpc\Plugin\JsonRpcServiceBase;
use Drupal\jsonrpc\Plugin\JsonRpcServiceManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Lists the plugin definitions of a given type.
 *
 * @JsonRpcService(
 *   id = "plugins",
 *   usage = @Translation("Provides plugin definitions operations."),
 *   access = {"administer site configuration"},
 *   methods = {@JsonRpcMethod(
 *     name = "list",
 *     usage = @Translation("List defined plugins."),
 *     params = {
 *       "page" = @JsonRpcMethodParameter(data_type="offset_limit_paginator"),
 *       "service" = @JsonRpcMethodParameter(data_type="string"),
 *     }
 *   )}
 * )
 */
class Plugins extends JsonRpcServiceBase {

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
    $request = $configuration[JsonRpcServiceManager::JSONRPC_REQUEST_KEY];
    /* @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
    try {
      $plugin_manager = $container->get($request->getParameter('service')->getValue());
    }
    catch (ServiceNotFoundException $e) {
      throw JsonRpcException::fromPrevious($e, $request->id());
    }
    return new static($configuration, $plugin_id, $plugin_definition, $plugin_manager);
  }

  public function list(ParameterBag $params) {
    $paginator = $params->get('page');
    return array_slice($this->pluginManager->getDefinitions(), $paginator->offset->value, $paginator->limit->value);
  }

}
