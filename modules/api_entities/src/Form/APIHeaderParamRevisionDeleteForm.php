<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting an API Header Param revision.
 *
 * @ingroup devportal_api_entities
 */
class APIHeaderParamRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The API Header Param revision.
   *
   * @var \Drupal\devportal_api_entities\APIHeaderParamInterface
   */
  protected $revision;

  /**
   * The API Header Param storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $apiHeaderParamStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new APIHeaderParamRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection, DateFormatterInterface $date_formatter) {
    $this->apiHeaderParamStorage = $entity_storage;
    $this->connection = $connection;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManager $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Database\Connection $connection */
    $connection = $container->get('database');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    return new static(
      $entity_type_manager->getStorage('api_header_param'),
      $connection,
      $date_formatter
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_header_param_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
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
    return $this->t('Delete');
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
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->apiHeaderParamStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('API HTTP Method Header Parameter: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()->addMessage($this->t('Revision from %revision-date of API HTTP Method Header Parameter %title has been deleted.', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
      '%title' => $this->revision->label(),
    ]));
    $form_state->setRedirect(
      'entity.api_header_param.canonical',
       ['api_header_param' => $this->revision->id()]
    );
    $query = $this->connection->select('api_header_param_field_revision');
    $query->addExpression('COUNT(DISTINCT vid)');
    $result = $query->condition('id', $this->revision->id())
      ->execute()
      ->fetchField();
    if ($result > 1) {
      $form_state->setRedirect(
        'entity.api_header_param.version_history',
         ['api_header_param' => $this->revision->id()]
      );
    }
  }

}
