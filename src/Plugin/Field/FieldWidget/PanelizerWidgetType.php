<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\Field\FieldWidget\PanelizerWidgetType.
 */

namespace Drupal\panelizer\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'panelizer' widget.
 *
 * @FieldWidget(
 *   id = "panelizer",
 *   label = @Translation("Panelizer"),
 *   field_types = {
 *     "panelizer"
 *   }
 * )
 */
class PanelizerWidgetType extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'allow_panel_choice' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    /*
    $elements['allow_panel_choice'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow panel choice'),
      '#default_value' => $this->getSetting('allow_panel_choice'),
    );
    */

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if (!empty($this->getSetting('allow_panel_choice'))) {
      $summary[] = t('Allow panel choice');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    if (!empty($this->getSetting('allow_panel_choice'))) {
      $element['default'] = $element + [
        '#type' => 'select',
        // @todo: get the list of defaults
        '#options' => [],
        '#default_value' => isset($items[$delta]->default) ? $items[$delta]->default : NULL,
      ];
    }
    else {
      $element['default'] = $element + [
        '#type' => 'value',
        '#value' => isset($items[$delta]->default) ? $items[$delta]->default : NULL,
      ];
    }

    $element['panels_display'] = [
      '#type' => 'value',
      '#value' => isset($items[$delta]->panels_display) ? $items[$delta]->panels_display : [],
    ];

    return $element;
  }

}
