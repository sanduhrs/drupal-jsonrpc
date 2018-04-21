<?php

namespace Drupal\jsonrpc\ParameterFactory;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\jsonrpc\Exception\JsonRpcException;
use Drupal\jsonrpc\Object\Error;
use Drupal\jsonrpc\ParameterInterface;
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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.repository'));
  }

  public static function schema(ParameterInterface $parameter) {
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
  public function convert($input, ParameterInterface $parameter) {
    try {
      if ($entity = $this->entityRepository->loadEntityByUuid($input['type'], $input['uuid'])) {
        return $entity;
      }
      throw JsonRpcException::fromError(Error::invalidParams('The requested entity could not be found.'));
    }
    catch (EntityStorageException $e) {
      throw JsonRpcException::fromError(Error::invalidParams('This entity type is not supported. Error: ' . $e->getMessage()));
    }
  }

}
