<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\devportal_api_entities\APIResponseHeaderInterface;
use Drupal\devportal_api_reference\Traits\APIRefTrait;
use Drupal\devportal_api_entities\Traits\APIResponseRefTrait;
use Drupal\devportal_api_entities\Traits\AutoLabelTrait;
use Drupal\devportal_api_entities\Traits\ItemTrait;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefTrait;
use Drupal\devportal\Traits\URLRouteParametersTrait;;
use Drupal\devportal_api_entities\Traits\VendorExtensionTrait;

/**
 * Defines the API Response Header entity class.
 *
 * @ContentEntityType(
 *   id = "api_response_header",
 *   label = @Translation("API Response Header"),
 *   handlers = {
 *     "storage" = "Drupal\devportal_api_entities\APIResponseHeaderStorage",
 *     "list_builder" = "Drupal\devportal_api_entities\APIResponseHeaderListBuilder",
 *     "view_builder" = "Drupal\devportal\DevportalContentEntityViewBuilder",
 *     "views_data" = "Drupal\devportal_api_entities\APIResponseHeaderViewsData",
 *     "form" = {
 *       "default" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "add" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "edit" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "delete" = "Drupal\devportal\Form\DevportalContentEntityDeleteForm",
 *     },
 *     "inline_form" = "Drupal\devportal_api_entities\Form\DevportalInlineForm",
 *     "route_provider" = {
 *       "html" = "Drupal\devportal_api_entities\APIResponseHeaderHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\devportal_api_entities\APIResponseHeaderAccessControlHandler",
 *     "translation" = "Drupal\devportal_api_entities\APIResponseHeaderTranslationHandler",
 *   },
 *   admin_permission = "administer api response headers",
 *   fieldable = TRUE,
 *   base_table = "api_response_header",
 *   data_table = "api_response_header_field_data",
 *   field_ui_base_route = "entity.api_response_header_type.edit_form",
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
 *     "vendor_extension" = "extensions",
 *     "auto_label_source" = "name",
 *     "item" = {
 *       "type" = "param_type",
 *       "format" = "format",
 *       "items" = "api_param_item",
 *       "collection_format" = "collection_format",
 *       "default" = "param_default",
 *     },
 *     "api_response" = "api_response",
 *     "api_ref" = "api_ref",
 *     "api_version_tag" = "api_version_tag",
 *   },
 *   bundle_entity_type = "api_response_header_type",
 *   bundle_label = @Translation("API Response Header type"),
 *   revision_table = "api_response_header_revision",
 *   revision_data_table = "api_response_header_field_revision",
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/api_response_header/{api_response_header}",
 *     "add-page" = "/api_response_header/add",
 *     "add-form" = "/api_response_header/add/{api_response_header_type}",
 *     "edit-form" = "/api_response_header/{api_response_header}/edit",
 *     "delete-form" = "/api_response_header/{api_response_header}/delete",
 *     "collection" = "/admin/content/api_response_header",
 *     "version-history" = "/api_response_header/{api_response_header}/revisions",
 *     "revision" = "/api_response_header/{api_response_header}/revisions/{api_response_header_revision}/view",
 *     "revision_revert" = "/api_response_header/{api_response_header}/revisions/{api_response_header_revision}/revert",
 *     "revision_delete" = "/api_response_header/{api_response_header}/revisions/{api_response_header_revision}/delete",
 *     "multiple_delete_confirm" = "/admin/content/api_response_header/delete",
 *     "translation_revert" = "/api_response_header/{api_response_header}/revisions/{api_response_header_revision}/revert/{langcode}",
 *   },
 *   translatable = TRUE,
 * )
 */
class APIResponseHeader extends RevisionableContentEntityBase implements APIResponseHeaderInterface {

  use EntityChangedTrait;
  use AutoLabelTrait;
  use ItemTrait;
  use APIResponseRefTrait;
  use APIRefTrait;
  use APIVersionTagRefTrait;
  use VendorExtensionTrait;
  use URLRouteParametersTrait;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

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
      // If we are updating an existing APIResponseHeader without adding a new
      // revision, we need to make sure $entity->revision_log is reset whenever
      // it is empty. Therefore, this code allows us to avoid clobbering an
      // existing log entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the auto label field.
    $fields += static::autoLabelBaseFieldDefinitions($entity_type);

    // Add the vendor extension fields.
    $fields += static::vendorExtensionBaseFieldDefinitions($entity_type);

    // Add the item fields.
    $fields += static::itemBaseFieldDefinitions($entity_type);

    // Add the API Response field.
    $fields += static::apiResponseBaseFieldDefinitions($entity_type);
    $fields['api_response']->setDescription(t('API Response referenced from API Response Header.'));

    // Add the API Reference field.
    $fields += static::apiRefBaseFieldDefinitions($entity_type);
    $fields['api_ref']->setDescription(t('API Reference referenced from API Response Header.'));

    // Add the API Version Tag field.
    $fields += static::apiVersionTagBaseFieldDefinitions($entity_type);
    $fields['api_version_tag']->setDescription(t('API Version Tag referenced from API Response Header.'));

    $fields['id']->setDescription(t('The API Response Header ID.'));

    $fields['uuid']->setDescription(t('The API Response Header UUID.'));

    $fields['vid']->setDescription(t('The API Response Header revision ID.'));

    $fields['langcode']->setDescription(t('The API Response Header language code.'));

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the API Response Header was last edited.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $label = parent::label();
    if (empty($label)) {
      $label = $this->generateAutoLabel();
    }
    return $label;
  }

}
