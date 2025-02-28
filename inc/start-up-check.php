<?php
/**
 * Before starting the plugin, ensure that we got a Teachable API key.
 *
 * Without it, all the rest won't work.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum\Start_Up_Check;

/**
 * Check if the Teachable plugin is configured with an API key.
 *
 * @return bool True if the API key is set, false otherwise.
 */
function has_teachable_wp_key(): bool {
	$settings = get_option('teachable_general_settings');

	if ( empty( $settings['wp_key'] ) ) {
		add_action('admin_init', __NAMESPACE__ . '\\display_missing_wp_key_admin_notice');

		return false;
	}

	return true;
}

/**
 * Display an admin notice if the Teachable API key is missing.
 */
function display_missing_wp_key_admin_notice(): void {
	wp_admin_notice(
		'Teachable API key is missing. Please add it in the settings.',
		[
			'type' => 'error',
		]
	);
}
