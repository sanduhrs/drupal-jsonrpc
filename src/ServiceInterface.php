<?php

namespace Drupal\jsonrpc;

interface ServiceInterface {

  /**
   * The service methods.
   *
   * @return \Drupal\jsonrpc\MethodInterface[]
   *   The service methods.
   */
  public function getMethods();

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
