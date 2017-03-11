<?php

namespace Drupal\Tests\facets\Functional;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the processor functionality.
 *
 * @group facets
 */
class ProcessorIntegrationTest extends FacetsTestBase {

  /**
   * The url of the edit form.
   *
   * @var string
   */
  protected $editForm;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);

    // Set up example content types and insert 10 new content items.
    $this->setUpExampleStructure();
    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');
    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');
  }

  /**
   * {@inheritdoc}
   */
  protected function installModulesFromClassProperty(ContainerInterface $container) {
    // This will just set the Drupal state to include the necessary bundles for
    // our test entity type. Otherwise, fields from those bundles won't be found
    // and thus removed from the test index. (We can't do it in setUp(), before
    // calling the parent method, since the container isn't set up at that
    // point.)
    $bundles = array(
      'entity_test_mulrev_changed' => array('label' => 'Entity Test Bundle'),
      'item' => array('label' => 'item'),
      'article' => array('label' => 'article'),
    );
    \Drupal::state()->set('entity_test_mulrev_changed.bundles', $bundles);

    parent::installModulesFromClassProperty($container);
  }

  /**
   * Tests for the processors behavior in the backend.
   */
  public function testProcessorAdmin() {
    $facet_name = "Guanaco";
    $facet_id = "guanaco";

    $this->createFacet($facet_name, $facet_id);

    // Go to the processors form and check that the count limit processor is not
    // checked.
    $this->drupalGet('admin/config/search/facets/' . $facet_id . '/edit');
    $this->assertNoFieldChecked('edit-facet-settings-count-limit-status');

    $form = ['facet_settings[count_limit][status]' => TRUE];
    $this->drupalPostForm(NULL, $form, 'Save');
    $this->assertResponse(200);
    $this->assertFieldChecked('edit-facet-settings-count-limit-status');

    // Enable the sort processor and change sort direction, check that the
    // change is persisted.
    $form = [
      'facet_sorting[active_widget_order][status]' => TRUE,
      'facet_sorting[active_widget_order][settings][sort]' => 'DESC',
    ];
    $this->drupalPostForm(NULL, $form, 'Save');
    $this->assertResponse(200);
    $this->assertFieldChecked('edit-facet-sorting-active-widget-order-status');
    $this->assertFieldChecked('edit-facet-sorting-active-widget-order-settings-sort-desc');

    // Add an extra processor so we can test the weights as well.
    $form = [
      'facet_settings[hide_non_narrowing_result_processor][status]' => TRUE,
      'facet_settings[count_limit][status]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $form, 'Save');
    $this->assertResponse(200);
    $this->assertFieldChecked('edit-facet-settings-count-limit-status');
    $this->assertFieldChecked('edit-facet-settings-hide-non-narrowing-result-processor-status');
    $this->assertOptionSelected('edit-processors-count-limit-weights-build', -10);
    $this->assertOptionSelected('edit-processors-hide-non-narrowing-result-processor-weights-build', -10);

    // Change the weight of one of the processors and test that the weight
    // change persisted.
    $form = [
      'facet_settings[hide_non_narrowing_result_processor][status]' => TRUE,
      'facet_settings[count_limit][status]' => TRUE,
      'processors[hide_non_narrowing_result_processor][weights][build]' => 5,
    ];
    $this->drupalPostForm(NULL, $form, 'Save');
    $this->assertFieldChecked('edit-facet-settings-count-limit-status');
    $this->assertFieldChecked('edit-facet-settings-hide-non-narrowing-result-processor-status');
    $this->assertOptionSelected('edit-processors-count-limit-weights-build', -10);
    $this->assertOptionSelected('edit-processors-hide-non-narrowing-result-processor-weights-build', 5);
  }

  /**
   * Tests the for processors in the frontend with a 'keywords' facet.
   */
  public function testProcessorIntegration() {
    $facet_name = "Vicuña";
    $facet_id = "vicuna";
    $this->editForm = 'admin/config/search/facets/' . $facet_id . '/edit';

    $this->createFacet($facet_name, $facet_id, 'keywords');
    $this->drupalPostForm($this->editForm, ['facet_settings[query_operator]' => 'and'], 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertText('grape');
    $this->assertText('orange');
    $this->assertText('apple');
    $this->assertText('strawberry');
    $this->assertText('banana');

    $this->checkCountLimitProcessor();
    $this->checkExcludeItems();
    $this->checkHideNonNarrowingProcessor();
    $this->checkHideActiveItems();
  }

  /**
   * Tests the for sorting processors in the frontend with a 'keywords' facet.
   */
  public function testSortingWidgets() {
    $facet_name = "Huacaya alpaca";
    $facet_id = "huacaya_alpaca";
    $this->editForm = 'admin/config/search/facets/' . $facet_id . '/edit';

    $this->createFacet($facet_name, $facet_id, 'keywords');

    $this->checkSortByActive();
    $this->checkSortByCount();
    $this->checkSortByDisplay();
    $this->checkSortByRaw();
  }

  /**
   * Tests sorting of results.
   */
  public function testResultSorting() {
    $id = 'burrowing_owl';
    $name = 'Burrowing owl';
    $this->editForm = 'admin/config/search/facets/' . $id . '/edit';

    $this->createFacet($name, $id, 'keywords');
    $this->disableAllFacetSorts();

    $values = [
      'facet_sorting[display_value_widget_order][status]' => TRUE,
      'widget_config[show_numbers]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $values, 'Save');

    $expected_results = [
      'apple',
      'banana',
      'grape',
      'orange',
      'strawberry',
    ];

    $this->drupalGet('search-api-test-fulltext');
    foreach ($expected_results as $k => $link) {
      if ($k > 0) {
        $x = $expected_results[($k - 1)];
        $y = $expected_results[$k];
        $this->assertStringPosition($x, $y);
      }
    }

    // Sort by count, then by display value.
    $values['facet_sorting[count_widget_order][status]'] = TRUE;
    $values['facet_sorting[count_widget_order][settings][sort]'] = 'ASC';
    $values['processors[count_widget_order][weights][sort]'] = 1;
    $values['facet_sorting[display_value_widget_order][status]'] = TRUE;
    $values['processors[display_value_widget_order][weights][sort]'] = 2;
    $this->disableAllFacetSorts();
    $this->drupalPostForm($this->editForm, $values, 'Save');

    $expected_results = [
      'banana',
      'apple',
      'strawberry',
      'grape',
      'orange',
    ];

    $this->drupalGet('search-api-test-fulltext');
    foreach ($expected_results as $k => $link) {
      if ($k > 0) {
        $x = $expected_results[($k - 1)];
        $y = $expected_results[$k];
        $this->assertStringPosition($x, $y);
      }
    }

    $values['facet_sorting[display_value_widget_order][status]'] = TRUE;
    $values['facet_sorting[count_widget_order][status]'] = TRUE;
    $values['facet_sorting[count_widget_order][settings][sort]'] = 'ASC';
    $this->drupalPostForm($this->editForm, $values, 'Save');
    $this->assertFieldChecked('edit-facet-sorting-display-value-widget-order-status');
    $this->assertFieldChecked('edit-facet-sorting-count-widget-order-status');

    $expected_results = [
      'banana',
      'apple',
      'strawberry',
      'grape',
      'orange',
    ];

    $this->drupalGet('search-api-test-fulltext');
    foreach ($expected_results as $k => $link) {
      if ($k > 0) {
        $x = $expected_results[($k - 1)];
        $y = $expected_results[$k];
        $this->assertStringPosition($x, $y);
      }
    }
  }

  /**
   * Tests the count limit processor.
   */
  protected function checkCountLimitProcessor() {
    $this->drupalGet($this->editForm);

    $form = [
      'widget_config[show_numbers]' => TRUE,
      'facet_settings[count_limit][status]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
    $this->assertResponse(200);
    $this->assertFieldChecked('edit-facet-settings-count-limit-status');
    $form = [
      'widget_config[show_numbers]' => TRUE,
      'facet_settings[count_limit][status]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $form = [
      'widget_config[show_numbers]' => TRUE,
      'facet_settings[count_limit][status]' => TRUE,
      'facet_settings[count_limit][settings][minimum_items]' => 5,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertFacetLabel('grape (6)');
    $this->assertNoText('apple');

    $form = [
      'widget_config[show_numbers]' => TRUE,
      'facet_settings[count_limit][status]' => TRUE,
      'facet_settings[count_limit][settings][minimum_items]' => 1,
      'facet_settings[count_limit][settings][maximum_items]' => 5,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertNoText('grape');
    $this->assertFacetLabel('apple (4)');

    $form = [
      'widget_config[show_numbers]' => FALSE,
      'facet_settings[count_limit][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests the exclude items.
   */
  protected function checkExcludeItems() {
    $form = [
      'facet_settings[exclude_specified_items][status]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $form = [
      'facet_settings[exclude_specified_items][status]' => TRUE,
      'facet_settings[exclude_specified_items][settings][exclude]' => 'banana',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertText('grape');
    $this->assertNoText('banana');

    $form = [
      'facet_settings[exclude_specified_items][status]' => TRUE,
      'facet_settings[exclude_specified_items][settings][exclude]' => '(.*)berry',
      'facet_settings[exclude_specified_items][settings][regex]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertNoText('strawberry');
    $this->assertText('grape');

    $form = [
      'facet_settings[exclude_specified_items][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests hiding non-narrowing results.
   */
  protected function checkHideNonNarrowingProcessor() {
    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertFacetLabel('apple');

    $this->clickLink('apple');
    $this->assertText('Displaying 4 search results');
    $this->assertFacetLabel('grape');

    $form = [
      'facet_settings[hide_non_narrowing_result_processor][status]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertFacetLabel('apple');

    $this->clickLink('apple');
    $this->assertText('Displaying 4 search results');
    $this->assertNoLink('grape');

    $form = [
      'facet_settings[hide_non_narrowing_result_processor][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests hiding active results.
   */
  protected function checkHideActiveItems() {
    $form = [
      'facet_settings[hide_active_items_processor][status]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 10 search results');
    $this->assertFacetLabel('grape');
    $this->assertFacetLabel('banana');

    $this->clickLink('grape');
    $this->assertText('Displaying 6 search results');
    $this->assertNoLink('grape');
    $this->assertFacetLabel('banana');

    $form = [
      'facet_settings[hide_active_items_processor][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests the active widget order.
   */
  protected function checkSortByActive() {
    $this->disableAllFacetSorts();
    $form = [
      'facet_sorting[active_widget_order][status]' => TRUE,
      'facet_sorting[active_widget_order][settings][sort]' => 'ASC',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->clickLink('strawberry');
    $this->assertStringPosition('strawberry', 'grape');

    $form = [
      'facet_sorting[active_widget_order][status]' => TRUE,
      'facet_sorting[active_widget_order][settings][sort]' => 'DESC',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->clickLink('strawberry');
    $this->assertStringPosition('grape', 'strawberry');

    $form = [
      'facet_sorting[active_widget_order][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests the active widget order.
   */
  protected function checkSortByCount() {
    $this->disableAllFacetSorts();
    $form = [
      'widget_config[show_numbers]' => TRUE,
      'facet_sorting[count_widget_order][status]' => TRUE,
      'facet_sorting[count_widget_order][settings][sort]' => 'ASC',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertStringPosition('banana', 'apple');
    $this->assertStringPosition('banana', 'strawberry');
    $this->assertStringPosition('apple', 'orange');

    $form = [
      'facet_sorting[count_widget_order][status]' => TRUE,
      'facet_sorting[count_widget_order][settings][sort]' => 'DESC',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertStringPosition('apple', 'banana');
    $this->assertStringPosition('strawberry', 'banana');
    $this->assertStringPosition('orange', 'apple');

    $form = [
      'widget_config[show_numbers]' => FALSE,
      'facet_sorting[count_widget_order][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests the display order.
   */
  protected function checkSortByDisplay() {
    $this->disableAllFacetSorts();
    $form = ['facet_sorting[display_value_widget_order][status]' => TRUE];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertStringPosition('grape', 'strawberry');
    $this->assertStringPosition('apple', 'banana');

    $form = [
      'facet_sorting[display_value_widget_order][status]' => TRUE,
      'facet_sorting[display_value_widget_order][settings][sort]' => 'DESC',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertStringPosition('strawberry', 'grape');
    $this->assertStringPosition('banana', 'apple');

    $form = ['facet_sorting[display_value_widget_order][status]' => FALSE];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Tests the display order.
   */
  protected function checkSortByRaw() {
    $this->disableAllFacetSorts();
    $form = [
      'facet_sorting[raw_value_widget_order][status]' => TRUE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertStringPosition('grape', 'strawberry');
    $this->assertStringPosition('apple', 'banana');

    $form = [
      'facet_sorting[raw_value_widget_order][status]' => TRUE,
      'facet_sorting[raw_value_widget_order][settings][sort]' => 'DESC',
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertStringPosition('strawberry', 'grape');
    $this->assertStringPosition('banana', 'apple');

    $form = [
      'facet_sorting[raw_value_widget_order][status]' => FALSE,
    ];
    $this->drupalPostForm($this->editForm, $form, 'Save');
  }

  /**
   * Creates a facet block by id.
   *
   * @param string $id
   *   The id of the block.
   */
  protected function createFacetBlock($id) {
    $plugin_id = 'facet_block:' . $id;
    $settings = [
      'region' => 'footer',
      'id' => str_replace('_', '-', $id),
    ];
    $this->blocks[$id] = $this->drupalPlaceBlock($plugin_id, $settings);
  }

  /**
   * Disables all sorting processors for a clean testing base.
   */
  protected function disableAllFacetSorts($path = FALSE) {
    $settings = [
      'facet_sorting[raw_value_widget_order][status]' => FALSE,
      'facet_sorting[display_value_widget_order][status]' => FALSE,
      'facet_sorting[count_widget_order][status]' => FALSE,
      'facet_sorting[active_widget_order][status]' => FALSE,
    ];
    if (!$path) {
      $path = $this->editForm;
    }
    $this->drupalPostForm($path, $settings, 'Save');
  }

}
