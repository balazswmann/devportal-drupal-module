<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Url;
use Drupal\devportal_api_entities\APIHeaderParamInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting an APIHeaderParam revision.
 *
 * @ingroup devportal_api_entities
 */
class APIHeaderParamRevisionRevertForm extends ConfirmFormBase {

  /**
   * The APIHeaderParam revision.
   *
   * @var \Drupal\devportal_api_entities\APIHeaderParamInterface
   */
  protected $revision;

  /**
   * The APIHeaderParam storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $apiHeaderParamStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $timeService;

  /**
   * Constructs a new APIHeaderParamRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The APIHeaderParam storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Component\Datetime\TimeInterface $time_service
   *   The time service.
   */
  public function __construct(EntityStorageInterface $entity_storage, DateFormatterInterface $date_formatter, TimeInterface $time_service) {
    $this->apiHeaderParamStorage = $entity_storage;
    $this->dateFormatter = $date_formatter;
    $this->timeService = $time_service;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $entity_storage */
    $entity_storage = $container->get('entity_type.manager')
      ->getStorage('api_header_param');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var \Drupal\Component\Datetime\TimeInterface $time_service */
    $time_service = $container->get('datetime.time');
    return new static(
      $entity_storage,
      $date_formatter,
      $time_service
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_header_param_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert to the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_header_param.version_history', ['api_header_param' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $api_header_param_revision = NULL) {
    $this->revision = $this->apiHeaderParamStorage->loadRevision($api_header_param_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->setRevisionLogMessage($this->t('Copy of the revision from %date.', [
      '%date' => $this->dateFormatter->format($original_revision_timestamp),
    ]));
    $this->revision->save();

    $this->logger('content')->notice('API HTTP Method Header Parameter: reverted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()->addMessage($this->t('API HTTP Method Header Parameter %title has been reverted to the revision from %revision-date.', [
      '%title' => $this->revision->label(),
      '%revision-date' => $this->dateFormatter->format($original_revision_timestamp),
    ]));
    $form_state->setRedirect(
      'entity.api_header_param.version_history',
      ['api_header_param' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\devportal_api_entities\APIHeaderParamInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\devportal_api_entities\APIHeaderParamInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(APIHeaderParamInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime($this->timeService->getRequestTime());

    return $revision;
  }

}
