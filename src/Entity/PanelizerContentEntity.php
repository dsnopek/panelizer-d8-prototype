<?php

/**
 * @file
 * Contains \Drupal\panelizer\Entity\PanelizerContentEntity.
 */

namespace Drupal\panelizer\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\page_manager\PageInterface;

/**
 * Defines the Panelizer content entity class.
 *
 * @ContentEntityType(
 *   id = "panelizer",
 *   label = @Translation("Panelizer"),
 *   controllers = {
 *     "storage" = "Drupal\panelizer\PanelizerContentEntityStorage"
 *   },
 *   base_table = "panelizer_entity",
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "pid",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class PanelizerContentEntity extends ContentEntityBase implements PageInterface {
  /**
   * The status (enabled/disabled) of the page entity.
   *
   * @var boolean
   */
  protected $status;

  /**
   * The configuration of the page variants.
   *
   * @var array
   */
  protected $page_variants = array();

  /**
   * The configuration of access conditions.
   *
   * @var array
   */
  protected $access_conditions = array();

  /**
   * Tracks the logic used to compute access, either 'and' or 'or'.
   *
   * @var string
   */
  protected $access_logic = 'and';

  /**
   * The plugin bag that holds the page variants.
   *
   * @var \Drupal\Component\Plugin\PluginBag
   */
  protected $pageVariantBag;

  /**
   * The plugin bag that holds the access conditions.
   *
   * @var \Drupal\Component\Plugin\PluginBag
   */
  protected $accessConditionBag;

  /**
   * Indicates if this page should be displayed in the admin theme.
   *
   * @var bool
   */
  protected $use_admin_theme;

  /**
   * Stores a reference to the executable version of this page.
   *
   * This is only used on runtime, and is not stored.
   *
   * @var \Drupal\page_manager\PageExecutable
   */
  protected $executable;

  /**
   * {@inheritdoc}
   */
  public function status() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function getExecutable() {
    if (!isset($this->executable)) {
      // @todo Use a factory.
      $this->executable = new PageExecutable($this);
    }
    return $this->executable;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $properties = parent::toArray();
    $names = array(
      'page_variants',
      'access_conditions',
      'access_logic',
      'use_admin_theme',
    );
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    // TODO: For a 'Full page override' we *could* give the entity path, but
    // for other display modes there isn't anything we can do here.
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function usesAdminTheme() {
    // TODO: Since we aren't always a full page, how do we address this?
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(EntityStorageInterface $storage) {
    parent::postCreate($storage);
    // Ensure there is at least one page variant.
    if (!$this->getPageVariants()->count()) {
      // TODO: Call $this->addPageVariant() with the default PageVariant,
      // which could have been created by the user or the default-default.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addPageVariant(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getPageVariants()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPageVariant($page_variant_id) {
    return $this->getPageVariants()->get($page_variant_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removePageVariant($page_variant_id) {
    $this->getPageVariants()->removeInstanceId($page_variant_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPageVariants() {
    if (!$this->pageVariantBag) {
      $this->pageVariantBag = new PageVariantBag(\Drupal::service('plugin.manager.page_variant'), $this->page_variants);
      $this->pageVariantBag->sort();
    }
    return $this->pageVariantBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginBags() {
    return array(
      'page_variants' => $this->getPageVariants(),
      'access_conditions' => $this->getAccessConditions(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessConditions() {
    // TODO: Access conditions really don't make sense - we should use normal
    // entity access.
    if (!$this->accessConditionBag) {
      $this->accessConditionBag = new ConditionPluginBag(\Drupal::service('plugin.manager.condition'), $this->access_conditions);
    }
    return $this->accessConditionBag;
  }

  /**
   * {@inheritdoc}
   */
  public function addAccessCondition(array $configuration) {
    // TODO: Access conditions really don't make sense - we should use normal
    // entity access.
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getAccessConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessCondition($condition_id) {
    // TODO: Access conditions really don't make sense - we should use normal
    // entity access.
    return $this->getAccessConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeAccessCondition($condition_id) {
    // TODO: Access conditions really don't make sense - we should use normal
    // entity access.
    $this->getAccessConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessLogic() {
    // TODO: Access conditions really don't make sense - we should use normal
    // entity access.
    return $this->access_logic;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    return $this->getExecutable()->getContexts();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['pid'] = FieldDefinition::create('integer')
      ->setLabel(t('Panelizer Entity ID'))
      ->setDescription(t('The Panelizer Entity ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The Panelizer Entity UUID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['page_variants'] = FieldDefinition::create('map')
      ->setLabel(t('Page variants'))
      ->setDescription(t('Serialized bag of PageVariants.'));

    $fields['access_conditions'] = FieldDefinition::create('map')
      ->setLabel(t('Access conditions'))
      ->setDescription(t('Serialized bag of Conditions.'));

    $fields['access_logic'] = FieldDefinition::create('string')
      ->setLabel(t('Access logic'))
      ->setDescription(t('The logic used to compute access, either "and" or "or".'));

    return $fields;
  }
}
