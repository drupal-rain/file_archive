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
        'field types' => array('file_archive'),
        'settings' => array(),
      ),
    );
  }

}
