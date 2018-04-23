<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\jsonrpc\ParameterDefinitionInterface;
use Drupal\jsonrpc\ParameterFactoryInterface;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Transformation\TransformationBase;
use Shaper\Validator\JsonSchemaValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ParameterFactoryBase extends TransformationBase implements ParameterFactoryInterface {

  /**
   * @var \JsonSchema\Validator
   */
  protected $validator;

  /**
   * @var \Shaper\Validator\ValidateableInterface
   */
  protected $inputValidator;

  /**
   * @var \Drupal\jsonrpc\ParameterDefinitionInterface
   */
  protected $definition;

  /**
   * ParameterFactoryBase constructor.
   *
   * @param \JsonSchema\Validator $validator
   */
  public function __construct(ParameterDefinitionInterface $definition, Validator $validator) {
    $this->validator = $validator;
    $this->definition = $definition;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ParameterDefinitionInterface $definition, ContainerInterface $container) {
    return new static($definition, $container->get('jsonrpc.schema_validator'));
  }

  public function getInputValidator() {
    if (!$this->inputValidator) {
      $schema = $this->definition->getSchema();
      $this->inputValidator = new JsonSchemaValidator(
        $schema,
        $this->validator,
        Constraint::CHECK_MODE_TYPE_CAST
      );
    }
    return $this->inputValidator;
  }

}
