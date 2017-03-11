<?php

namespace Drupal\Tests\search_api\Kernel\Processor;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\node\Entity\NodeType;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Query\Query;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\Tests\search_api\Kernel\ResultsTrait;

/**
 * Tests the "Hierarchy" processor.
 *
 * @see \Drupal\search_api\Plugin\search_api\processor\AddHierarchy
 *
 * @group search_api
 *
 * @coversDefaultClass \Drupal\search_api\Plugin\search_api\processor\AddHierarchy
 */
class AddHierarchyTest extends ProcessorTestBase {

  use NodeCreationTrait;
  use EntityReferenceTestTrait;
  use ResultsTrait;
  use TaxonomyTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'filter',
    'taxonomy',
  );

  /**
   * A hierarchy to test.
   */
  protected static $hierarchy = array(
    'fruit' => array(
      'apple',
      'pear'
    ),
    'vegetable' => array(
      'radish',
      'turnip',
    ),
  );

  /**
   * The nodes created for testing.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes = array();

  /**
   * Hierarchical taxonomy terms.
   *
   * This is keyed by "type.item", for example: "fruit.pear".
   *
   * @var \Drupal\taxonomy\TermInterface[]
   */
  protected $terms = array();

  /**
   * Vocabulary to test with when using taxonomy for the hierarchy.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * {@inheritdoc}
   */
  public function setUp($processor = NULL) {
    parent::setUp('hierarchy');

    $this->installConfig(array('filter'));
    $this->installEntitySchema('taxonomy_term');
    $this->createTaxonomyHierarchy();

    // Create a node type for testing.
    $type = NodeType::create(array(
      'type' => 'page',
      'name' => 'page',
    ));
    $type->save();

    // Add the taxonomy field to page type.
    $this->createEntityReferenceField(
      'node',
      'page',
      'term_field',
      NULL,
      'taxonomy_term',
      'default',
      array(),
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );

    // Add a generic entity reference field.
    $this->createEntityReferenceField(
      'node',
      'page',
      'parent_reference',
      NULL,
      'node',
      'default',
      array()
    );

    // Index the taxonomy field.
    $term_field = new Field($this->index, 'term_field');
    $term_field->setType('integer');
    $term_field->setPropertyPath('term_field');
    $term_field->setDatasourceId('entity:node');
    $term_field->setLabel('Terms');
    $this->index->addField($term_field);
    $this->index->save();

    // Index the entity reference field.
    $reference_field = new Field($this->index, 'parent_reference');
    $reference_field->setType('integer');
    $reference_field->setPropertyPath('parent_reference');
    $reference_field->setDatasourceId('entity:node');
    $reference_field->setLabel('Parent page');
    $this->index->addField($reference_field);
    $this->index->save();

    // Setup a node index.
    $this->index->setDatasources(array(
      'entity:node' => $this->index->createPlugin('datasource', 'entity:node'),
    ));
    $this->index->save();
    $this->container
      ->get('search_api.index_task_manager')
      ->addItemsAll($this->index);
    $index_storage = $this->container
      ->get('entity_type.manager')
      ->getStorage('search_api_index');
    $index_storage->resetCache(array($this->index->id()));
    $this->index = $index_storage->load($this->index->id());
  }

  /**
   * Helper function to create the hierarchy with taxonomy terms.
   */
  protected function createTaxonomyHierarchy() {
    $this->vocabulary = $this->createVocabulary();

    foreach (static::$hierarchy as $type => $items) {
      // Add the 'type' item, and nest items underneath.
      $this->terms[$type] = $type_term = $this->createTerm($this->vocabulary, array(
        'name' => $type,
      ));
      foreach ($items as $item) {
        $this->terms["$type.$item"] = $this->createTerm($this->vocabulary, array(
          'name' => $item,
          'parent' => $type_term,
        ));
      }
    }
  }

  /**
   * Tests taxonomy-based hierarchy indexing.
   *
   * @covers ::preprocessIndexItems
   */
  public function testPreprocessIndexItemsTaxonomy() {
    // Add hierarchical terms to 3 nodes.
    foreach (array('vegetable.turnip', 'vegetable', 'fruit.pear') as $i => $term) {
      $this->nodes[$i] = $this->createNode(array(
        'type' => 'page',
        'term_field' => array(
          'target_id' => $this->terms[$term]->id(),
        ),
      ));
    }
    $this->index->reindex();
    $this->indexItems();

    // By default, hierarchy is not indexed, so a search for 'vegetable' should
    // only return node 2.
    $query = new Query($this->index);
    $query->addCondition('term_field', $this->terms['vegetable']->id());
    $result = $query->execute();
    $expected = array('node' => array(1));
    $this->assertResults($result, $expected);

    // Enable hierarchical indexing.
    $processor = $this->index->getProcessor('hierarchy');
    $processor->setConfiguration(array(
      'fields' => array(
        'term_field' => 'taxonomy_term-parent',
      ),
    ));
    $this->index->save();
    $this->indexItems();

    // Query for "vegetable" should return 2 items:
    // Node 1 is "vegetable.turnip" and node 2 is just "vegetable".
    $query = new Query($this->index);
    $query->addCondition('term_field', $this->terms['vegetable']->id());
    $result = $query->execute();
    $expected = array('node' => array(0, 1));
    $this->assertResults($result, $expected);

    // A search for just turnips should return node 1 only.
    $query = new Query($this->index);
    $query->addCondition('term_field', $this->terms['vegetable.turnip']->id());
    $result = $query->execute();
    $expected = array('node' => array(0));
    $this->assertResults($result, $expected);

    // Also add a term with multiple parents.
    $this->terms['avocado'] = $this->createTerm($this->vocabulary, array(
      'name' => 'Avocado',
      'parent' => array($this->terms['fruit']->id(), $this->terms['vegetable']->id()),
    ));
    $this->nodes[3] = $this->createNode(array(
      'type' => 'page',
      'term_field' => array(
        'target_id' => $this->terms['avocado']->id(),
      ),
    ));
    $this->index->reindex();
    $this->indexItems();

    // Searching for 'fruit' or 'vegetable' should return this new node.
    $query = new Query($this->index);
    $query->addCondition('term_field', $this->terms['fruit']->id());
    $result = $query->execute();
    $expected = array('node' => array(2, 3));
    $this->assertResults($result, $expected);

    $query = new Query($this->index);
    $query->addCondition('term_field', $this->terms['vegetable']->id());
    $result = $query->execute();
    $expected = array('node' => array(0, 1, 3));
    $this->assertResults($result, $expected);
  }

  /**
   * Tests non-taxonomy-based hierarchy.
   *
   * @covers ::preprocessIndexItems
   * @covers ::addHierarchyValues
   */
  public function testPreprocessIndexItems() {
    // Setup the nodes to follow the hierarchy.
    foreach (static::$hierarchy as $type => $items) {
      $this->nodes[] = $type_node = $this->createNode(array(
        'title' => $type,
      ));
      foreach ($items as $item) {
        $this->nodes[] = $this->createNode(array(
          'title' => $item,
          'parent_reference' => array('target_id' => $type_node->id()),
        ));
      }
    }
    // Add a third tier of hierarchy for specific types of radishes.
    foreach (array('Cherry Belle', 'Snow Belle', 'Daikon') as $item) {
      $this->nodes[] = $this->createNode(array(
        'title' => $item,
        'parent_reference' => array('target_id' => $this->nodes[5]->id()),
      ));
    }
    $this->index->reindex();
    $this->indexItems();

    // Initially hierarchy is excluded, so "vegetable" should only return nodes
    // 5 and 6.
    $query = new Query($this->index);
    $query->addCondition('parent_reference', $this->nodes[3]->id());
    $result = $query->execute();
    $expected = array('node' => array(4, 5));
    $this->assertResults($result, $expected);

    // Enable hierarchical indexing.
    $processor = $this->index->getProcessor('hierarchy');
    $processor->setConfiguration(array(
      'fields' => array(
        'parent_reference' => 'node-parent_reference',
      ),
    ));
    $this->index->save();
    $this->indexItems();

    // A search for "vegetable" should now include the hierarchy.
    $query = new Query($this->index);
    $query->addCondition('parent_reference', $this->nodes[3]->id());
    $result = $query->execute();
    $expected = array('node' => array(4, 5, 6, 7, 8));
    $this->assertResults($result, $expected);
  }

}
