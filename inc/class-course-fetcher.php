<?php
/**
 * Fetch and parse course data from the Teachable API.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum;

use Exception;
use Teachable;

class Course_Fetcher {
	/**
	 * @var string The API key to use for requests.
	 */
	protected string $api_key;

	/**
	 * Get the Teachable API key.
	 *
	 * This code is adapted from the Teachable plugin. There's no public API for getting the key.
	 *
	 * @return string The API key.
	 * @throws Exception If the key is not found or invalid.
	 */
	protected function get_api_key(): string {
		if ( $this->api_key ) {
			return $this->api_key;
		}

		$settings = get_option( 'teachable_general_settings' );

		if ( empty( $settings['wp_key'] ) ) {
			throw new Exception( 'No API key found in settings.' );
		}

		$key = Teachable\decrypt( $settings['wp_key'] );

		if ( empty( $key ) ) {
			throw new Exception( 'Invalid API key found in settings.' );
		}

		$this->api_key = $key;

		return $this->api_key;
	}
}
