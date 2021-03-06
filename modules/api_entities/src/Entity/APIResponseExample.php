<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\devportal_api_entities\APIResponseExampleInterface;
use Drupal\devportal_api_reference\Traits\APIRefTrait;
use Drupal\devportal_api_entities\Traits\APIResponseRefTrait;
use Drupal\devportal_api_entities\Traits\AutoLabelTrait;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefTrait;
use Drupal\devportal\Traits\URLRouteParametersTrait;;

/**
 * Defines the API Response Example entity class.
 *
 * @ContentEntityType(
 *   id = "api_response_example",
 *   label = @Translation("API Response Example"),
 *   handlers = {
 *     "storage" = "Drupal\devportal_api_entities\APIResponseExampleStorage",
 *     "list_builder" = "Drupal\devportal_api_entities\APIResponseExampleListBuilder",
 *     "view_builder" = "Drupal\devportal\DevportalContentEntityViewBuilder",
 *     "views_data" = "Drupal\devportal_api_entities\APIResponseExampleViewsData",
 *     "form" = {
 *       "default" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "add" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "edit" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "delete" = "Drupal\devportal\Form\DevportalContentEntityDeleteForm",
 *     },
 *     "inline_form" = "Drupal\devportal_api_entities\Form\DevportalInlineForm",
 *     "route_provider" = {
 *       "html" = "Drupal\devportal_api_entities\APIResponseExampleHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\devportal_api_entities\APIResponseExampleAccessControlHandler",
 *     "translation" = "Drupal\devportal_api_entities\APIResponseExampleTranslationHandler",
 *   },
 *   admin_permission = "administer api response examples",
 *   fieldable = TRUE,
 *   base_table = "api_response_example",
 *   data_table = "api_response_example_field_data",
 *   field_ui_base_route = "entity.api_response_example_type.edit_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "revision" = "vid",
 *     "langcode" = "langcode",
 *     "label" = "auto_label",
 *   },
 *   api_extra_info = {
 *     "auto_label" = {
 *       "auto_label" = "auto_label",
 *       "autogenerated_label" = "autogenerated_label",
 *     },
 *     "api_response" = "api_response",
 *     "api_ref" = "api_ref",
 *     "api_version_tag" = "api_version_tag",
 *   },
 *   bundle_entity_type = "api_response_example_type",
 *   bundle_label = @Translation("API Response Example type"),
 *   revision_table = "api_response_example_revision",
 *   revision_data_table = "api_response_example_field_revision",
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/api_response_example/{api_response_example}",
 *     "add-page" = "/api_response_example/add",
 *     "add-form" = "/api_response_example/add/{api_response_example_type}",
 *     "edit-form" = "/api_response_example/{api_response_example}/edit",
 *     "delete-form" = "/api_response_example/{api_response_example}/delete",
 *     "collection" = "/admin/content/api_response_example",
 *     "version-history" = "/api_response_example/{api_response_example}/revisions",
 *     "revision" = "/api_response_example/{api_response_example}/revisions/{api_response_example_revision}/view",
 *     "revision_revert" = "/api_response_example/{api_response_example}/revisions/{api_response_example_revision}/revert",
 *     "revision_delete" = "/api_response_example/{api_response_example}/revisions/{api_response_example_revision}/delete",
 *     "multiple_delete_confirm" = "/admin/content/api_response_example/delete",
 *     "translation_revert" = "/api_response_example/{api_response_example}/revisions/{api_response_example_revision}/revert/{langcode}",
 *   },
 *   translatable = TRUE,
 * )
 */
class APIResponseExample extends RevisionableContentEntityBase implements APIResponseExampleInterface {

  use EntityChangedTrait;
  use AutoLabelTrait;
  use APIResponseRefTrait;
  use APIRefTrait;
  use APIVersionTagRefTrait;
  use URLRouteParametersTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Generate auto label.
    $this->autoLabelPreSave();
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing APIResponseExample without adding a new
      // revision, we need to make sure $entity->revision_log is reset whenever
      // it is empty. Therefore, this code allows us to avoid clobbering an
      // existing log entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function generateAutoLabel() {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $response_first */
    $response_first = $this->get('api_response')
      ->first();
    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityReference $response_first_entity */
    $response_first_entity = $response_first->get('entity');
    /** @var \Drupal\devportal_api_entities\Entity\APIResponse $response */
    $response = $response_first_entity->getTarget()
      ->getValue();

    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $produces_first */
    $produces_first = $this->get('produces')
      ->first();
    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityReference $produces_first_entity */
    $produces_first_entity = $produces_first->get('entity');
    /** @var \Drupal\taxonomy\Entity\Term $produces */
    $produces = $produces_first_entity->getTarget()
      ->getValue();

    return $response->getCode() . ' - ' . $produces->label();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the auto label field.
    $fields += static::autoLabelBaseFieldDefinitions($entity_type);

    // Add the API Response field.
    $fields += static::apiResponseBaseFieldDefinitions($entity_type);
    $fields['api_response']->setDescription(t('API Response referenced from API Response Example.'));

    // Add the API Reference field.
    $fields += static::apiRefBaseFieldDefinitions($entity_type);
    $fields['api_ref']->setDescription(t('API Reference referenced from API Response Example.'));

    // Add the API Version Tag field.
    $fields += static::apiVersionTagBaseFieldDefinitions($entity_type);
    $fields['api_version_tag']->setDescription(t('API Version Tag referenced from API Response Example.'));

    $fields['id']->setDescription(t('The API Response Example ID.'));

    $fields['uuid']->setDescription(t('The API Response Example UUID.'));

    $fields['vid']->setDescription(t('The API Response Example revision ID.'));

    $fields['langcode']->setDescription(t('The API Response Example language code.'));

    $fields['example'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Example'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['produces'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Produces'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSettings([
        'target_type' => 'taxonomy_term',
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => [
            'api_mime_type' => 'api_mime_type',
          ],
          'sort' => [
            'field' => '_none',
          ],
          'auto_create' => FALSE,
          'auto_create_bundle' => '',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 5,
        'settings' => [
          'form_mode' => 'default',
          'override_labels' => TRUE,
          // @FIXME Should these use $this->>t()?
          'label_singular' => 'producible',
          'label_plural' => 'producibles',
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
          'match_operator' => 'CONTAINS',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'weight' => 5,
        'settings' => [
          'link' => FALSE,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the API Response Example was last edited.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function label() {
    $label = parent::label();
    if (empty($label)) {
      $label = $this->generateAutoLabel();
    }
    return $label;
  }

}
