<?php

namespace Drupal\jsonrpc;

use Drupal\Core\Access\AccessibleInterface;

interface ServiceInterface extends AccessibleInterface {

  /**
   * The service methods.
   *
   * @return \Drupal\jsonrpc\MethodInterface[]
   *   The service methods.
   */
  public function getMethods();

  /**
   * A service method definition.
   *
   * @param string $name
   *   The method name of the method definition to get.
   *
   * @return \Drupal\jsonrpc\MethodInterface|null
   *   The service method definition, NULL if it does not exist.
   */
  public function getMethod($name);

  /**
   * The service methods which are available to the current user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The account for which to get accessible methods. The current
   *   account will be used if one is not provided.
   *
   * @return \Drupal\jsonrpc\MethodInterface[]
   *   The available methods.
   */
  public function availableMethods($account = NULL);

}
