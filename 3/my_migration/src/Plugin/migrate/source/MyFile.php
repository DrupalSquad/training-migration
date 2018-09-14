<?php

namespace Drupal\my_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Files (images only).
 *
 * @MigrateSource(
 *   id = "my_file"
 * )
 */
class MyFile extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('file_managed')
      ->fields('file_managed', array_keys($this->fields()))
      // Ignore unpublished files.
      ->condition('status', '1', '=')
      // Only interested in JPEG image files; that's the bulk of content.
      ->condition('filemime', 'image/jpeg', '=');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'fid' => $this->t('File ID'),
      'uid' => $this->t('User ID'),
      'filename' => $this->t('File name'),
      'uri' => $this->t('File path (in public files dir)'),
      'filemime' => $this->t('File MIME type'),
      'timestamp' => $this->t('File created date UNIX timestamp'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Update uri to remove public:// directory portion.
    $original_path = $row->getSourceProperty('uri');
    $new_path = str_replace('sites/default/files/', 'public://', $original_path);
    $row->setSourceProperty('uri', $new_path);

    return parent::prepareRow($row);
  }

}
