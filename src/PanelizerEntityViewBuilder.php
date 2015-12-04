<?php
/**
 * Contains \Drupal\panelizer\PanelizerEntityViewBuilder.
 */

namespace Drupal\panelizer;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\panelizer\Plugin\PanelizerEntityManager;
use Drupal\panels\PanelsDisplay;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PanelizerEntityViewBuilder extends EntityViewBuilder {

  /**
   * The Panelizer entity manager.
   *
   * @var \Drupal\panelizer\Plugin\PanelizerEntityManager
   */
  protected $panelizerManager;

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
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, PanelizerEntityManager $panelizer_manager) {
    parent::__construct($entity_type, $entity_manager, $language_manager);
    $this->panelizerManager = $panelizer_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('plugin.manager.panelizer_entity')
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
    $this->entityManager->getHandler($this->entityTypeId, 'fallback_view_builder');
  }

  /**
   * Get the Panels display out of an the entity view display
   *
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display.
   *
   * @return \Drupal\panels\PanelsDisplay
   *   The Panels display.
   */
  protected function getPanelsDisplay(EntityViewDisplayInterface $display) {
    // @todo: Do some magic to convert into a Panels display!
    return new PanelsDisplay();
  }

  /**
   * Build a Panelized display for the given entity, Panel and view mode.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\panels\PanelsDisplay $panels_display
   * @param $view_mode
   *
   * @return array
   *   Render array.
   */
  protected function buildPanelizedDisplay(EntityInterface $entity, PanelsDisplay $panels_display, $view_mode) {
    // @todo: Do the Panels magic!
    return ['temp' => ['#markup' => 'Panelized!']];
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    $fallback_view_builder = $this->getFallbackViewBuilder();

    // Divide the entities into those which are panelized and those not.
    $panelized_entities = [];
    $fallback_entities = [];
    foreach ($entities as $id => $entity) {
      if ($this->isPanelizerEnabled($displays[$entity->bundle()])) {
        $panelized_entities_by_bundle[$id] = $entity;
        $panelized_entities_by_bundle[$entity->bundle()][$id] = $entity;
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
    $this->moduleHandler()->invokeAll('entity_prepare_view', array($this->entityTypeId, $panelized_entities, $displays, $view_mode));
    /*
    foreach ($panelized_entities_by_bundle as $bundle => $panelized_entities) {
      $panels_display = $this->getPanelsDisplay($displays[$bundle]);
      foreach ($panelized_entities as $id => $entity) {
        $build[$id] += $this->buildPanelizedDisplay($entity, $panels_display, $view_mode);
      }
    }
    */
  }

  // @todo: override view()
  // @todo: override viewMultiple()
  // @todo: override resetCache()
  // @todo: override viewField()
  // @todo: override viewFieldItem()
  // @todo: override getCacheTags()

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    // We don't have to check if Panelizer is enabled here, because we'll never
    // make it to this point if it isn't.

    $panels_display = $this->getPanelsDisplay($display);
    $this->getPanelizerPlugin()->alterBuild($build, $entity, $panels_display, $view_mode);
  }

}