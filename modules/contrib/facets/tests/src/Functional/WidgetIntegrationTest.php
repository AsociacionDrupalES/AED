<?php

namespace Drupal\Tests\facets\Functional;

/**
 * Tests the overall functionality of the Facets admin UI.
 *
 * @group facets
 */
class WidgetIntegrationTest extends FacetsTestBase {

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
   * Tests checkbox widget.
   */
  public function testCheckboxWidget() {
    $id = 't';
    $name = 'Facet & checkbox~';
    $this->createFacet($name, $id);
    $this->drupalGet('admin/config/search/facets/' . $id . '/edit');
    $this->drupalPostForm(NULL, ['widget' => 'checkbox'], 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');
  }

  /**
   * Tests links widget's basic functionality.
   */
  public function testLinksWidget() {
    $id = 'links_widget';
    $name = '>.Facet &* Links';
    $this->createFacet($name, $id);
    $this->drupalGet('admin/config/search/facets/' . $id . '/edit');
    $this->drupalPostForm(NULL, ['widget' => 'links'], 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->clickLink('item');
    $this->checkFacetIsActive('item');
  }

  /**
   * Tests dropdown widget's basic functionality.
   */
  public function testDropdownWidget() {
    $id = 'select_widget';
    $name = 'Select';
    $this->createFacet($name, $id);
    $this->drupalGet('admin/config/search/facets/' . $id . '/edit');
    $this->drupalPostForm(NULL, ['widget' => 'dropdown'], 'Configure widget');
    $this->drupalPostForm(NULL, ['widget' => 'dropdown', 'facet_settings[show_only_one_result]' => TRUE], 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');
  }

  /**
   * Tests the functionality of a widget to hide/show the item-count.
   */
  public function testLinksShowHideCount() {
    $id = 'links_widget';
    $name = '>.Facet &* Links';
    $facet_edit_page = 'admin/config/search/facets/' . $id . '/edit';

    $this->createFacet($name, $id);

    // Go to the view and check that the facet links are shown with their
    // default settings.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['widget' => 'links', 'widget_config[show_numbers]' => TRUE], 'Save');

    // Go back to the same view and check that links now display the count.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item (3)');
    $this->assertFacetLabel('article (2)');

    $edit = [
      'widget' => 'links',
      'widget_config[show_numbers]' => TRUE,
      'facet_settings[query_operator]' => 'or',
    ];
    $this->drupalPostForm($facet_edit_page, $edit, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item (3)');
    $this->assertFacetLabel('article (2)');
    $this->clickPartialLink('item');
    $this->assertFacetLabel('item (3)');
    $this->assertFacetLabel('article (2)');

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['widget' => 'links', 'widget_config[show_numbers]' => FALSE], 'Save');

    // The count should be hidden again.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');
  }

}
