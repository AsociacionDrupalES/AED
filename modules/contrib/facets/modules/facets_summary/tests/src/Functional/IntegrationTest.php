<?php

namespace Drupal\facets_summary\Tests;

use Drupal\Tests\facets\Functional\FacetsTestBase;
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
   * Tests the overall functionality of the Facets summary admin UI.
   */
  public function testFramework() {
    $this->drupalGet('admin/config/search/facets');
    $this->assertNoText('Facets Summary');

    $values = [
      'name' => 'Owl',
      'id' => 'owl',
      'facet_source_id' => 'views_page:search_api_test_view__page_1',
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

    $this->drupalGet('admin/config/search/facets/facet-summary/owl/edit');
    $this->assertNoText('No facets found.');
    $this->assertText('Llama');
  }

}
