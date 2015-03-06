<?php

/**
 * @file
 */


namespace Drupal\file_archive\Field;

use Drupal\kore\Field;

class file_archive extends Field\Field_Abscract {

  public static function info() {
    return array(
      'file_archive' => array(
        'label' => t('File archive'),
        'description' => t("This field archive files of a file field."),
        'settings' => array(),
        'instance_settings' => array(
          'archive_scheme' => 'public', // Archive file scheme, destination
          'archive_directory' => '', // Directory to save archive
          'description_field' => FALSE, // Enable description field
          'file_field' => NULL, // Target file field
        ),
        'default_widget' => 'file_archive_default',
        'default_formatter' => 'file_archive_default',
      ),
    );
  }

  public static function schema($field) {
    return array(
      'columns' => array(
        'title' => array(
          'description' => 'The title of this archive.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'description' => array(
          'description' => 'A description of the archive.',
          'type' => 'text',
          'size' => 'normal',
          'not null' => FALSE,
        ),
        'fid' => array(
          'description' => 'The {file_managed}.fid being referenced in this field as the archive file.',
          'type' => 'int',
          'not null' => FALSE,
          'unsigned' => TRUE,
        ),
        'files' => array(
          'description' => 'Serialized data of files array in archive.',
          'type' => 'text',
          'size' => 'big',
          'serialize' => TRUE,
          'object default' => array(),
        ),
      ),
      'indexes' => array(
        'fid' => array('fid'),
      ),
      'foreign keys' => array(
        'archive_fid' => array(
          'table' => 'file_managed',
          'columns' => array('fid' => 'fid'),
        ),
      ),
    );
  }

  public static function instance_settings_form($field, $instance) {
    $settings = $instance['settings'];
    $form = array();

    // Description field
    $form['description_field'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable %description field', array('%description' => 'description')),
      '#default_value' => $settings['description_field'],
    );

    // File field
    // @todo Better implementation.
    $fields = field_info_fields();
    $options = drupal_map_assoc(array_keys(array_filter($fields, 'self::is_file_field')));
    $form['file_field'] = array(
      '#type' => 'select',
      '#title' => t('Select the file field'),
      '#options' => $options,
      '#default_value' => $settings['file_field'],
    );

    // Archive
    $form['archive_scheme'] = array(
      '#type' => 'radios',
      '#title' => t('Scheme'),
      '#destination' => t('Storage destination for archive'),
      '#options' => \Drupal\ko\File::schemeOptions(),
      '#default_value' => $settings['archive_scheme'],
    );
    $form['archive_directory'] = array(
      '#type' => 'textfield',
      '#title' => t('Directory'),
      '#default_value' => $settings['archive_directory'],
      '#description' => theme('token_tree_link', array('token_types' => array($instance['entity_type'], 'file', 'user'))),
      '#element_validate' => array('token_element_validate'),
      '#token_types' => array($instance['entity_type'], 'file', 'user'),
    );

    return $form;
  }

  public static function is_empty($item, $field) {
    if (!empty($item['title']) && $item['archive']) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Unserialize 'files' column.
   */
  public static function load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {
    foreach ($items as $id => $item) {
      if ($item) {
        $items[$id][0]['files'] = unserialize($item[0]['files']);
      }
    }
  }

  /**
   * Prepare 'files'.
   */
  public static function presave($entity_type, $entity, $field, $instance, $langcode, &$items) {
    if (count($items) == 0) {
      return;
    }
    
    $file_items = field_get_items($entity_type, $entity, $instance['settings']['file_field'], $langcode);
    $files = array();
    foreach ($file_items as $file_item) {
      $files[] = $file_item['fid'];
    }
    $items[0]['files'] = serialize($files);
  }

  /**
   * Save archive file.
   */
  public static function save($entity_type, $entity, $field, $instance, $langcode, &$items) {
    if (count($items) == 0) {
      return;
    }

    // @todo Description field.

    // Archive field value
    $files = unserialize($items[0]['files']);
    list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $entity_unchanged = entity_load_unchanged($entity_type, $id);
    $items_unchanged = field_get_items($entity_type, $entity_unchanged, $instance['field_name'], $langcode);
    if (!empty($entity_unchanged) && isset($items_unchanged[0]) && !empty($items_unchanged[0]['fid'])) {
      $archive_file = $items_unchanged[0]['fid'];
    }
    else {
      $name = array(
        $entity_type,
        $id,
        $instance['field_name'],
      );
      $archive_file = $instance['settings']['archive_scheme'] . '://' . $instance['settings']['archive_directory'] . '/' . implode('-', $name) . '.zip';
    }
    $archiver = new \ArchiverZipFile($archive_file);
    $archiver->removeAll();
    foreach ($files as $fid) {
      $archiver->addFile($fid);
    }
    $archive = $archiver->save();
    // dpm($archive);
    $items[0]['fid'] = $archive->fid;
  }

  /**
   * Filter file field.
   */
  public static function is_file_field($field) {
    return ($field['type'] == 'file' || $field['type'] == 'image');
  }

}
