<?php
/**
 * Block to display a section of Teachable course curriculum.
 *
 * Child block of the Curriculum block.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum\Blocks\Section;

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
	register_block_type(
		BUILD_DIR . '/section',
		[
			'render_callback' => __NAMESPACE__ . '\\render_block',
		]
	);
}

/**
 * Render the block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string Rendered block content.
 */
function render_block( array $attributes ): string {
	$course_data = get_post_meta( get_the_ID(), 'teachable_curriculum_data', true );

	if ( empty( $course_data ) || empty( $attributes['sectionId'] ) || ! isset( $course_data[ (int) $attributes['sectionId'] ] ) ) {
		return sprintf(
			'<div %s><p>%s</p></div>',
			get_block_wrapper_attributes(),
			current_user_can( 'edit_post', get_the_ID() ) ? 'Could not get section data.' : ''
		);
	}
	$section_data = $course_data[ (int) $attributes['sectionId'] ];

	ob_start()
	?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<?php
		if ( ! empty( $section_data['name'] ) ) {
			echo '<p><strong>' . esc_html( $section_data['name'] ) . '</strong></p>';
		}

		if ( ! empty( $section_data['lectures'] ) ) {
			echo '<ul class="wp-block-list">';
			foreach ( $section_data['lectures'] as $lecture_id => $lecture_data ) {
				if ( ! empty( $lecture_data['name'] ) ) {
					echo '<li> ' . esc_html( $lecture_data['name'] ) . '</li>';
				}
			}
			echo '</ul>';
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}
