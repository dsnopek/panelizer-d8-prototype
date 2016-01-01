<?php
/**
 * @file
 * Contains \Drupal\panelizer\Panelizer
 */

namespace Drupal\panelizer;


use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\panelizer\Plugin\PanelizerEntityManager;
use Drupal\panels\PanelsDisplayManagerInterface;

/**
 * The Panelizer service.
 */
class Panelizer implements PanelizerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Panelizer entity manager.
   *
   * @var \Drupal\panelizer\Plugin\PanelizerEntityManager
   */
  protected $panelizerEntityManager;

  /**
   * The Panels display manager.
   *
   * @var \Drupal\Panels\PanelsDisplayManagerInterface
   */
  protected $panelsManager;

  /**
   * Constructs a Panelizer.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\panelizer\Plugin\PanelizerEntityManager $panelizer_entity_manager
   *   The Panelizer entity manager.
   * @param \Drupal\panels\PanelsDisplayManagerInterface $panels_manager
   *   The Panels display manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PanelizerEntityManager $panelizer_entity_manager, PanelsDisplayManagerInterface $panels_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->panelizerEntityManager = $panelizer_entity_manager;
    $this->panelsManager = $panels_manager;
  }

  /**
   * Gets the Panelizer entity plugin.
   *
   * @param $entity_type_id
   *   The entity type id.
   *
   * @return \Drupal\panelizer\Plugin\PanelizerEntityInterface
   */
  protected function getEntityPlugin($entity_type_id) {
    return $this->panelizerEntityManager->createInstance($entity_type_id, []);
  }

  /**
   * Gets the entity view display for the entity type, bundle and view mode.
   *
   * @param $entity_type_id
   *   The entity type id.
   * @param $bundle
   *   The bundle.
   * @param $view_mode
   *   The view mode.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface|NULL
   *   The entity view display if one exists; NULL otherwise.
   */
  protected function getEntityViewDisplay($entity_type_id, $bundle, $view_mode) {
    // Check the existence and status of:
    // - the display for the view mode,
    // - the 'default' display.
    $candidate_ids = array();
    if ($view_mode != 'default') {
      $candidate_ids[] = $entity_type_id . '.' . $bundle . '.' . $view_mode;
    }
    $candidate_ids[] = $entity_type_id . '.' . $bundle . '.default';
    $results = \Drupal::entityQuery('entity_view_display')
      ->condition('id', $candidate_ids)
      ->condition('status', TRUE)
      ->execute();

    // Select the first valid candidate display, if any.
    $load_id = FALSE;
    foreach ($candidate_ids as $candidate_id) {
      if (isset($results[$candidate_id])) {
        $load_id = $candidate_id;
        break;
      }
    }

    // Use the selected display if any, or create a fresh runtime object.
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    if ($load_id) {
      $display = $storage->load($load_id);
    }
    else {
      $display = $storage->create(array(
        'targetEntityType' => $entity_type_id,
        'bundle' => $bundle,
        'mode' => $view_mode,
        'status' => TRUE,
      ));
    }

    // Let the display know which view mode was originally requested.
    $display->originalMode = $view_mode;

    // Let modules alter the display.
    $display_context = array(
      'entity_type' => $entity_type_id,
      'bundle' => $bundle,
      'view_mode' => $view_mode,
    );
    // @todo: inject this!
    \Drupal::moduleHandler()->alter('entity_view_display', $display, $display_context);

    return $display;
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelsDisplay(FieldableEntityInterface $entity, $view_mode, EntityViewDisplayInterface $display = NULL) {
    // First, check if the entity has the panelizer field.
    if (isset($entity->field_panelizer)) {
      $values = [];
      foreach ($entity->field_panelizer as $item) {
        $values[$item->view_mode] = $item->panels_display;
      }
      if (isset($values[$view_mode])) {
        $panels_display = $this->panelsManager->importDisplay($values[$view_mode]);

        // @todo: Should be set when written, not here!
        $storage_id_parts = [$entity->getEntityTypeId(), $entity->id(), $view_mode];
        if ($entity instanceof RevisionableInterface) {
          $storage_id_parts[] = $entity->getRevisionId();
        }
        $panels_display->setStorage('panelizer_field.panels_storage', implode(':', $storage_id_parts));

        return $panels_display;
      }
    }

    return $this->getDefaultPanelsDisplay('default', $entity->getEntityTypeId(), $entity->bundle(), $view_mode, $display);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultPanelsDisplays($entity_type_id, $bundle, $view_mode, EntityViewDisplayInterface $display = NULL) {
    if (!$display) {
      $display = $this->getEntityViewDisplay($entity_type_id, $bundle, $view_mode);
    }

    // Get a list of all the defaults.
    $display_config = $display->getThirdPartySetting('panelizer', 'displays', []);
    $display_names = array_keys($display_config);
    if (empty($display_names)) {
      $display_names = ['default'];
    }

    // Get each one individually.
    $panels_displays = [];
    foreach ($display_names as $name) {
      $panels_displays[$name] = $this->getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode, $display);
    }

    return $panels_displays;
  }

  /**
   * @inheritDoc
   */
  public function getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode, EntityViewDisplayInterface $display = NULL) {
    if (!$display) {
      $display = $this->getEntityViewDisplay($entity_type_id, $bundle, $view_mode);
    }

    $config = $display->getThirdPartySetting('panelizer', 'displays', []);
    if (!empty($config[$name])) {
      $panels_display = $this->panelsManager->importDisplay($config[$name]);
    }
    else {
      $panels_display = $this->getEntityPlugin($entity_type_id)->getDefaultDisplay($display, $bundle, $view_mode);
      // @todo: This is actually an appropriate place to set the storage info.
    }

    // @todo: Should be set when written, not here!
    $storage_id_parts = [
      $entity_type_id,
      $bundle,
      $view_mode,
      $name,
    ];
    $panels_display->setStorage('panelizer.panels_storage', implode(':', $storage_id_parts));

    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function isPanelized($entity_type_id, $bundle, $view_mode, EntityViewDisplayInterface $display = NULL) {
    if (!$display) {
      $display = $this->getEntityViewDisplay($entity_type_id, $bundle, $view_mode);
    }

    return $this->getEntityPlugin($entity_type_id) && $display->getThirdPartySetting('panelizer', 'enable', FALSE);
  }


}