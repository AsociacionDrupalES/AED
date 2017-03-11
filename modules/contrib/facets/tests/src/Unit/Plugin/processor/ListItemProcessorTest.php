<?php

namespace Drupal\Tests\facets\Unit\Plugin\processor;

use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\processor\ListItemProcessor;
use Drupal\facets\Result\Result;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class ListItemProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\processor\BuildProcessorInterface
   */
  protected $processor;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facets\Result\Result[]
   */
  protected $results;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->results = [
      new Result(1, 1, 10),
      new Result(2, 2, 5),
      new Result(3, 3, 15),
    ];

    $config_manager = $this->getMockBuilder(ConfigManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity_field_manager = $this->getMockBuilder(EntityFieldManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity_type_bundle_info = $this->getMockBuilder(EntityTypeBundleInfo::class)
      ->disableOriginalConstructor()
      ->getMock();

    $processor_id = 'list_item';
    $this->processor = new ListItemProcessor([], $processor_id, [], $config_manager, $entity_field_manager, $entity_type_bundle_info);
  }

  /**
   * Tests facet build with field.module field.
   */
  public function testFacetFieldmoduleBuild() {
    $module_field = $this->getMockBuilder(FieldStorageConfig::class)
      ->disableOriginalConstructor()
      ->getMock();
    $module_field->expects($this->at(0))
      ->method('getSetting')
      ->with('allowed_values_function')
      ->willReturn('');
    $module_field->expects($this->at(1))
      ->method('getSetting')
      ->with('allowed_values')
      ->willReturn([
        1 => 'llama',
        2 => 'badger',
        3 => 'kitten',
      ]);

    $config_manager = $this->getMockBuilder(ConfigManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config_manager->expects($this->any())
      ->method('loadConfigEntityByName')
      ->willReturn($module_field);

    $entity_field_manager = $this->getMockBuilder(EntityFieldManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity_type_bundle_info = $this->getMockBuilder(EntityTypeBundleInfo::class)
      ->disableOriginalConstructor()
      ->getMock();

    $processor_id = 'list_item';
    $processor = new ListItemProcessor([], $processor_id, [], $config_manager, $entity_field_manager, $entity_type_bundle_info);

    // Config entity field facet.
    $module_field_facet = new Facet([], 'facets_facet');
    $module_field_facet->setFieldIdentifier('test_facet');
    $module_field_facet->setResults($this->results);
    $module_field_facet->addProcessor([
      'processor_id' => 'list_item',
      'weights' => [],
      'settings' => [],
    ]);
    /* @var \Drupal\facets\Result\Result[] $module_field_facet- */
    $module_field_results = $processor->build($module_field_facet, $this->results);

    $this->assertCount(3, $module_field_results);
    $this->assertEquals('llama', $module_field_results[0]->getDisplayValue());
    $this->assertEquals('badger', $module_field_results[1]->getDisplayValue());
    $this->assertEquals('kitten', $module_field_results[2]->getDisplayValue());
  }

  /**
   * Tests facet build with field.module field.
   */
  public function testBundle() {
    $module_field = $this->getMockBuilder(FieldStorageConfig::class)
      ->disableOriginalConstructor()
      ->getMock();
    $module_field->expects($this->at(0))
      ->method('getSetting')
      ->with('allowed_values_function')
      ->willReturn([]);
    $module_field->expects($this->at(1))
      ->method('getSetting')
      ->with('allowed_values')
      ->willReturn([]);

    $config_manager = $this->getMockBuilder(ConfigManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config_manager->expects($this->any())
      ->method('loadConfigEntityByName')
      ->willReturn($module_field);

    $entity_field_manager = $this->getMockBuilder(EntityFieldManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity_type_bundle_info = $this->getMockBuilder(EntityTypeBundleInfo::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_type_bundle_info->expects($this->exactly(1))
      ->method('getBundleInfo')
      ->willReturn([
        1 => ['label' => 'Monkey'],
        2 => ['label' => 'Donkey'],
        3 => ['label' => 'Kong'],
      ]);

    $processor_id = 'list_item';
    $processor = new ListItemProcessor([], $processor_id, [], $config_manager, $entity_field_manager, $entity_type_bundle_info);

    // Config entity field facet.
    $module_field_facet = new Facet([], 'facets_facet');
    $module_field_facet->setFieldIdentifier('test_facet');
    $module_field_facet->setResults($this->results);
    $module_field_facet->addProcessor([
      'processor_id' => 'list_item',
      'weights' => [],
      'settings' => [],
    ]);
    /* @var \Drupal\facets\Result\Result[] $module_field_facet- */
    $module_field_results = $processor->build($module_field_facet, $this->results);

    $this->assertCount(3, $module_field_results);
    $this->assertEquals('Monkey', $module_field_results[0]->getDisplayValue());
    $this->assertEquals('Donkey', $module_field_results[1]->getDisplayValue());
    $this->assertEquals('Kong', $module_field_results[2]->getDisplayValue());
  }

  /**
   * Tests facet build with base props.
   */
  public function testFacetBasepropBuild() {
    $config_manager = $this->getMockBuilder(ConfigManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $base_field = $this->getMockBuilder(BaseFieldDefinition::class)
      ->disableOriginalConstructor()
      ->getMock();
    $base_field->expects($this->any())
      ->method('getSetting')
      ->willReturnMap([
        ['allowed_values_function', ''],
        [
          'allowed_values',
          [
            1 => 'blue whale',
            2 => 'lynx',
            3 => 'dog-wolf-lion',
          ],
        ],
      ]);

    $entity_field_manager = $this->getMockBuilder(EntityFieldManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_field_manager->expects($this->any())
      ->method('getFieldDefinitions')
      ->with('node', '')
      ->willReturn([
        'test_facet_baseprop' => $base_field,
      ]);

    $entity_type_bundle_info = $this->getMockBuilder(EntityTypeBundleInfo::class)
      ->disableOriginalConstructor()
      ->getMock();

    $processor_id = 'list_item';
    $processor = new ListItemProcessor([], $processor_id, [], $config_manager, $entity_field_manager, $entity_type_bundle_info);

    // Base prop facet.
    $base_prop_facet = new Facet([], 'facets_facet');
    $base_prop_facet->setFieldIdentifier('test_facet_baseprop');
    $base_prop_facet->setResults($this->results);
    $base_prop_facet->addProcessor([
      'processor_id' => 'list_item',
      'weights' => [],
      'settings' => [],
    ]);

    /** @var \Drupal\facets\Result\Result[] $base_prop_results */
    $base_prop_results = $processor->build($base_prop_facet, $this->results);

    $this->assertCount(3, $base_prop_results);
    $this->assertEquals('blue whale', $base_prop_results[0]->getDisplayValue());
    $this->assertEquals('lynx', $base_prop_results[1]->getDisplayValue());
    $this->assertEquals('dog-wolf-lion', $base_prop_results[2]->getDisplayValue());
  }

  /**
   * Tests configuration.
   */
  public function testConfiguration() {
    $config = $this->processor->defaultConfiguration();
    $this->assertEquals([], $config);
  }

  /**
   * Tests testDescription().
   */
  public function testDescription() {
    $this->assertEquals('', $this->processor->getDescription());
  }

  /**
   * Tests isHidden().
   */
  public function testIsHidden() {
    $this->assertEquals(FALSE, $this->processor->isHidden());
  }

  /**
   * Tests isLocked().
   */
  public function testIsLocked() {
    $this->assertEquals(FALSE, $this->processor->isLocked());
  }

}
