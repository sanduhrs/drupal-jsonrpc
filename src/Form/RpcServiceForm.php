<?php

namespace Drupal\jsonrpc\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RpcServiceForm.
 */
class RpcServiceForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $rpc_service = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $rpc_service->label(),
      '#description' => $this->t("Label for the RPC Service."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $rpc_service->id(),
      '#machine_name' => [
        'exists' => '\Drupal\jsonrpc\Entity\RpcService::load',
      ],
      '#disabled' => !$rpc_service->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $rpc_service = $this->entity;
    $status = $rpc_service->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label RPC Service.', [
          '%label' => $rpc_service->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label RPC Service.', [
          '%label' => $rpc_service->label(),
        ]));
    }
    $form_state->setRedirectUrl($rpc_service->toUrl('collection'));
  }

}
