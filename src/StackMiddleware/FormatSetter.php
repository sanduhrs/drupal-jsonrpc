<?php

namespace Drupal\jsonrpc\StackMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Sets the 'json' format on all requests to JSON RPC-managed routes.
 *
 * @internal
 */
class FormatSetter implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs a FormatSetter object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    if (static::isJsonRpcRequest($request)) {
      $request->setRequestFormat('json');
    }

    return $this->httpKernel->handle($request, $type, $catch);
  }

  /**
   * Checks whether the current request is a JSON-RPC request.
   *
   * Inspects:
   * - request path (uses a heuristic, because e.g. language negotiation may use
   *   path prefixes)
   * - 'Accept' request header value.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return bool
   *   Whether the current request is a JSON API request.
   */
  protected static function isJsonRpcRequest(Request $request) {
    return strpos($request->getPathInfo(), '/jsonrpc/') !== FALSE
      &&
      // Check if the 'Accept' header includes the custom JSON-RPC media type.
      // Note that this is a Drupal-only thing. There is no official JSON-RPC
      // media type.
      count(array_filter($request->getAcceptableContentTypes(), function ($accept) {
        return strpos($accept, 'application/vnd.rpc+json') === 0;
      }));
  }

}
