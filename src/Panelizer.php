<?php
/**
 * @file
 * Contains \Drupal\panelizer\Panelizer
 */

namespace Drupal\panelizer;


use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\panelizer\Plugin\PanelizerEntityManager;
use Drupal\panels\PanelsDisplayManagerInterface;

/**
 * The Panelizer service.
 */
class Panelizer implements PanelizerInterface {

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
   * @param \Drupal\panelizer\Plugin\PanelizerEntityManager $panelizer_entity_manager
   *   The Panelizer entity manager.
   * @param \Drupal\panels\PanelsDisplayManagerInterface $panels_manager
   *   The Panels display manager.
   */
  public function __construct(PanelizerEntityManager $panelizer_entity_manager, PanelsDisplayManagerInterface $panels_manager) {
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
  public function getEntityPlugin($entity_type_id) {
    return $this->panelizerEntityManager->createInstance($entity_type_id, []);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelsDisplay(EntityInterface $entity, $view_mode, EntityViewDisplayInterface $display = NULL) {
    // First, check if the entity has the panelizer field.
    if (isset($entity->field_panelizer)) {
      $values = [];
      foreach ($entity->field_panelizer as $item) {
        $values[$item->view_mode] = $item->panels_display;
      }
      if (isset($values[$view_mode])) {
        $panels_display = $this->panelsManager->importDisplay($values[$view_mode]);

        // @todo: Should be set when written, not here!
        $storage_id_parts = [$entity->getEntityTypeId(), $entity->id()];
        if ($entity instanceof RevisionableInterface) {
          $storage_id_parts[] = $entity->getRevisionId();
        }
        $panels_display->setStorage('panelizer_field.panels_storage', implode(':', $storage_id_parts));

        return $panels_display;
      }
    }

    if (!$display) {
      $display = EntityViewDisplay::collectRenderDisplay($entity, $view_mode);
    }

    // Otherwise, get the correct default off the entity view display.
    $displays = $display->getThirdPartySetting('panelizer', 'displays', []);
    if (!empty($displays['default'])) {
      $displays['default'] = $this->panelsManager->importDisplay($displays['default']);
    }
    else {
      $displays['default'] = $this->getEntityPlugin($entity->getEntityTypeId())->getDefaultDisplay($display, $entity->bundle(), $view_mode);
      // @todo: This is actually an appropriate place to set the storage info.
    }

    // @todo: Should be set when written, not here!
    $storage_id_parts = [
      $entity->getEntityTypeId(),
      $entity->bundle(),
      $view_mode,
      'default',
    ];
    $displays['default']->setStorage('panelizer.panels_storage', implode(':', $storage_id_parts));

    return $displays['default'];
  }

}