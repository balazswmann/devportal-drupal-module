<?php

/**
 * @file
 * Page callbacks for API Response Header entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Response Header templates.
 *
 * Default template: api-response-header.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Response Header
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_response_header(array &$variables) {
  // Fetch API Response Header Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIResponseHeader $entity */
  $entity = $variables['elements']['#api_response_header'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
