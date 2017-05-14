<?php

namespace Drupal\Tests\facets\Functional;

use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\Entity\Facet;
use Drupal\facets\FacetSourceInterface;

/**
 * Tests the overall functionality of the Facets admin UI.
 *
 * @group facets
 */
class UrlIntegrationTest extends FacetsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views',
    'node',
    'search_api',
    'facets',
    'block',
    'facets_search_api_dependency',
    'facets_query_processor',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);

    $this->setUpExampleStructure();
    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');
  }

  /**
   * Tests various url integration things.
   */
  public function testUrlIntegration() {
    $id = 'facet';
    $name = '&^Facet@#1';
    $this->createFacet($name, $id);

    $url = Url::fromUserInput('/search-api-test-fulltext', ['query' => ['f[0]' => 'facet:item']]);
    $this->checkClickedFacetUrl($url);

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = Facet::load($id);
    $this->assertTrue($facet instanceof FacetInterface);
    $config = $facet->getFacetSourceConfig();
    $this->assertTrue($config instanceof FacetSourceInterface);
    $this->assertEqual('f', $config->getFilterKey());

    $facet = NULL;
    $config = NULL;

    // Go to the only enabled facet source's config and change the filter key.
    $this->drupalGet('admin/config/search/facets');
    $this->clickLink('Configure', 1);

    $edit = [
      'filter_key' => 'y',
      'url_processor' => 'query_string',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = Facet::load($id);
    $config = $facet->getFacetSourceConfig();
    $this->assertTrue($config instanceof FacetSourceInterface);
    $this->assertEqual('y', $config->getFilterKey());

    $facet = NULL;
    $config = NULL;

    $url_2 = Url::fromUserInput('/search-api-test-fulltext', ['query' => ['y[0]' => 'facet:item']]);
    $this->checkClickedFacetUrl($url_2);

    // Go to the only enabled facet source's config and change the url
    // processor.
    $this->drupalGet('admin/config/search/facets');
    $this->clickLink('Configure', 1);

    $edit = [
      'filter_key' => 'y',
      'url_processor' => 'dummy_query',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = Facet::load($id);
    $config = $facet->getFacetSourceConfig();
    $this->assertTrue($config instanceof FacetSourceInterface);
    $this->assertEqual('y', $config->getFilterKey());

    $facet = NULL;
    $config = NULL;

    $url_3 = Url::fromUserInput('/search-api-test-fulltext', ['query' => ['y[0]' => 'facet||item']]);
    $this->checkClickedFacetUrl($url_3);
  }

  /**
   * Tests the url when a colon is used in the value.
   */
  public function testColonValue() {
    $id = 'water_bear';
    $name = 'Water bear';
    $this->createFacet($name, $id, 'keywords');

    // Add a new entity that has a colon in one of it's keywords.
    $entity_test_storage = \Drupal::entityTypeManager()
      ->getStorage('entity_test_mulrev_changed');
    $entity_test_storage->create([
      'name' => 'Entity with colon',
      'body' => 'test test',
      'type' => 'item',
      'keywords' => ['orange', 'test:colon'],
      'category' => 'item_category',
    ])->save();
    // Make sure the new item is indexed.
    $this->assertEqual(1, $this->indexItems($this->indexId));

    // Go to the overview and test that we have the expected links.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('test:colon');
    $this->assertFacetLabel('orange');
    $this->assertFacetLabel('banana');

    // Click the link with the colon.
    $this->clickLink('test:colon');
    $this->assertResponse(200);

    // Make sure 'test:colon' is active.
    $url = Url::fromUserInput('/search-api-test-fulltext', ['query' => ['f[0]' => 'water_bear:test:colon']]);
    $this->assertUrl($url);
    $this->checkFacetIsActive('test:colon');
    $this->assertFacetLabel('orange');
    $this->assertFacetLabel('banana');
  }

}
