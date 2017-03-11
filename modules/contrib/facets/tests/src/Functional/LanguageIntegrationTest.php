<?php

namespace Drupal\Tests\facets\Functional;

use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the integration with the language module.
 *
 * @group facets
 */
class LanguageIntegrationTest extends FacetsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views',
    'search_api',
    'facets',
    'block',
    'facets_search_api_dependency',
    'language',
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

    // Make absolutely sure the ::$blocks variable doesn't pass information
    // along between tests.
    $this->blocks = NULL;
  }

  /**
   * Tests that a facet works on a page with language prefix.
   *
   * @see https://www.drupal.org/node/2712557
   */
  public function testLanguageIntegration() {
    $facet_id = 'owl';
    $facet_name = 'Owl';
    $this->createFacet($facet_name, $facet_id);

    ConfigurableLanguage::create(['id' => 'xx-lolspeak'])->save();

    // Go to the search view with a language prefix and click on one of the
    // facets.
    $this->drupalGet('xx-lolspeak/search-api-test-fulltext');
    $this->assertText('item');
    $this->assertText('article');
    $this->clickLink('item');

    // Check that the language code is still in the url.
    $this->assertTrue(strpos($this->getUrl(), 'xx-lolspeak/'), 'Found the language code in the url');
    $this->assertResponse(200);
    $this->assertText('item');
    $this->assertText('article');
  }

}
