<?php

/**
 * @file
 */


namespace Drupal\file_archive\Field_Widget;

use Drupal\kore\Field_Widget;

class file_archive_default extends Field_Widget\Field_Widget_Abstract {

  public static function info() {
    return array(
      'file_archive_default' => array(
        'label' => t('Silent file archiver'),
        'description' => t('Default file archive widget.'),
        'field types' => array('file_archive'),
        'settings' => array(),
      ),
    );
  }

  public static function form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
    $element['archive'] = array(
      '#type' => 'value',
      '#value' => TRUE,
    );

    $element['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => !empty($items[$delta]['title']) ? $items[$delta]['title'] : '',
    );
    if ($instance['settings']['description_field']) {
      $element['description'] = array(
        '#type' => 'textarea',
        '#title' => t('Description'),
        '#default_value' => $items[$delta]['description'],
        '#rows' => 3,
      );
    }

    $element['#type'] = 'fieldset';

    return $element;
  }

}
