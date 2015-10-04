<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Derivative\SelectionBase.
 */

namespace Drupal\Core\Entity\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides derivative plugins for Entity Reference Selection plugins.
 *
 * @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\SelectionBase
 * @see \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManager
 * @see \Drupal\Core\Entity\Annotation\EntityReferenceSelection
 * @see \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface
 * @see plugin_api
 */
class SelectionBase extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity manager
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Creates an SelectionBase object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      $this->derivatives[$entity_type_id] = $base_plugin_definition;
      $this->derivatives[$entity_type_id]['entity_types'] = array($entity_type_id);
      $this->derivatives[$entity_type_id]['label'] = t('@entity_type selection', array('@entity_type' => $entity_type->getLabel()));
      $this->derivatives[$entity_type_id]['base_plugin_label'] = (string) $base_plugin_definition['label'];

      // If the entity type doesn't provide a 'label' key in its plugin
      // definition, we have to use the alternate PhpSelection class as default
      // plugin, which allows filtering the target entities by their label()
      // method. The major downside of PhpSelection is that it is more expensive
      // performance-wise than SelectionBase because it has to load all the
      // target entities in order to perform the filtering process, regardless
      // of whether a limit has been passed.
      // @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\PhpSelection
      if (!$entity_type->hasKey('label')) {
        $this->derivatives[$entity_type_id]['class'] = 'Drupal\Core\Entity\Plugin\EntityReferenceSelection\PhpSelection';
      }
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
