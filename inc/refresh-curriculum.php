<?php
/**
 * Implement an interface that allows to refresh the cached curriculum data from the Teachable API.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum\Refresh_Curriculum;

use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDC\Teachable_Course_Curriculum\Course_Data;
use const WPDC\Teachable_Course_Curriculum\BUILD_DIR;

/**
 * Add hooks.
 */
function bootstrap(): void {
	add_action('admin_enqueue_scripts', __NAMESPACE__  . '\\enqueue_teachable_metabox_script');
	add_action('init', __NAMESPACE__  . '\\register_teachable_course_meta');
	add_action('rest_api_init', __NAMESPACE__ . '\\register_refresh_curriculum_endpoint');
}

/**
 * Register the Teachable course meta fields.
 */
function register_teachable_course_meta(): void {
	register_post_meta(
		'course',
		'wpdc_teachable_course_id',
		[
			'show_in_rest' => true,
			'single' => true,
			'type' => 'integer',
			'sanitize_callback' => 'absint',
		]
	);

	register_post_meta(
		'course',
		'teachable_curriculum_data',
		[
			'type'         => 'object',
			'description'  => 'Curriculum data for the post',
			'single'       => true,
			'default'   => [],
			'show_in_rest' => [
				'schema' => [
					'type'                 => 'object',
					'patternProperties'    => [
						'^[0-9]+$' => [
							'type'       => 'object',
							'properties' => [
								'name'     => [ 'type' => 'string' ],
								'lectures' => [
									'type'              => 'object',
									'patternProperties' => [
										'^[0-9]+$' => [
											'type'       => 'object',
											'properties' => [
												'name' => [ 'type' => 'string' ],
											],
											'required' => [ 'name' ],
										],
									],
								],
							],
							'required' => [ 'name', 'lectures' ],
						],
					],
				],
			],
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_curriculum_data',
		]
	);
}

/**
 * Sanitize the curriculum data.
 *
 * @param mixed $meta_value Unsafe curriculum data.
 *
 * @return array Sanitized curriculum data.
 */
function sanitize_teachable_curriculum_data( mixed $meta_value ): array {
	if ( ! is_array( $meta_value ) ) {
		return [];
	}

	$sanitized_data = [];

	foreach ( $meta_value as $section_id => $section_data ) {
		if ( ! is_numeric( $section_id ) || ! is_array( $section_data ) ) {
			continue;
		}

		$sanitized_section = [];

		// Sanitize section name, skip section if no name set.
		if ( ! isset( $section_data['name'] ) || ! is_string( $section_data['name'] ) ) {
			continue;
		}

		$sanitized_section['name'] = sanitize_text_field( $section_data['name'] );

		// Sanitize lectures, skipping any invalid lectures.
		$sanitized_section['lectures'] = [];
		if ( isset( $section_data['lectures'] ) && is_array( $section_data['lectures'] ) ) {
			foreach ( $section_data['lectures'] as $lecture_id => $lecture_data ) {
				if ( ! is_numeric( $lecture_id ) || ! is_array( $lecture_data ) ) {
					continue;
				}

				if ( ! isset( $lecture_data['name'] ) || ! is_string( $lecture_data['name'] ) ) {
					continue;
				}

				$sanitized_section['lectures'][ $lecture_id ] = [ 'name' => $lecture_data['name'] ];
			}
		}

		$sanitized_data[ $section_id ] = $sanitized_section;
	}

	return $sanitized_data;
}

/**
 * Enqueue the JavaScript for the metabox giving access to the Teachable integration in the block editor.
 */
function enqueue_teachable_metabox_script( string $hook ): void {
	if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen->post_type !== 'course' ) {
		return;
	}

	$args = require BUILD_DIR . '/refresh-curriculum.asset.php';

	wp_enqueue_script(
		'wpdc-teachable-integration',
		plugins_url('build/refresh-curriculum.js', dirname( __FILE__ ) ),
		$args['dependencies'],
		$args['version'],
		true
	);

	wp_localize_script(
		'wpdc-teachable-integration',
		'teachableIntegration',
		[
			'nonce' => wp_create_nonce( 'wpdc_refresh_curriculum' )
		]
	);
}

/**
 * Register an endpoint to handle the refresh action.
 */
function register_refresh_curriculum_endpoint(): bool {
	return register_rest_route(
		'wpdc-teachable/v1',
		'/refresh-curriculum/(?P<post_id>\d+)',
		[
			'methods' => 'POST',
			'callback' => __NAMESPACE__ . '\\refresh_curriculum',
			'permission_callback' => function( WP_REST_Request $request ) {
				$post_id = (int) $request->get_param( 'post_id' );

				if ( ! $post_id || get_post_status( $post_id ) === false ) {
					return new WP_Error( 'rest_invalid', __( 'Invalid post ID.', 'text-domain' ), [ 'status' => 400 ] );
				}

				return current_user_can( 'edit_post', $post_id );
			},

		]
	);
}

/**
 * API endpoint callback to handle the refresh action.
 *
 * @param WP_REST_Request $request The request object.
 *
 * @return WP_Error|WP_REST_Response
 */
function refresh_curriculum( WP_REST_Request $request ): WP_Error|WP_REST_Response {
	$course_data = new Course_Data(
		(int) $request->get_param( 'post_id' ),
		(int) get_post_meta( (int) $request->get_param('post_id'), 'wpdc_teachable_course_id', true )
	);

	try {
		$course_data->refresh_course_data();

	} catch ( Exception $e ) {
		return new WP_Error(
			'curriculum_refresh_failed',
			$e->getMessage(),
			[
				'status' => 500,
			]
		);
	}

	return rest_ensure_response( [
		'success' => true,
	] );
}
