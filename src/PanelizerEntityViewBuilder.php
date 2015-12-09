<?php
/**
 * Contains \Drupal\panelizer\PanelizerEntityViewBuilder.
 */

namespace Drupal\panelizer;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\panelizer\Plugin\PanelizerEntityManager;
use Drupal\Panels\PanelsDisplayManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity view builder for entities that can be panelized.
 */
class PanelizerEntityViewBuilder extends EntityViewBuilder {

  /**
   * The Panelizer entity manager.
   *
   * @var \Drupal\panelizer\Plugin\PanelizerEntityManager
   */
  protected $panelizerManager;

  /**
   * The Panels display manager.
   *
   * @var \Drupal\Panels\PanelsDisplayManagerInterface
   */
  protected $panelsManager;

  /**
   * The Panelizer entity plugin for this entity type.
   *
   * @var \Drupal\panelizer\Plugin\PanelizerEntityInterface
   */
  protected $panelizerPlugin;

  /**
   * Constructs a new EntityViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\panelizer\Plugin\PanelizerEntityManager $panelizer_manager
   *   The Panelizer entity manager.
   * @param \Drupal\Panels\PanelsDisplayManagerInterface $panels_manager
   *   The Panels display manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, PanelizerEntityManager $panelizer_manager, PanelsDisplayManagerInterface $panels_manager) {
    parent::__construct($entity_type, $entity_manager, $language_manager);
    $this->panelizerManager = $panelizer_manager;
    $this->panelsManager = $panels_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('plugin.manager.panelizer_entity'),
      $container->get('panels.display_manager')
    );
  }

  /**
   * Get the Panelizer entity plugin.
   *
   * @return \Drupal\panelizer\Plugin\PanelizerEntityInterface|FALSE
   */
  protected function getPanelizerPlugin() {
    if (!isset($this->panelizerPlugin)) {
      if (!$this->panelizerManager->hasDefinition($this->entityTypeId)) {
        $this->panelizerPlugin = FALSE;
      }
      else {
        $this->panelizerPlugin = $this->panelizerManager->createInstance($this->entityTypeId, []);
      }
    }

    return $this->panelizerPlugin;
  }

  /**
   * Check if Panelizer should be used for building this display.
   *
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display we're building.
   *
   * @return bool
   */
  protected function isPanelizerEnabled(EntityViewDisplayInterface $display) {
    return $display->getThirdPartySetting('panelizer', 'enable', FALSE);
  }

  /**
   * Gets the original view builder for this entity.
   *
   * @return \Drupal\Core\Entity\EntityViewBuilderInterface
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getFallbackViewBuilder() {
    return $this->entityManager->getHandler($this->entityTypeId, 'fallback_view_builder');
  }

  /**
   * Get the Panels display out of an the entity view display
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display.
   * @param $view_mode
   *   The view mode.
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   *   The Panels display.
   */
  protected function getPanelsDisplay(EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    // @todo: first check if the $entity has the Panelizer field and use that.

    // Get the correct display off the 3rd party settings.
    $displays = $display->getThirdPartySetting('panelizer', 'displays', []);
    if (!empty($displays['default'])) {
      $displays['default'] = $this->panelsManager->importDisplay($displays['default']);
    }
    else {
      $displays['default'] = $this->getPanelizerPlugin()->getDefaultDisplay($display, $entity->bundle(), $view_mode);
    }

    return $displays['default'];
  }

  /*
   * Methods from EntityViewBuilderInterface.
   */

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    $fallback_view_builder = $this->getFallbackViewBuilder();

    $panelized_entities = [];
    $fallback_entities = [];
    foreach ($entities as $id => $entity) {
      $display = $displays[$entity->bundle()];
      if ($this->isPanelizerEnabled($display)) {
        $panelized_entities[$id] = $entity;
      }
      else {
        $fallback_entities[$id] = $entity;
      }
    }

    // Handle all the fallback entities first!
    if (!empty($fallback_entities)) {
      $fallback_view_builder->buildComponents($build, $entities, $displays, $view_mode);
    }

    // Handle the panelized entities.
    if (!empty($panelized_entities)) {
      $this->moduleHandler()
        ->invokeAll('entity_prepare_view', array(
          $this->entityTypeId,
          $panelized_entities,
          $displays,
          $view_mode
        ));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $displays = EntityViewDisplay::collectRenderDisplays([$entity], $view_mode);
    $display = $displays[$entity->bundle()];

    if (!$this->isPanelizerEnabled($display)) {
      return $this->getFallbackViewBuilder()->view($entity, $view_mode, $langcode);
    }

    return parent::view($entity, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    $displays = EntityViewDisplay::collectRenderDisplays($entities, $view_mode);

    $panelized_entities = [];
    $fallback_entities = [];
    foreach ($entities as $id => $entity) {
      $display = $displays[$entity->bundle()];
      if ($this->isPanelizerEnabled($display)) {
        $panelized_entities[$id] = $entity;
      }
      else {
        $fallback_entities[$id] = $entity;
      }
    }

    $result = [];
    if (!empty($fallback_entities)) {
      $result += $this->getFallbackViewBuilder()->viewMultiple($fallback_entities, $view_mode, $langcode);
    }
    if (!empty($panelized_entities)) {
      $result += parent::viewMultiple($panelized_entities, $view_mode, $langcode);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $entities = NULL) {
    $this->getFallbackViewBuilder()->resetCache($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function viewField(FieldItemListInterface $items, $display_options = array()) {
    return $this->getFallbackViewBuilder()->viewfield($items, $display_options);
  }

  /**
   * {@inheritdoc}
   */
  public function viewFieldItem(FieldItemInterface $item, $display = array()) {
    return $this->getFallbackViewBuilder()->viewFieldItem($item, $display);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getFallbackViewBuilder()->getCacheTags();
  }

  /*
   * Methods from EntityViewBuilder which we use to render the Panelized entity.
   */

  /**
   * {@inheritdoc}
   */
  public function build(array $build) {
    // @todo: render an individual panelized entity.
    // @todo: We probably want to do this in buildMultiple() and not override this.
    $build['message'] = ['#markup' => 'this entity is panelized, yo!'];
    return parent::build($build);
  }

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $build_list) {
    // @todo: render multiple panelized entities.
    // @todo: Get all the PanelsDisplays for bundle/view mode at once!
    $build['message'] = ['#markup' => 'these entities are panelized, yo!'];
    return parent::buildMultiple($build_list);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    $panels_display = $this->getPanelsDisplay($entity, $display, $view_mode);
    $this->getPanelizerPlugin()->alterBuild($build, $entity, $panels_display, $view_mode);
  }

}