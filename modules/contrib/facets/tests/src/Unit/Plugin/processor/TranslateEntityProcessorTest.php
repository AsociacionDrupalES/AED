<?php

namespace Drupal\Tests\facets\Unit\Plugin\processor;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\facets\Entity\Facet;
use Drupal\facets\FacetSource\SearchApiFacetSourceInterface;
use Drupal\facets\Plugin\facets\processor\TranslateEntityProcessor;
use Drupal\facets\Result\Result;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\node\Entity\Node;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class TranslateEntityProcessorTest extends UnitTestCase {

  /**
   * The mocked facet.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $facet;

  /**
   * The mocked language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Mock facet.
    $datasource = $this->getMock(DatasourceInterface::class);
    $datasource->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $field = $this->getMock(FieldInterface::class);
    $field->expects($this->any())
      ->method('getDatasource')
      ->willReturn($datasource);
    $index = $this->getMock(IndexInterface::class);
    $index->expects($this->any())
      ->method('getField')
      ->willReturn($field);
    $facet_source = $this->getMock(SearchApiFacetSourceInterface::class);
    $facet_source->expects($this->any())
      ->method('getIndex')
      ->willReturn($index);
    $this->facet = $this->getMockBuilder(Facet::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->facet->expects($this->any())
      ->method('getFacetSource')
      ->willReturn($facet_source);
    $this->facet->expects($this->any())
      ->method('getFieldIdentifier')
      ->willReturn('testfield');

    // Mock language manager.
    $this->languageManager = $this->getMockBuilder(LanguageManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $language = new Language(['langcode' => 'en']);
    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->will($this->returnValue($language));

    // Mock entity type manager.
    $this->entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Create container.
    $container = new ContainerBuilder();
    $container->set('language_manager', $this->languageManager);
    $container->set('entity_type.manager', $this->entityTypeManager);
    \Drupal::setContainer($container);
  }

  /**
   * Tests that node results were correctly changed.
   */
  public function testNodeResultsChanged() {
    // Set original results.
    /** @var \Drupal\facets\Result\ResultInterface[] $original_results */
    $original_results = [
      new Result(2, 2, 6),
    ];
    $this->facet->setResults($original_results);

    // Mock entity type.
    $field_config = $this->getMock(FieldStorageConfigInterface::class);
    $field_config->expects($this->any())
      ->method('getSetting')
      ->willReturn('node');
    $field_storage = $this->getMock(EntityStorageInterface::class);
    $field_storage->expects($this->any())
      ->method('load')
      ->willReturn($field_config);
    $this->entityTypeManager->expects($this->at(0))
      ->method('getStorage')
      ->with('field_storage_config')
      ->willReturn($field_storage);

    // Mock node.
    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node->expects($this->any())
      ->method('label')
      ->willReturn('shaken not stirred');
    $nodes = [
      2 => $node,
    ];
    $node_storage = $this->getMock(EntityStorageInterface::class);
    $node_storage->expects($this->any())
      ->method('loadMultiple')
      ->willReturn($nodes);
    $this->entityTypeManager->expects($this->at(1))
      ->method('getStorage')
      ->willReturn($node_storage);

    // Set expected results.
    $expected_results = [
      ['nid' => 2, 'title' => 'shaken not stirred'],
    ];

    // Without the processor we expect the id to display.
    foreach ($expected_results as $key => $expected) {
      $this->assertEquals($expected['nid'], $original_results[$key]->getRawValue());
      $this->assertEquals($expected['nid'], $original_results[$key]->getDisplayValue());
    }

    // With the processor we expect the title to display.
    /** @var \Drupal\facets\Result\ResultInterface[] $filtered_results */
    $processor = new TranslateEntityProcessor([], 'translate_entity', [], $this->languageManager, $this->entityTypeManager);
    $filtered_results = $processor->build($this->facet, $original_results);
    foreach ($expected_results as $key => $expected) {
      $this->assertEquals($expected['nid'], $filtered_results[$key]->getRawValue());
      $this->assertEquals($expected['title'], $filtered_results[$key]->getDisplayValue());
    }
  }

  /**
   * Tests that term results were correctly changed.
   */
  public function testTermResultsChanged() {
    // Set original results.
    /** @var \Drupal\facets\Result\ResultInterface[] $original_results */
    $original_results = [
      new Result(1, 1, 5),
    ];
    $this->facet->setResults($original_results);

    // Mock entity type.
    $field_config = $this->getMock(FieldStorageConfigInterface::class);
    $field_config->expects($this->any())
      ->method('getSetting')
      ->willReturn('taxonomy_term');
    $field_storage = $this->getMock(EntityStorageInterface::class);
    $field_storage->expects($this->any())
      ->method('load')
      ->willReturn($field_config);
    $this->entityTypeManager->expects($this->at(0))
      ->method('getStorage')
      ->with('field_storage_config')
      ->willReturn($field_storage);

    // Mock term.
    $term = $this->getMockBuilder(Term::class)
      ->disableOriginalConstructor()
      ->getMock();
    $term->expects($this->once())
      ->method('label')
      ->willReturn('Burrowing owl');
    $terms = [
      1 => $term,
    ];
    $term_storage = $this->getMock(EntityStorageInterface::class);
    $term_storage->expects($this->any())
      ->method('loadMultiple')
      ->willReturn($terms);
    $this->entityTypeManager->expects($this->at(1))
      ->method('getStorage')
      ->willReturn($term_storage);

    // Set expected results.
    $expected_results = [
      ['tid' => 1, 'name' => 'Burrowing owl'],
    ];

    // Without the processor we expect the id to display.
    foreach ($expected_results as $key => $expected) {
      $this->assertEquals($expected['tid'], $original_results[$key]->getRawValue());
      $this->assertEquals($expected['tid'], $original_results[$key]->getDisplayValue());
    }

    // With the processor we expect the title to display.
    /** @var \Drupal\facets\Result\ResultInterface[] $filtered_results */
    $processor = new TranslateEntityProcessor([], 'translate_entity', [], $this->languageManager, $this->entityTypeManager);
    $filtered_results = $processor->build($this->facet, $original_results);
    foreach ($expected_results as $key => $expected) {
      $this->assertEquals($expected['tid'], $filtered_results[$key]->getRawValue());
      $this->assertEquals($expected['name'], $filtered_results[$key]->getDisplayValue());
    }
  }

  /**
   * Test that deleted entities still in index results doesn't display.
   */
  public function testDeletedEntityResults() {
    // Set original results.
    /** @var \Drupal\facets\Result\ResultInterface[] $original_results */
    $original_results = [
      new Result(1, 1, 5),
    ];
    $this->facet->setResults($original_results);

    // Mock entity type.
    $field_config = $this->getMock(FieldStorageConfigInterface::class);
    $field_config->expects($this->any())
      ->method('getSetting')
      ->willReturn('taxonomy_term');
    $field_storage = $this->getMock(EntityStorageInterface::class);
    $field_storage->expects($this->any())
      ->method('load')
      ->willReturn($field_config);
    $this->entityTypeManager->expects($this->at(0))
      ->method('getStorage')
      ->with('field_storage_config')
      ->willReturn($field_storage);

    $term_storage = $this->getMock(EntityStorageInterface::class);
    $term_storage->expects($this->any())
      ->method('loadMultiple')
      ->willReturn([]);
    $this->entityTypeManager->expects($this->at(1))
      ->method('getStorage')
      ->willReturn($term_storage);

    // Processor should return nothing (and not throw an exception).
    /** @var \Drupal\facets\Result\ResultInterface[] $filtered_results */
    $processor = new TranslateEntityProcessor([], 'translate_entity', [], $this->languageManager, $this->entityTypeManager);
    $filtered_results = $processor->build($this->facet, $original_results);
    $this->assertEmpty($filtered_results);
  }

}
