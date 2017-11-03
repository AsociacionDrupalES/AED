<?php

namespace Drupal\Tests\search_api\Kernel\Views;

use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Plugin\views\field\SearchApiStandard;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\search_api\Processor\ConfigurablePropertyInterface;
use Drupal\search_api\Processor\ProcessorInterface;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Utility\Utility;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Tests whether Views pages correctly create search display plugins.
 *
 * @group search_api
 */
class ViewsPropertyExtractionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'search_api',
    'user',
  ];

  /**
   * Tests whether property extraction works correctly.
   *
   * @param string $property_path
   *   The combined property path of the field.
   * @param string $expected
   *   The expected value on the row.
   * @param bool $pre_set
   *   (optional) Whether to pre-set the values on the row (to check whether
   *   they're correctly passed through).
   * @param bool $return_fields
   *   (optional) Whether to return any fields for the index.
   *
   * @dataProvider propertyExtractionDataProvider
   */
  public function testPropertyExtraction($property_path, $expected, $pre_set = FALSE, $return_fields = TRUE) {
    $datasource_id = 'entity:user';

    /** @var \Drupal\search_api\IndexInterface|\PHPUnit_Framework_MockObject_MockObject $index */
    $index = $this->getMock(IndexInterface::class);
    $property2 = $this->getMock(ConfigurablePropertyInterface::class);
    $property2->method('getProcessorId')->willReturn('processor2');
    $property2->method('getDataType')->willReturn('string');
    $property2->method('defaultConfiguration')->willReturn([]);
    $property2->method('getClass')->willReturn(StringData::class);
    $index->method('getPropertyDefinitions')->willReturnMap([
      [NULL, [
        'property1' => new ProcessorProperty([
          'processor_id' => 'processor1',
        ]),
      ]],
      [$datasource_id, [
        'property2' => $property2,
      ]],
    ]);
    $generate_add_field_values = function ($value) {
      return function (ItemInterface $item) use ($value) {
        foreach ($item->getFields() as $field) {
          $values = [$value];
          $config = $field->getConfiguration();
          if (!empty($config[$value])) {
            $values = [$config[$value]];
          }
          $field->setValues($values);
        }
      };
    };
    $processor1 = $this->getMock(ProcessorInterface::class);
    $processor1->method('addFieldValues')
      ->willReturnCallback($generate_add_field_values('Processor 1'));
    $processor2 = $this->getMock(ProcessorInterface::class);
    $processor2->method('addFieldValues')
      ->willReturnCallback($generate_add_field_values('Processor 2'));
    $index->method('getProcessor')->willReturnMap([
      ['processor1', $processor1],
      ['processor2', $processor2],
    ]);
    $fields_helper = $this->container->get('search_api.fields_helper');
    $property_path_split = Utility::splitCombinedId($property_path);
    $fields = [];
    if ($return_fields) {
      $fields = [
        'foo' => $fields_helper->createField($index, 'foo', [
          'datasource_id' => $property_path_split[0],
          'property_path' => $property_path_split[1],
          'configuration' => [
            'Processor 2' => 'foobar',
          ],
        ]),
        'test' => $fields_helper->createField($index, 'test', [
          'datasource_id' => $property_path_split[0],
          'property_path' => $property_path_split[1],
          'configuration' => [
            'Processor 2' => 'Override',
          ],
        ]),
      ];
    }
    $index->method('getFields')->willReturn($fields);

    $query = $this->getMockBuilder(SearchApiQuery::class)
      ->disableOriginalConstructor()
      ->getMock();
    $query->method('getIndex')->willReturn($index);

    /** @var \Drupal\views\ViewExecutable $view */
    $view = $this->getMockBuilder(ViewExecutable::class)
      ->disableOriginalConstructor()
      ->getMock();
    $view->query = $query;

    /** @var \Drupal\views\Plugin\views\display\DisplayPluginBase $display */
    $display = $this->getMockBuilder(DisplayPluginBase::class)
      ->disableOriginalConstructor()
      ->getMock();

    $configuration = [
      'real field' => $property_path,
      'search_api field' => 'test',
    ];
    $field = new SearchApiStandard($configuration, '', []);
    $field->init($view, $display);
    $field->query();

    $user = User::create([
      'name' => 'Test user',
    ]);
    $object = $user->getTypedData();
    $id = Utility::createCombinedId($datasource_id, $user->id());
    $row = new ResultRow([
      '_item' => $fields_helper->createItemFromObject($index, $object, $id),
      '_object' => $object,
      '_relationship_objects' => [
        NULL => [$object],
      ],
      'search_api_datasource' => $datasource_id,
    ]);
    if ($pre_set) {
      $row->$property_path = ['Pre-set'];
    }
    // For the configurable property, also set the values for special property
    // path.
    if ($property_path === 'entity:user/property2' && $return_fields) {
      $special_path = "$property_path|test";
      if ($pre_set) {
        $row->$special_path = ['Pre-set'];
      }
      // Whether that special property path will actually used by the field
      // plugin depends on whether it finds a matching field.
      if ($return_fields) {
        $property_path = $special_path;
      }
    }
    $values = [$row];

    $field->preRender($values);

    $this->assertEquals([$expected], $row->$property_path);
  }

  /**
   * Provides test data for the property extraction test.
   *
   * @return array[]
   *   Array of argument lists for testPropertyExtraction().
   *
   * @see \Drupal\Tests\search_api\Kernel\Views\ViewsPropertyExtractionTest::testPropertyExtraction()
   */
  public function propertyExtractionDataProvider() {
    return [
      'extract normal property' => ['entity:user/name', 'Test user'],
      'use normal property' => ['entity:user/name', 'Pre-set', TRUE],
      'extract processor property' => ['property1', 'Processor 1'],
      'use processor property' => ['property1', 'Pre-set', TRUE],
      'extract configurable property' => ['entity:user/property2', 'Override'],
      'use configurable property' => ['entity:user/property2', 'Pre-set', TRUE],
      'use overridden configurable property' => ['entity:user/property2', 'Processor 2', FALSE, FALSE],
    ];
  }

}
