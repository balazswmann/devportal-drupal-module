<?php

namespace Drupal\devportal_api_entities\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Redirects to an API Response Example deletion form.
 *
 * @Action(
 *   id = "api_response_example_delete_action",
 *   label = @Translation("Delete API Response Example"),
 *   type = "api_response_example",
 *   confirm_form_route_name = "entity.api_response_example.multiple_delete_confirm"
 * )
 */
class DeleteAPIResponseExample extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The tempstore object.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new DeleteAPIResponseExample object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param AccountInterface $current_user
   *   Current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user) {
    $this->currentUser = $current_user;
    $this->tempStore = $temp_store_factory->get('api_response_example_multiple_delete_confirm');

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var PrivateTempStoreFactory $temp_store_factory */
    $temp_store_factory = $container->get('tempstore.private');
    /** @var AccountInterface $current_user */
    $current_user = $container->get('current_user');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $temp_store_factory,
      $current_user
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $info = [];
    /** @var \Drupal\devportal_api_entities\APIResponseExampleInterface $api_response_example */
    foreach ($entities as $api_response_example) {
      $langcode = $api_response_example->language()->getId();
      $info[$api_response_example->id()][$langcode] = $langcode;
    }
    $this->tempStore->set($this->currentUser->id(), $info);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\devportal_api_entities\APIResponseExampleInterface $object */
    return $object->access('delete', $account, $return_as_object);
  }

}
