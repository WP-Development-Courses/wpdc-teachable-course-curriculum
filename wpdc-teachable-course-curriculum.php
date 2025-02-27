<?php
/**
 * Plugin Name: WPDC - Teachable Course Curriculum
 * Description: Retrieves the curriculum of a course from Teachable, and displays it as a block.
 * Requires Plugins: teachable
 * Update URI: false
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum;

const BUILD_DIR = __DIR__ . '/build';

require_once __DIR__ . '/inc/blocks/curriculum.php';
Blocks\Curriculum\bootstrap();
