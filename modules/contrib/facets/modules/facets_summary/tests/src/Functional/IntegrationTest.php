<?php

namespace Drupal\Tests\facets_summary\Functional;

use Drupal\Tests\facets\Functional\FacetsTestBase;
use Drupal\facets_summary\Entity\FacetsSummary;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the overall functionality of the Facets summary admin UI.
 *
 * @group facets
 */
class IntegrationTest extends FacetsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'facets_summary',
  ];

  /**
   * No config checking.
   *
   * @var bool
   *
   * @todo Enable config checking again.
   */
  protected $strictConfigSchema = FALSE;

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
   * Tests the overall functionality of the Facets summary admin UI.
   */
  public function testFramework() {
    $this->drupalGet('admin/config/search/facets');
    $this->assertNoText('Facets Summary');

    $values = [
      'name' => 'Owl',
      'id' => 'owl',
      'facet_source_id' => 'search_api:views_page__search_api_test_view__page_1',
    ];
    $this->drupalPostForm('admin/config/search/facets/add-facet-summary', $values, 'Save');
    $this->drupalPostForm(NULL, [], 'Save');

    $this->drupalGet('admin/config/search/facets');
    $this->assertText('Facets Summary');
    $this->assertText('Owl');

    $this->drupalGet('admin/config/search/facets/facet-summary/owl/edit');
    $this->assertText('No facets found.');

    $this->createFacet('Llama', 'llama');
    $this->drupalGet('admin/config/search/facets');
    $this->assertText('Llama');

    // Go back to the facet summary and check that the facets are not checked by
    // default and that they show up in the list here.
    $this->drupalGet('admin/config/search/facets/facet-summary/owl/edit');
    $this->assertNoText('No facets found.');
    $this->assertText('Llama');
    $this->assertNoFieldChecked('edit-facets-llama-checked');

    // Post the form and check that no facets are checked after saving the form.
    $this->drupalPostForm(NULL, [], 'Save');
    $this->assertNoFieldChecked('edit-facets-llama-checked');

    // Enable a facet and check it's status after saving.
    $this->drupalPostForm(NULL, ['facets[llama][checked]' => TRUE], 'Save');
    $this->assertFieldChecked('edit-facets-llama-checked');

    $this->configureShowCountProcessor();
    $this->configureResetFacetsProcessor();
  }

  /**
   * Tests with multiple facets.
   *
   * Includes a regression test for #2841357
   */
  public function testMultipleFacets() {
    // Create facets.
    $this->createFacet('Giraffe', 'giraffe', 'keywords');
    // Clear all the caches between building the 2 facets - because things fail
    // otherwise.
    $this->resetAll();
    $this->createFacet('Llama', 'llama');

    // Add a summary.
    $values = [
      'name' => 'Owlß',
      'id' => 'owl',
      'facet_source_id' => 'search_api:views_page__search_api_test_view__page_1',
    ];
    $this->drupalPostForm('admin/config/search/facets/add-facet-summary', $values, 'Save');
    $this->drupalPostForm(NULL, [], 'Save');

    // Edit the summary and enable the giraffe's.
    $summaries = [
      'facets[giraffe][checked]' => TRUE,
      'facets[giraffe][label]' => 'Summary giraffe',
    ];
    $this->drupalPostForm('admin/config/search/facets/facet-summary/owl/edit', $summaries, 'Save');

    $block = [
      'region' => 'footer',
      'id' => str_replace('_', '-', 'owl'),
      'weight' => 50,
    ];
    $block = $this->drupalPlaceBlock('facets_summary_block:owl', $block);

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results');
    $this->assertText($block->label());
    $this->assertFacetBlocksAppear();

    $this->clickLink('apple');
    $list_items = $this->getSession()
      ->getPage()
      ->findById('block-' . $block->id())
      ->findAll('css', 'li');
    $this->assertCount(1, $list_items);

    $this->clickLink('item');
    $list_items = $this->getSession()
      ->getPage()
      ->findById('block-' . $block->id())
      ->findAll('css', 'li');
    $this->assertCount(1, $list_items);

    // Edit the summary and enable the giraffe's.
    $summaries = [
      'facets[giraffe][checked]' => TRUE,
      'facets[giraffe][label]' => 'Summary giraffe',
      'facets[llama][checked]' => TRUE,
      'facets[llama][label]' => 'Summary llama',
    ];
    $this->drupalPostForm('admin/config/search/facets/facet-summary/owl/edit', $summaries, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results');
    $this->assertText($block->label());
    $this->assertFacetBlocksAppear();

    $this->clickLink('apple');
    $list_items = $this->getSession()
      ->getPage()
      ->findById('block-' . $block->id())
      ->findAll('css', 'li');
    $this->assertCount(1, $list_items);

    $this->clickLink('item');
    $list_items = $this->getSession()
      ->getPage()
      ->findById('block-' . $block->id())
      ->findAll('css', 'li');
    $this->assertCount(2, $list_items);

    $this->checkShowCountProcessor();
    $this->checkResetFacetsProcessor();
  }

  /**
   * Check that the disabling of the cache works.
   */
  public function testViewsCacheDisable() {
    // Load the view, verify cache settings.
    $view = Views::getView('search_api_test_view');
    $view->setDisplay('page_1');
    $current_cache = $view->display_handler->getOption('cache');
    $this->assertEquals('none', $current_cache['type']);
    $view->display_handler->setOption('cache', ['type' => 'tag']);
    $view->save();
    $current_cache = $view->display_handler->getOption('cache');
    $this->assertEquals('tag', $current_cache['type']);

    // Create a facet and check for the cache disabled message.
    $id = "western_screech_owl";
    $name = "Western screech owl";
    $values = [
      'name' => $name,
      'id' => $id,
      'facet_source_id' => 'search_api:views_page__search_api_test_view__page_1',
    ];
    $this->drupalPostForm('admin/config/search/facets/add-facet-summary', $values, 'Save');
    $this->assertSession()->pageTextContains('Caching of view Search API Test Fulltext search view has been disabled.');

    // Check the view's cache settings again to see if they've been updated.
    $view = Views::getView('search_api_test_view');
    $view->setDisplay('page_1');
    $current_cache = $view->display_handler->getOption('cache');
    $this->assertEquals('none', $current_cache['type']);
  }

  /**
   * Tests configuring show_count processor.
   */
  protected function configureShowCountProcessor() {
    $this->assertSession()->checkboxNotChecked('edit-facets-summary-settings-show-count-status');
    $this->drupalPostForm(NULL, ['facets_summary_settings[show_count][status]' => TRUE], 'Save');
    $this->assertSession()->checkboxChecked('edit-facets-summary-settings-show-count-status');
    $this->assertSession()->pageTextContains(t('Facets Summary Owl has been updated.'));
  }

  /**
   * Tests configuring reset facets processor.
   */
  protected function configureResetFacetsProcessor() {
    $this->assertSession()->checkboxNotChecked('edit-facets-summary-settings-reset-facets-status');
    $this->drupalPostForm(NULL, ['facets_summary_settings[reset_facets][status]' => TRUE], 'Save');
    $this->assertSession()->checkboxChecked('edit-facets-summary-settings-reset-facets-status');
    $this->assertSession()->pageTextContains(t('Facets Summary Owl has been updated.'));

    $this->assertSession()->fieldExists('facets_summary_settings[reset_facets][settings][link_text]');
    $this->drupalPostForm(NULL, ['facets_summary_settings[reset_facets][settings][link_text]' => 'Reset facets'], 'Save');
    $this->assertSession()->pageTextContains(t('Facets Summary Owl has been updated.'));
    $this->assertSession()->fieldValueEquals('facets_summary_settings[reset_facets][settings][link_text]', 'Reset facets');
  }

  /**
   * Tests show_count processor.
   */
  protected function checkShowCountProcessor() {
    // Create new facets summary.
    FacetsSummary::create([
      'id' => 'show_count',
      'name' => 'Show count summary',
      'facet_source_id' => 'search_api:views_page__search_api_test_view__page_1',
      'facets' => [
        'giraffe' => [
          'checked' => 1,
          'label' => 'Giraffe',
          'separator' => ',',
          'weight' => 0,
          'show_count' => 0,
        ],
        'llama' => [
          'checked' => 1,
          'label' => 'Llama',
          'separator' => ',',
          'weight' => 0,
          'show_count' => 0,
        ],
      ],
      'processor_configs' => [
        'show_count' => [
          'processor_id' => 'show_count',
          'weights' => ['build' => -10],
        ],
      ],
    ])->save();

    // Clear the cache after the new facet summary entity was created.
    $this->resetAll();

    // Place a block and test show_count processor.
    $this->drupalPlaceBlock('facets_summary_block:show_count', ['region' => 'footer', 'id' => 'show-count']);
    $this->drupalGet('search-api-test-fulltext');

    $this->assertSession()->pageTextNotContains('5 results found');

    $this->clickLink('apple');
    $this->assertSession()->pageTextContains('2 results found');

    $this->clickLink('item');
    $this->assertSession()->pageTextContains('1 result found');
  }

  /**
   * Tests reset facets processor.
   */
  protected function checkResetFacetsProcessor() {
    // Create new facets summary.
    FacetsSummary::create([
      'id' => 'reset_facets',
      'name' => t('Reset facets summary'),
      'facet_source_id' => 'search_api:views_page__search_api_test_view__page_1',
      'facets' => [
        'giraffe' => [
          'checked' => 1,
          'label' => 'Giraffe',
          'separator' => ',',
          'weight' => 0,
          'show_count' => 0,
        ],
        'llama' => [
          'checked' => 1,
          'label' => 'Llama',
          'separator' => ',',
          'weight' => 0,
          'show_count' => 0,
        ],
      ],
      'processor_configs' => [
        'reset_facets' => [
          'processor_id' => 'reset_facets',
          'weights' => ['build' => -10],
          'settings' => ['link_text' => 'Reset facets'],
        ],
      ],
    ])->save();

    // Clear the cache after the new facet summary entity was created.
    $this->resetAll();

    // Place a block and test reset facets processor.
    $this->drupalPlaceBlock('facets_summary_block:reset_facets', ['region' => 'footer', 'id' => 'reset-facets']);
    $this->drupalGet('search-api-test-fulltext');

    $this->assertSession()->pageTextContains(t('Displaying 5 search results'));
    $this->assertSession()->pageTextNotContains(t('Reset facets'));

    $this->clickLink('apple');
    $this->assertSession()->pageTextContains(t('Displaying 2 search results'));
    $this->assertSession()->pageTextContains(t('Reset facets'));

    $this->clickLink(t('Reset facets'));
    $this->assertSession()->pageTextContains(t('Displaying 5 search results'));
    $this->assertSession()->pageTextNotContains(t('Reset facets'));
  }

}
