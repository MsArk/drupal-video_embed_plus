<?php

namespace Drupal\video_embed_plus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Provides a settings form for Video Embed Plus.
 */
class VideoEmbedPlusSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'video_embed_plus_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['video_embed_plus.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('video_embed_plus.settings');

    $content_types = NodeType::loadMultiple();
    $options = [];
    foreach ($content_types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types to attach video embed field'),
      '#options' => $options,
      '#default_value' => $config->get('content_types') ?? [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * Handles saving settings and updating fields accordingly.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('video_embed_plus.settings');
    $old_types = $config->get('content_types') ?? [];
    $new_types = array_filter($form_state->getValue('content_types'));

    // Save the new configuration.
    $config->set('content_types', $new_types)->save();

    // Determine removed content types.
    $removed_types = array_diff($old_types, $new_types);
    foreach ($removed_types as $type) {
      $this->removeEmbedFieldIfExists($type);
    }

    // Create the field for newly selected types.
    foreach ($new_types as $type) {
      $this->createEmbedFieldIfNotExists($type);
    }
  }

  /**
   * Removes the video embed field from a given content type if it exists.
   *
   * @param string $type
   *   The machine name of the content type.
   */
  protected function removeEmbedFieldIfExists(string $type) {
    $field_name = 'field_video_embed';

    // Delete only the field config (not the shared storage).
    if ($field_config = FieldConfig::loadByName('node', $type, $field_name)) {
      $field_config->delete();
    }

    // Optionally remove from form and view displays for cleanup.
    $form_display = \Drupal::service('entity_display.repository')->getFormDisplay('node', $type, 'default');
    if ($form_display->getComponent($field_name)) {
      $form_display->removeComponent($field_name)->save();
    }

    $view_display = \Drupal::service('entity_display.repository')->getViewDisplay('node', $type, 'default');
    if ($view_display->getComponent($field_name)) {
      $view_display->removeComponent($field_name)->save();
    }
  }

  /**
   * Creates the video embed field if it doesn't exist for the given content type.
   *
   * @param string $type
   *   The machine name of the content type.
   */
  protected function createEmbedFieldIfNotExists(string $type) {
    $field_name = 'field_video_embed';

    // Create the field storage if it doesn't exist yet.
    if (!FieldStorageConfig::loadByName('node', $field_name)) {
      FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => 'video_embed_field',
        'cardinality' => 1,
      ])->save();
    }

    // Create the field config for the specific content type if it doesn't exist.
    if (!FieldConfig::loadByName('node', $type, $field_name)) {
      FieldConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'bundle' => $type,
        'label' => 'Video',
        'settings' => [],
      ])->save();
    }

    // Add the field to the form display.
    $form_display = \Drupal::service('entity_display.repository')
      ->getFormDisplay('node', $type, 'default');
    $form_display->setComponent($field_name, [
      'type' => 'video_embed_field_textfield',
      'weight' => 20,
      'settings' => [],
    ])->save();

    // Add the field to the view display.
    $view_display = \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', $type, 'default');
    $view_display->setComponent($field_name, [
      'type' => 'video_embed_field_thumbnail',
      'label' => 'above',
      'weight' => 20,
      'settings' => [],
    ])->save();
  }
}
