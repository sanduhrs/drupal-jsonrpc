<?php

namespace Drupal\jsonrpc_core\TypedData;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a data type definition for defining pagination options.
 *
 * @DataType(
 *   id = "offset_limit_paginator",
 *   label = @Translation("Offset Limit Paginator"),
 *   description = @Translation("Defines pagination information."),
 * )
 */
class OffsetLimitPaginatorDefinition extends ComplexDataDefinitionBase {

  public function getPropertyDefinitions() {
    if (!isset($this->propertyDefinitions)) {
      $this->propertyDefinitions['offset'] = DataDefinition::create('integer')
        ->setLabel('Offset')
        ->addConstraint('Range', ['min' => 0])
        ->setRequired(TRUE);
      $this->propertyDefinitions['limit'] = DataDefinition::create('integer')
        ->setLabel('Limit')
        ->addConstraint('Range', ['min' => 0])
        ->setRequired(TRUE);
    }
    return $this->propertyDefinitions;
  }

}
