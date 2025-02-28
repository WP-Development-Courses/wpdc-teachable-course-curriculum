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
	 * @var int The course ID to get data for.
	 */
	protected int $course_id;

	/**
	 * @var string The API key to use for requests.
	 */
	protected string $api_key;

	/**
	 * Course_Fetcher constructor.
	 *
	 * @param int $course_id The course ID to get data for.
	 * @throws Exception If the API key is not found or invalid.
	 */
	public function __construct( int $course_id ) {
		$this->course_id = $course_id;
		$this->api_key = $this->get_api_key();
	}

	/**
	 * Get the Teachable API key.
	 *
	 * This code is adapted from the Teachable plugin. There's no public API for getting the key.
	 *
	 * @return string The API key.
	 * @throws Exception If the key is not found or invalid.
	 */
	protected function get_api_key(): string {
		$settings = get_option( 'teachable_general_settings' );

		if ( empty( $settings['wp_key'] ) ) {
			throw new Exception( 'No API key found in settings.' );
		}

		$key = Teachable\decrypt( $settings['wp_key'] );

		if ( empty( $key ) ) {
			throw new Exception( 'Invalid API key found in settings.' );
		}

		return $key;
	}

	/**
	 * Fetch data from the Teachable API.
	 *
	 * @param string $url The URL to fetch.
	 * @return array The fetched data.
	 * @throws Exception If there is an error fetching the data, or if the data is invalid.
	 */
	protected function fetch( string $url ): array {
		$response = wp_remote_get(
			$url,
			[
				'headers' => [
					'apiKey' => $this->get_api_key(),
				],
				'timeout' => 300,
			]
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error for request to URL ' . $url . ': ' . $response->get_error_message() );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) ) {
			throw new Exception( 'Error decoding JSON data for request to URL ' . $url );
		}

		return $data;
	}

	/**
	 * Fetch course data from the Teachable API.
	 *
	 * @return array The course data.
	 *
	 * @throws Exception If there is an error fetching the data, or if the data is invalid.
	 */
	public function fetch_course_data(): array {
		return $this->fetch( 'https://developers.teachable.com/v1/courses/' . $this->course_id );
	}

	/**
	 * Fetch lecture data from the Teachable API.
	 *
	 * @param int $lecture_id The lecture ID to fetch.
	 *
	 * @return array The lecture data.
	 *
	 * @throws Exception If there is an error fetching the data, or if the data is invalid.
	 */
	public function fetch_lecture_data( int $lecture_id ): array {
		return $this->fetch( 'https://developers.teachable.com/v1/courses/' . $this->course_id . '/lectures/' . $lecture_id );
	}
}
