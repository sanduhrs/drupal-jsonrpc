<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\ParameterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class TypedDataParameterFactory extends ParameterFactoryBase {

  /**
   * The TypedData manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedData;

  /**
   * The TypedData data type.
   *
   * @var string
   */
  protected $dataType;

  /**
   * {@inheritdoc}
   */
  public function __construct(TypedDataManagerInterface $typed_data_manager) {
    $this->typedData = $typed_data_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('typed_data_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(ParameterInterface $parameter) {
    // @todo: map TypedData data type from the param data_type to actual JSON Schema.
    return [
      'type' => 'string',
    ];
  }

  /**
   * @return string
   */
  public function getDataType() {
    return $this->dataType;
  }

  /**
   * @param string $data_type
   */
  public function setDataType($data_type) {
    $this->dataType = $data_type;
  }

  /**
   * @param mixed $input
   * @param \Drupal\jsonrpc\ParameterInterface $parameter
   *
   * @return mixed
   * @throws \Drupal\jsonrpc\Exception\JsonRpcException
   */
  protected function doConvert($input, ParameterInterface $parameter) {
    $data_definition = $this->typedData->createDataDefinition($this->getDataType());
    if (in_array(Map::class, class_parents($data_definition->getClass()))) {
      $input = (array) $input;
    }
    $converted = $this->typedData->create($data_definition, $input);
    if (($violations = $converted->validate()) && $violations->count()) {
      $error = Error::invalidParams(array_map(function (ConstraintViolationInterface $violation) {
        return $violation->getMessage();
      }, iterator_to_array($violations)));
      throw JsonRpcException::fromError($error);
    };
    return $converted;
  }

}
