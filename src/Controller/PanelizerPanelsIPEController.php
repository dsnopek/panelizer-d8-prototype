<?php

/**
 * @file
 * Contains \Drupal\panelizer\Controller\PanelizerPanelsIPEController.
 */

namespace Drupal\panelizer\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\panelizer\PanelizerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controller for Panelizer's Panels IPE routes.
 */
class PanelizerPanelsIPEController extends ControllerBase {

  /**
   * The Panelizer service.
   *
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

  /**
   * Constructs a PanelizerPanelsIPEController.
   *
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   *   The Panelizer service.
   */
  public function __construct(PanelizerInterface $panelizer) {
    $this->panelizer = $panelizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('panelizer')
    );
  }

  /**
   * Reverts an entity view mode to a particular named default.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function revertToDefault(FieldableEntityInterface $entity, $view_mode, Request $request) {
    $data = Json::decode($request->getContent());
    if (empty($data['default'])) {
      throw new BadRequestHttpException("Default name to revert to must be passed!");
    }
    $this->panelizer->setPanelsDisplay($entity, $view_mode, $data['default']);
  }

  /**
   * Custom access checker for reverting an entity view mode to a named default.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return bool
   *   TRUE if access is allowed; FALSE otherwise.
   */
  public function accessRevertToDefault(FieldableEntityInterface $entity, $view_mode, AccountInterface $account) {
    return $this->panelizer->hasEntityPermission('revert to default', $entity, $view_mode, $account);
  }

}