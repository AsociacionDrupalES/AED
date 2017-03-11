<?php

namespace Drupal\Tests\rest_facets\Functional;

use Drupal\Tests\facets\Functional\FacetsTestBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the integration of REST-views and facets.
 *
 * @group facets
 */
class RestIntegrationTest extends FacetsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'rest_view',
    'rest_facets',
    'rest',
    'hal',
    'serialization',
    'views_ui',
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
   * Tests that the facet results are correct.
   */
  public function testRestResults() {
    global $base_url;
    $name = 'Type';
    $id = 'type';

    // Add a new facet to filter by content type.
    $this->createFacet($name, $id, 'type', 'rest_export_1', 'search_api_rest_test_view');

    // Use the array widget.
    $facet_edit_page = '/admin/config/search/facets/' . $id . '/edit';
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);

    $this->drupalPostForm(NULL, ['widget' => 'array'], $this->t('Configure widget'));
    $values['widget'] = 'array';
    $values['widget_config[show_numbers]'] = TRUE;
    $values['facet_sorting[count_widget_order][status]'] = TRUE;
    $values['facet_sorting[count_widget_order][settings][sort]'] = 'ASC';
    $values['facet_sorting[display_value_widget_order][status]'] = FALSE;
    $values['facet_sorting[active_widget_order][status]'] = FALSE;
    $values['facet_settings[query_operator]'] = 'or';

    $this->drupalPostForm(NULL, $values, $this->t('Save'));

    drupal_flush_all_caches();

    $name = 'Keywords';
    $id = 'keywords';
    // Add a new facet to filter by keywords.
    $this->createFacet($name, $id, 'keywords', 'rest_export_1', 'search_api_rest_test_view');

    // Use the array widget.
    $facet_edit_page = '/admin/config/search/facets/' . $id . '/edit';
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);

    $this->drupalPostForm(NULL, ['widget' => 'array'], $this->t('Configure widget'));
    $values['widget'] = 'array';
    $values['widget_config[show_numbers]'] = TRUE;
    $values['facet_sorting[count_widget_order][status]'] = TRUE;
    $values['facet_sorting[count_widget_order][settings][sort]'] = 'ASC';
    $values['facet_sorting[display_value_widget_order][status]'] = FALSE;
    $values['facet_sorting[active_widget_order][status]'] = FALSE;
    $values['facet_settings[query_operator]'] = 'or';

    $this->drupalPostForm(NULL, $values, $this->t('Save'));

    // Get the output from the rest view and decode it into an array.
    $json = $this->drupalGet('facets-rest');
    $json_decoded = json_decode($json);

    $this->assertEqual(count($json_decoded->search_results), 5);

    // Verify the facet "Type".
    $results = [
      'article' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aarticle',
        'count' => 2,
      ],
      'item' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem',
        'count' => 3,
      ],
    ];

    foreach ($json_decoded->facets[1][0]->type as $result) {
      $value = $result->values->value;
      $this->assertEqual($result->url, $results[$value]['url']);
      $this->assertEqual($result->values->count, $results[$value]['count']);
    }

    // Verify the facet "Keywords".
    $results = [
      'banana' => [
        'url' => $base_url . '/facets-rest?f[0]=keywords%3Abanana',
        'count' => 1,
      ],
      'strawberry' => [
        'url' => $base_url . '/facets-rest?f[0]=keywords%3Astrawberry',
        'count' => 2,
      ],
      'apple' => [
        'url' => $base_url . '/facets-rest?f[0]=keywords%3Aapple',
        'count' => 2,
      ],
      'orange' => [
        'url' => $base_url . '/facets-rest?f[0]=keywords%3Aorange',
        'count' => 3,
      ],
      'grape' => [
        'url' => $base_url . '/facets-rest?f[0]=keywords%3Agrape',
        'count' => 3,
      ],
    ];

    foreach ($json_decoded->facets[0][0]->keywords as $result) {
      $value = $result->values->value;
      $this->assertEqual($result->url, $results[$value]['url']);
      $this->assertEqual($result->values->count, $results[$value]['count']);
    }

    // Filter and verify that the results are correct.
    $json = $this->drupalGet($base_url . '/facets-rest?f[0]=type%3Aitem');
    $json_decoded = json_decode($json);

    $this->assertEqual(count($json_decoded->search_results), 3);

    $results = [
      'article' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem&f[1]=type%3Aarticle',
        'count' => 2,
      ],
      'item' => [
        'url' => $base_url . '/facets-rest',
        'count' => 3,
      ],
      'banana' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem&f[1]=keywords%3Abanana',
        'count' => 0,
      ],
      'strawberry' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem&f[1]=keywords%3Astrawberry',
        'count' => 0,
      ],
      'apple' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem&f[1]=keywords%3Aapple',
        'count' => 1,
      ],
      'orange' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem&f[1]=keywords%3Aorange',
        'count' => 2,
      ],
      'grape' => [
        'url' => $base_url . '/facets-rest?f[0]=type%3Aitem&f[1]=keywords%3Agrape',
        'count' => 1,
      ],
    ];

    foreach ($json_decoded->facets[1][0]->type as $result) {
      $value = $result->values->value;
      $this->assertEqual($result->url, $results[$value]['url']);
      $this->assertEqual($result->values->count, $results[$value]['count']);
    }

    foreach ($json_decoded->facets[0][0]->keywords as $result) {
      $value = $result->values->value;
      $this->assertEqual($result->url, $results[$value]['url']);
      $this->assertEqual($result->values->count, $results[$value]['count']);
    }

  }

}
