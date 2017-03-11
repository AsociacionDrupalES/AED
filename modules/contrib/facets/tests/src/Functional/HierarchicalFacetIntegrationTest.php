<?php

namespace Drupal\Tests\facets\Functional;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Language\LanguageInterface;
use Drupal\search_api\Item\Field;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;

/**
 * Tests the hierarchical facets implementation.
 *
 * @group facets
 */
class HierarchicalFacetIntegrationTest extends FacetsTestBase {

  use TaxonomyTestTrait;
  use EntityReferenceTestTrait;

  /**
   * Drupal vocabulary.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * The fieldname for the referenced term.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Uri to the facets edit page.
   *
   * @var string
   */
  protected $facetEditPage;

  /**
   * An array of taxonomy terms.
   *
   * @var \Drupal\taxonomy\Entity\Term[]
   */
  protected $parents = [];

  /**
   * An array of taxonomy terms.
   *
   * @var \Drupal\taxonomy\Entity\Term[]
   */
  protected $terms = [];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);

    // Create hierarchical terms in a new vocabulary.
    $this->vocabulary = $this->createVocabulary();
    $this->createHierarchialTermStructure();

    // Default content that is extended with a term reference field below.
    $this->setUpExampleStructure();

    // Create a taxonomy_term_reference field on the article and item.
    $this->fieldName = Unicode::strtolower($this->randomMachineName());
    $fieldLabel = $this->randomString();

    $this->createEntityReferenceField('entity_test_mulrev_changed', 'article', $this->fieldName, $fieldLabel, 'taxonomy_term');
    $this->createEntityReferenceField('entity_test_mulrev_changed', 'item', $this->fieldName, $fieldLabel, 'taxonomy_term');

    $this->insertExampleContent();

    // Add fields to index.
    $index = $this->getIndex();

    // Index the taxonomy and entity reference fields.
    $term_field = new Field($index, $this->fieldName);
    $term_field->setType('integer');
    $term_field->setPropertyPath($this->fieldName);
    $term_field->setDatasourceId('entity:entity_test_mulrev_changed');
    $term_field->setLabel($fieldLabel);
    $index->addField($term_field);

    $index->save();
    $this->indexItems($this->indexId);

    $facet_name = 'hierarchical facet';
    $facet_id = 'hierarchical_facet';
    $this->facetEditPage = 'admin/config/search/facets/' . $facet_id . '/edit';

    $this->createFacet($facet_name, $facet_id, $this->fieldName);

    // Make absolutely sure the ::$blocks variable doesn't pass information
    // along between tests.
    $this->blocks = NULL;
  }

  /**
   * Test the hierarchical facets functionality.
   */
  public function testHierarchicalFacet() {
    $this->verifyUseHierarchyOption();
    $this->verifyExpandHierarchyOption();
    $this->verifyEnableParentWhenChildGetsDisabledOption();
  }

  /**
   * Verify the backend option "Use hierarchy" is working.
   */
  protected function verifyUseHierarchyOption() {
    // Verify that the link to the index processors settings page is available.
    $this->drupalGet($this->facetEditPage);
    $this->clickLink('Search api index processor configuration');
    $this->assertResponse(200);

    // Enable hierarchical facets and translation of entity ids to its names for
    // a better readability.
    $this->drupalGet($this->facetEditPage);
    $edit = [
      'facet_settings[use_hierarchy]' => '1',
      'facet_settings[translate_entity][status]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Child elements should be collapsed and invisible.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('Parent 1');
    $this->assertFacetLabel('Parent 2');
    $this->assertNoLink('Child 1');
    $this->assertNoLink('Child 2');
    $this->assertNoLink('Child 3');
    $this->assertNoLink('Child 4');

    // Click the first parent and make sure its children are visible.
    $this->clickLink('Parent 1');
    $this->checkFacetIsActive('Parent 1');
    $this->assertFacetLabel('Child 1');
    $this->assertFacetLabel('Child 2');
    $this->assertNoLink('Child 3');
    $this->assertNoLink('Child 4');
  }

  /**
   * Verify the "Always expand hierarchy" option is working.
   */
  protected function verifyExpandHierarchyOption() {
    // Expand the hierarchy and verify that all items are visible initially.
    $this->drupalGet($this->facetEditPage);
    $edit = [
      'facet_settings[expand_hierarchy]' => '1',
      'facet_settings[use_hierarchy]' => '1',
      'facet_settings[translate_entity][status]' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->drupalGet('search-api-test-fulltext');

    $this->assertFacetLabel('Parent 1');
    $this->assertFacetLabel('Parent 2');
    $this->assertFacetLabel('Child 1');
    $this->assertFacetLabel('Child 2');
    $this->assertFacetLabel('Child 3');
    $this->assertFacetLabel('Child 4');
  }

  /**
   * Verify the "Enable parent when child gets disabled" option is working.
   */
  protected function verifyEnableParentWhenChildGetsDisabledOption() {
    // Make sure the option is disabled initially.
    $this->drupalGet($this->facetEditPage);
    $edit = [
      'facet_settings[expand_hierarchy]' => '1',
      'facet_settings[enable_parent_when_child_gets_disabled]' => FALSE,
      'facet_settings[use_hierarchy]' => '1',
      'facet_settings[translate_entity][status]' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->drupalGet('search-api-test-fulltext');

    // Enable a child under Parent 2.
    $this->clickLink('Child 4');
    $this->checkFacetIsActive('Child 4');
    $this->checkFacetIsNotActive('Parent 2');

    // Uncheck the facet again.
    $this->clickLink('(-) Child 4');
    $this->checkFacetIsNotActive('Child 4');
    $this->checkFacetIsNotActive('Parent 2');

    // Enable the option.
    $this->drupalGet($this->facetEditPage);
    $edit = [
      'facet_settings[expand_hierarchy]' => '1',
      'facet_settings[enable_parent_when_child_gets_disabled]' => '1',
      'facet_settings[use_hierarchy]' => '1',
      'facet_settings[translate_entity][status]' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->drupalGet('search-api-test-fulltext');

    // Enable a child under Parent 2.
    $this->clickLink('Child 4');
    $this->checkFacetIsActive('Child 4');
    $this->checkFacetIsNotActive('Parent 2');

    // Uncheck the facet again and see if Parent 2 is active now.
    $this->clickLink('(-) Child 4');
    $this->checkFacetIsNotActive('Child 4');
    $this->checkFacetIsActive('Parent 2');
  }

  /**
   * Setup a term structure for our test.
   */
  protected function createHierarchialTermStructure() {
    // Generate 2 parent terms.
    foreach (['Parent 1', 'Parent 2'] as $name) {
      $this->parents[$name] = Term::create([
        'name' => $name,
        'description' => '',
        'vid' => $this->vocabulary->id(),
        'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      ]);
      $this->parents[$name]->save();
    }

    // Generate 4 child terms.
    for ($i = 1; $i <= 4; $i++) {
      $this->terms[$i] = Term::create([
        'name' => sprintf('Child %d', $i),
        'description' => '',
        'vid' => $this->vocabulary->id(),
        'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      ]);
      $this->terms[$i]->save();
    }

    // Build up the hierarchy.
    $this->terms[1]->parent = [$this->parents['Parent 1']->id()];
    $this->terms[1]->save();

    $this->terms[2]->parent = [$this->parents['Parent 1']->id()];
    $this->terms[2]->save();

    $this->terms[3]->parent = [$this->parents['Parent 2']->id()];
    $this->terms[3]->save();

    $this->terms[4]->parent = [$this->parents['Parent 2']->id()];
    $this->terms[4]->save();
  }

  /**
   * Creates several test entities with the term-reference field.
   */
  protected function insertExampleContent() {
    $entity_test_storage = \Drupal::entityTypeManager()
      ->getStorage('entity_test_mulrev_changed');

    $this->entities[1] = $entity_test_storage->create(array(
      'name' => 'foo bar baz',
      'body' => 'test test',
      'type' => 'item',
      'keywords' => array('orange'),
      'category' => 'item_category',
      $this->fieldName => [$this->parents['Parent 1']->id()],
    ));
    $this->entities[1]->save();

    $this->entities[2] = $entity_test_storage->create(array(
      'name' => 'foo test',
      'body' => 'bar test',
      'type' => 'item',
      'keywords' => array('orange', 'apple', 'grape'),
      'category' => 'item_category',
      $this->fieldName => [$this->parents['Parent 2']->id()],
    ));
    $this->entities[2]->save();

    $this->entities[3] = $entity_test_storage->create(array(
      'name' => 'bar',
      'body' => 'test foobar',
      'type' => 'item',
      $this->fieldName => [$this->terms[1]->id()],
    ));
    $this->entities[3]->save();

    $this->entities[4] = $entity_test_storage->create(array(
      'name' => 'foo baz',
      'body' => 'test test test',
      'type' => 'article',
      'keywords' => array('apple', 'strawberry', 'grape'),
      'category' => 'article_category',
      $this->fieldName => [$this->terms[2]->id()],
    ));
    $this->entities[4]->save();

    $this->entities[5] = $entity_test_storage->create(array(
      'name' => 'bar baz',
      'body' => 'foo',
      'type' => 'article',
      'keywords' => array('orange', 'strawberry', 'grape', 'banana'),
      'category' => 'article_category',
      $this->fieldName => [$this->terms[3]->id()],
    ));
    $this->entities[5]->save();

    $this->entities[6] = $entity_test_storage->create(array(
      'name' => 'bar baz',
      'body' => 'foo',
      'type' => 'article',
      'keywords' => array('orange', 'strawberry', 'grape', 'banana'),
      'category' => 'article_category',
      $this->fieldName => [$this->terms[4]->id()],
    ));
    $this->entities[6]->save();
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
   * Convert facet name to machine name.
   *
   * @param string $facet_name
   *   The name of the facet.
   *
   * @return string
   *   The facet name changed to a machine name.
   */
  protected function convertNameToMachineName($facet_name) {
    return preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));
  }

}
