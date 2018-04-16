<?php

namespace Drupal\jsonrpc\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for RPC Endpoint plugins.
 */
abstract class RpcEndpointBase extends PluginBase implements RpcEndpointInterface, ContainerFactoryPluginInterface {

  use ContainerAwareTrait;

  /**
   * @var \Drupal\jsonrpc\Plugin\RpcParameter[]
   */
  protected $parameters;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $self = new static($configuration, $plugin_id, $plugin_definition);
    $self->setContainer($container);
    return $self;
  }

  /**
   * Declare the parameters this endpoint accepts.
   *
   * @param array $raw_params
   *   The parameters as parsed from the request.
   *
   * @return \Drupal\jsonrpc\Plugin\RpcParameter[]
   *   The collection of parameters.
   */
  abstract protected function parameterFactory(array $raw_params);

  protected function parameters() {
    if (!$this->parameters) {
      $this->parameters = $this->parameterFactory($this->configuration['params']);
      // Validates the parameters.
      array_walk($this->parameters, function ($param) {
        if (!$param instanceof RpcParameterInterface) {
          // TODO: Throw a proper exception that conforms to the spec.
          throw new \Exception();
        }
      });
    }
    return $this->parameters;
  }

  /**
   * Get the method.
   *
   * @return string
   */
  protected function getMethod() {
    // TODO: Change the ID from $this->id to $this->method.
    return $this->getPluginId();
  }

  /**
   * Get the method.
   *
   * @return \Drupal\Core\TypedData\TranslatableInterface
   */
  protected function getDescription() {
    return $this->getPluginDefinition()->description;
  }

  /**
   * Get the method.
   *
   * @return string
   */
  protected function getUsage() {
    return $this->getPluginDefinition()->usage;
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'execute', AccountInterface $account = NULL, $return_as_object = FALSE) {
    $account = $account ?: \Drupal::currentUser();
    $access_result = NULL;
    $permissions = $this->getPluginDefinition()['permissions'];
    // TODO: Inject current user properly.
    switch ($operation) {
      case 'execute':
        $access_result = $this->accessCallback($operation, $account, $return_as_object);
        foreach ($permissions as $permission) {
          $access_result = $access_result->andIf(
            AccessResult::allowedIfHasPermission($account, $permission)
          );
        }
        break;

      case 'view':
        $access_result = AccessResult::allowedIfHasPermission(
          $account,
          'use json-rpc services'
        );
        break;

      default:
        $access_result = AccessResult::neutral();
        break;
    }
    return $return_as_object ? $access_result : $access_result->isAllowed();
  }

  /**
   * Access callback to run in addition to checking the necessary permissions.
   *
   * @param string $operation
   *   The operation to be performed.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function accessCallback($operation, AccountInterface $account, $return_as_object) {
    return AccessResult::allowed();
  }

}
