<?php
/**
 * Block to display a Teachable course curriculum.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum\Blocks\Curriculum;

use const WPDC\Teachable_Course_Curriculum\BUILD_DIR;

/**
 * Add hooks.
 */
function bootstrap(): void {
	add_action( 'init', __NAMESPACE__ . '\\register_block_type_from_json' );
}

/**
 * Register the block type using its block.json.
 */
function register_block_type_from_json(): void {
	register_block_type( BUILD_DIR . '/curriculum' );
}
