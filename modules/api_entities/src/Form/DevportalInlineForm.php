<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Inline form handler for DP Docs content entity types.
 */
class DevportalInlineForm extends EntityInlineForm {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $fields['label']['label'] = $this->t('Label');
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function entityForm(array $entity_form, FormStateInterface $form_state) {
    $entity_form = parent::entityForm($entity_form, $form_state);

    // Remove the "Revision log" textarea,  it can't be disabled in the
    // form display and doesn't make sense in the inline form context.
    $entity_form['revision_log']['#access'] = FALSE;
    // Remove "Auto label" related fields for the same reason.
    // @FIXME: Use field names from annotation instead of hardcoding them here.
    $entity_form['auto_label']['#access'] = FALSE;
    $entity_form['autogenerated_label']['#access'] = FALSE;

    return $entity_form;
  }

}
