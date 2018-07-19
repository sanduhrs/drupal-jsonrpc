<?php

namespace Drupal\Tests\jsonrpc\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Class JsonRpcTestBase.
 *
 * @package Drupal\jsonrpc\Tests\Functional
 */
class JsonRpcTestBase extends BrowserTestBase {

  /**
   * Post a request in JSON format.
   *
   * @param string $url
   *   The URL to send the request to.
   * @param array $request
   *   The request structure that will be sent as JSON.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user to be used for Basic Auth authentication.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   * @throws \GuzzleHttp\Exception\GuzzleException
   *   Exceptions from the Guzzle client.
   */
  protected function postJson($url, array $request, AccountInterface $account = NULL) {
    $absolute_url = $this->buildUrl($url);
    $request_options = [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::ALLOW_REDIRECTS => FALSE,
      RequestOptions::JSON => $request,
    ];

    if (NULL !== $account) {
      $request_options[RequestOptions::AUTH] = [
        $account->getAccountName(),
        $account->passRaw,
      ];
    }

    $client = $this->getHttpClient();
    return $client->request('POST', $absolute_url, $request_options);
  }

}
