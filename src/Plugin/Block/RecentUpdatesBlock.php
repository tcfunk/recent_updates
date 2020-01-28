<?php

namespace Drupal\recent_updates\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'RecentUpdatesBlock' block.
 *
 * @Block(
 *  id = "recent_updates_block",
 *  admin_label = @Translation("Recent updates block"),
 * )
 */
class RecentUpdatesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['#theme'] = 'recent_updates_block';

    // Invalidate cache on Node CRUD.
    $build['#cache'] = [
      'tags' => [
        'node_list',
      ],
      'max-age' => 86400, // 24 hrs.
    ];

    // Query for nids of recently updated content.
    $today = new DrupalDateTime('today');
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $conditions = $query->andConditionGroup()
      ->condition('status', 1)
      ->condition('changed', $today->getTimestamp(), '>=');
    $query_result = $query
      ->condition($conditions)
      ->execute();

    // Load and display the results.
    $build['#content']['items'] = [
      '#theme' => 'menu',
      '#items' => [],
    ];

    foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($query_result) as $node) {
      $build['#content']['items']['#items'][] = [
        'title' => $node->label(),
        'url' => $node->toUrl(),
      ];
    }

    return $build;
  }

}
