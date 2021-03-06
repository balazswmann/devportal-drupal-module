<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Response Example entity type.
 */
class APIResponseExampleViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_response_example']['api_response_example_bulk_form'] = [
      'title' => $this->t('API Response Example operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Response Examples.'),
      'field' => [
        'id' => 'api_response_example_bulk_form',
      ],
    ];

    return $data;
  }

}
