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

// Ensure that we got everything we need to start the plugin.
require_once __DIR__ . '/inc/start-up-check.php';
if ( ! Start_Up_Check\has_teachable_wp_key() ) {
	return;
}

require_once __DIR__ . '/inc/blocks/curriculum.php';
Blocks\Curriculum\bootstrap();
