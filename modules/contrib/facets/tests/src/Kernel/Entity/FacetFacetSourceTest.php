<?php

namespace Drupal\Tests\facets\Kernel\Entity;

use Drupal\facets\Entity\Facet;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class FacetFacetSourceTest.
 *
 * Tests facet source behavior for the facet entity.
 *
 * @group facets
 * @coversDefaultClass \Drupal\facets\Entity\Facet
 */
class FacetFacetSourceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'facets',
    'field',
    'search_api',
    'search_api_db',
    'search_api_test_db',
    'search_api_test_example_content',
    'search_api_test_views',
    'search_api_test',
    'user',
    'system',
    'entity_test',
    'text',
    'views',
    'rest',
    'serialization',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('facets_facet');
    $this->installEntitySchema('entity_test_mulrev_changed');
    $this->installEntitySchema('search_api_task');

    \Drupal::state()->set('search_api_use_tracking_batch', FALSE);

    // Set tracking page size so tracking will work properly.
    \Drupal::configFactory()
      ->getEditable('search_api.settings')
      ->set('tracking_page_size', 100)
      ->save();

    $this->installConfig(array(
      'search_api_test_example_content',
      'search_api_test_db',
    ));

    $this->installConfig('search_api_test_views');
  }

  /**
   * Tests facet source behavior for the facet entity.
   *
   * @covers ::getFacetSourceId
   * @covers ::setFacetSourceId
   * @covers ::getFacetSources
   * @covers ::getFacetSource
   * @covers ::getFacetSourceConfig
   */
  public function testFacetSource() {
    $entity = new Facet([], 'facets_facet');
    $this->assertNull($entity->getFacetSourceId());

    $display_name = 'views_page:search_api_test_view__page_1';
    $display_id = 'views_page__search_api_test_view__page_1';
    $displays = $this->container
      ->get('plugin.manager.search_api.display')
      ->getDefinitions();
    $this->assertArrayHasKey($display_name, $displays);

    $entity->setFacetSourceId($display_name);
    $this->assertEquals($display_name, $entity->getFacetSourceId());
    $this->assertInstanceOf('\Drupal\facets\FacetSource\SearchApiFacetSourceInterface', $entity->getFacetSources()[$display_name]);
    $this->assertInstanceOf('\Drupal\facets\FacetSource\SearchApiFacetSourceInterface', $entity->getFacetSource());
    $this->assertInstanceOf('\Drupal\facets\FacetSourceInterface', $entity->getFacetSourceConfig());
    $this->assertEquals($display_name, $entity->getFacetSourceConfig()->getName());
    $this->assertEquals($display_id, $entity->getFacetSourceConfig()->id());
    $this->assertEquals('f', $entity->getFacetSourceConfig()->getFilterKey());

  }

  /**
   * Tests invalid query type.
   *
   * @covers ::getQueryType
   */
  public function testInvalidQueryType() {
    $entity = new Facet([], 'facets_facet');
    $entity->setWidget('links');
    $entity->setFacetSourceId('views_page:search_api_test_view__page_1');

    $this->setExpectedException('Drupal\facets\Exception\InvalidQueryTypeException');
    $entity->getQueryType();
  }

}
