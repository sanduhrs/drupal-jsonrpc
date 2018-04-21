<?php

namespace Drupal\jsonrpc\Shaper;

use Drupal\Component\Serialization\Json;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Response;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Transformation\TransformationBase;
use Shaper\Util\Context;
use Shaper\Validator\AcceptValidator;
use Shaper\Validator\CollectionOfValidators;
use Shaper\Validator\InstanceofValidator;
use Shaper\Validator\JsonSchemaValidator;

class RpcResponseNormalizer extends TransformationBase {

  const RESPONSE_VERSION_KEY = RpcRequestFactory::REQUEST_VERSION_KEY;
  const REQUEST_IS_BATCH_REQUEST = RpcRequestFactory::REQUEST_IS_BATCH_REQUEST;

  /**
   * @var \JsonSchema\Validator
   */
  protected $validator;

  /**
   * @var \Shaper\Validator\ValidateableInterface
   */
  protected $outputValidator;

  /**
   * RpcResponseNormalizer constructor.
   */
  public function __construct(Validator $validator) {
    $this->validator = $validator;
  }

  public function getInputValidator() {
    return new CollectionOfValidators(new InstanceofValidator(Response::class));
  }

  public function getOutputValidator() {
    return $this->outputValidator
      ? $this->outputValidator
      : new AcceptValidator();
  }

  public function setOutputSchema($result_schema) {
    $schema = Json::decode(file_get_contents(__DIR__ . '/response-schema.json'));
    $schema['properties']['result'] = $result_schema;
    $this->outputValidator = new JsonSchemaValidator(
      $schema,
      $this->validator,
      Constraint::CHECK_MODE_TYPE_CAST
    );
  }

  protected function doTransform($data, Context $context) {
    $this->setOutputSchema($data[0]->getResultSchema());
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
