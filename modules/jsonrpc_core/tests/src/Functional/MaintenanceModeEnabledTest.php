<?php

namespace Drupal\Tests\jsonrpc_core\Functional;

use Drupal\Tests\jsonrpc\Functional\JsonRpcTestBase;

/**
 * Test turning the maintenance mode on or off using JSON RPC.
 *
 * @group jsonrpc
 */
class MaintenanceModeEnabledTest extends JsonRpcTestBase {

  protected static $modules = [
    'jsonrpc',
    'jsonrpc_core',
    'basic_auth',
    'serialization',
  ];

  public function testEnablingMaintenanceMode() {

    $enabled_request = [
      'jsonrpc' => '2.0',
      'method' => 'maintenance_mode.enabled',
      'params' => [
        'enabled' => TRUE,
      ],
      'id' => 'maintenance_mode_enabled',
    ];

    // Assert that anonymous users are not able to enable the maintenance page.
    $response = $this->postJson('/jsonrpc', $enabled_request);
    $this->assertSame(401, $response->getStatusCode());

    // Assign correct permission and login.
    $account = $this->createUser(['administer site configuration'], NULL, TRUE);

    // Retry request with basic auth.
    $response = $this->postJson('/jsonrpc', $enabled_request, $account);
    $this->assertSame(200, $response->getStatusCode());

    // Asssert maintenance mode is enabled.
    $this->drupalGet('');
    $this->assertSession()->pageTextContains('Operating in maintenance mode.');

    // Send request to disable maintenance mode.
    $disabled_request = [
      'jsonrpc' => '2.0',
      'method' => 'maintenance_mode.enabled',
      'params' => [
        'enabled' => FALSE,
      ],
      'id' => 'maintenance_mode_disabled',
    ];

    $response = $this->postJson('/jsonrpc', $disabled_request, $account);
    $this->assertSame(200, $response->getStatusCode());

    // Asssert maintenance mode is disabled.
    $this->drupalGet('');
    $this->assertSession()->pageTextNotContains('Operating in maintenance mode.');
  }

}
