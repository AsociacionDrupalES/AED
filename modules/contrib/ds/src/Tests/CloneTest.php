<?php

namespace Drupal\ds\Tests;

/**
 * Tests for the manage display tab in Display Suite.
 *
 * @group ds
 */
class CloneTest extends FastTestBase {

  use DsTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'user',
    'comment',
    'field_ui',
    'ds',
  ];

  /**
   * Test adding a cloning a layout.
   */
  public function testClone() {
    // Go to the teaser display mode and select a DS layout.
    $this->dsSelectLayout([], [], 'admin/structure/types/manage/article/display/teaser');
    $this->assertText('Two column stacked layout');

    // Go back to the default view mode.
    $this->drupalGet('admin/structure/types/manage/article/display');

    // Clone layout, this will clone from the teaser view mode.
    $this->drupalPostForm(NULL, [], 'Clone layout');

    // Check for message.
    $this->assertText('The layout has been cloned.');

    // Check that this now also has the expected region layout.
    $this->assertOptionSelected('edit-layout', 'ds_2col_stacked');
  }

}
