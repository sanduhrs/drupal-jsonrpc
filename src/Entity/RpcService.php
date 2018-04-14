<?php

namespace Drupal\jsonrpc\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the RPC Service entity.
 *
 * @ConfigEntityType(
 *   id = "rpc_service",
 *   label = @Translation("RPC Service"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\jsonrpc\RpcServiceListBuilder",
 *     "form" = {
 *       "add" = "Drupal\jsonrpc\Form\RpcServiceForm",
 *       "edit" = "Drupal\jsonrpc\Form\RpcServiceForm",
 *       "delete" = "Drupal\jsonrpc\Form\RpcServiceDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\jsonrpc\RpcServiceHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "rpc_service",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/services/rpc_service/{rpc_service}",
 *     "add-form" = "/admin/config/services/rpc_service/add",
 *     "edit-form" = "/admin/config/services/rpc_service/{rpc_service}/edit",
 *     "delete-form" = "/admin/config/services/rpc_service/{rpc_service}/delete",
 *     "collection" = "/admin/config/services/rpc_service"
 *   }
 * )
 */
class RpcService extends ConfigEntityBase implements RpcServiceInterface {

  /**
   * The RPC Service ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The RPC Service label.
   *
   * @var string
   */
  protected $label;

  /**
   * Endpoints.
   *
   * @var array
   */
  protected $endpoints = [];

}
