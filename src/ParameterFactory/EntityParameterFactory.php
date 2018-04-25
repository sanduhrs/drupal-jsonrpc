<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\ParameterDefinitionInterface;
use JsonSchema\Validator;
use Shaper\Util\Context;
use Shaper\Validator\InstanceofValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityParameterFactory extends ParameterFactoryBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * EntityParameterFactory constructor.
   *
   * @param \JsonSchema\Validator $validator
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   */
  public function __construct(Validator $validator, EntityRepositoryInterface $entity_repository) {
    parent::__construct($validator);
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ParameterDefinitionInterface $definition, ContainerInterface $container) {
    return new static(
      $definition,
      $container->get('jsonrpc.schema_validator'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(ParameterDefinitionInterface $parameter_definition = NULL) {
    return [
      'type' => 'object',
      'properties' => [
        'type' => ['type' => 'string'],
        'uuid' => ['type' => 'string'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOutputValidator() {
    return new InstanceofValidator(EntityInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  protected function doTransform($data, Context $context = NULL) {
    try {
      if ($entity = $this->entityRepository->loadEntityByUuid($data['type'], $data['uuid'])) {
        return $entity;
      }
      throw JsonRpcException::fromError(Error::invalidParams('The requested entity could not be found.'));
    }
    catch (EntityStorageException $e) {
      throw JsonRpcException::fromError(Error::invalidParams('This entity type is not supported. Error: ' . $e->getMessage()));
    }
  }

}
