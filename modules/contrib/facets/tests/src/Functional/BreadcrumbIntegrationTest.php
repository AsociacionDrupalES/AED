<?php

namespace Drupal\Tests\facets\Functional;

use Drupal\Component\Utility\UrlHelper;

/**
 * Tests the overall functionality of the Facets admin UI.
 *
 * @group facets
 */
class BreadcrumbIntegrationTest extends FacetsTestBase {

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
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);

    $this->setUpExampleStructure();
    $this->insertExampleContent();
    self::assertEquals($this->indexItems($this->indexId), 5, '5 items were indexed.');

    $block = [
      'region' => 'footer',
      'label' => 'Breadcrumbs',
      'provider' => 'system',
    ];
    $this->drupalPlaceBlock('system_breadcrumb_block', $block);
    $this->resetAll();
  }

  /**
   * Tests Breadcrumb integration with grouping.
   */
  public function testGroupingIntegration() {
    $this->drupalGet('admin/config/search/facets');
    $this->clickLink('Configure', 1);
    $edit = [
      'filter_key' => 'f',
      'url_processor' => 'query_string',
      'breadcrumb[active]' => TRUE,
      'breadcrumb[group]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    $id = 'keywords';
    $name = '#Keywords';
    $this->createFacet($name, $id, 'keywords');
    $this->resetAll();
    $this->drupalGet('admin/config/search/facets/' . $id . '/edit');

    $id = 'type';
    $name = '#Type';
    $this->createFacet($name, $id);
    $this->resetAll();
    $this->drupalGet('admin/config/search/facets/' . $id . '/edit');
    $this->drupalPostForm(NULL, ['facet_settings[weight]' => '1'], 'Save');

    // Breadcrumb should show #Keywords: orange > #Type: article, item

    $initial_query = ['search_api_fulltext' => 'foo', 'test_param' => 1];
    $this->drupalGet('search-api-test-fulltext', ['query' => $initial_query]);

    $this->clickLink('item');
    $this->clickLink('article');
    $this->clickLink('orange');

    $this->assertSession()->linkExists('#Keywords: orange');
    $this->assertSession()->linkExists('#Type: article, item');

    $this->clickLink('#Type: article, item');

    $this->assertSession()->linkExists('#Keywords: orange');
    $this->assertSession()->linkExists('#Type: article, item');
    $this->checkFacetIsActive('orange');
    $this->checkFacetIsActive('item');
    $this->checkFacetIsActive('article');

    $this->clickLink('#Keywords: orange');
    $this->assertSession()->linkExists('#Keywords: orange');
    $this->assertSession()->linkNotExists('#Type: article, item');
    $this->checkFacetIsActive('orange');
    $this->checkFacetIsNotActive('item');
    $this->checkFacetIsNotActive('article');

    // Check that the current url still has the initial parameters.
    $curr_url = UrlHelper::parse($this->getUrl());
    $this->assertArraySubset($initial_query, $curr_url['query']);
  }

  /**
   * Tests Breadcrumb integration without grouping.
   */
//  public function testNonGroupingIntegration() {
    // TODO test it after we implement non grouping functionality.
//  }
}
