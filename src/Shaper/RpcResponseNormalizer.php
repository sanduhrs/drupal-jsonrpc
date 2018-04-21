<?php

namespace Drupal\jsonrpc\Shaper;

use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Response;
use Shaper\Transformation\TransformationBase;
use Shaper\Util\Context;
use Shaper\Validator\AcceptValidator;
use Shaper\Validator\CollectionOfValidators;
use Shaper\Validator\InstanceofValidator;

class RpcResponseNormalizer extends TransformationBase {

  const RESPONSE_VERSION_KEY = RpcRequestFactory::REQUEST_VERSION_KEY;
  const REQUEST_IS_BATCH_REQUEST = RpcRequestFactory::REQUEST_IS_BATCH_REQUEST;

  public function getInputValidator() {
    return new CollectionOfValidators(new InstanceofValidator(Response::class));
  }

  public function getOutputValidator() {
    return new AcceptValidator();
  }

  protected function doTransform($data, Context $context) {
    $output = array_map(function (Response $response) use ($context) {
      try {
        return $this->doNormalize($response, $context);
      }
      catch (\Exception $e) {
        return JsonRpcException::fromPrevious($e, $response->id(), $context[static::RESPONSE_VERSION_KEY]);
      }
    }, $data);
    return $context[static::REQUEST_IS_BATCH_REQUEST] ? $output : reset($output);
  }

  protected function doNormalize(Response $response, Context $context) {
    $normalized = [
      'jsonrpc' => $context[static::RESPONSE_VERSION_KEY],
      'id' => $response->id(),
    ];
    if ($response->isResultResponse()) {
      $normalized['result'] = $response->getResult();
    }
    if ($response->isErrorResponse()) {
      $error = $response->getError();
      $normalized['error'] = [
        'code' => $error->getCode(),
        'message' => $error->getMessage(),
        'data' => $error->getData(),
      ];
    }
    return $normalized;
  }

}
