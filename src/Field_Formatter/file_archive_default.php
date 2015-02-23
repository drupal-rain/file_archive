<?php

/**
 * @file
 */

namespace Drupal\file_archive\Field_Formatter;

use Drupal\kore\Field_Formatter;

class file_archive_default extends Field_Formatter\Field_Formatter_Abstract {

  public static function info() {
    return array(
      'file_archive_default' => array(
        'label' => t('Archive download link'),
        'field types' => array('file_archive'),
        'settings' => array(),
      ),
    );
  }

  public static function view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
    return NULL;
  }

}
