<?php
/**
 * Test the Course_Data class.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum\Tests;

use WPDC\Teachable_Course_Curriculum\Course_Data;
use WP_UnitTestCase;

class Test_Course_Data extends WP_UnitTestCase {
	public Course_Data $course_data;

	/**
	 * Set up the tests.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->course_data = new Course_Data( 123, 2115932 );
	}

	/**
	 * Verify the extraction of section data from the course endpoint response.
	 */
	public function test_extract_sections_from_course(): void {
		$this->course_data->extract_sections_from_course( get_course_endpoint_response() );

		$this->assertEquals(
			[
				9082340 => [
					'name' => 'The why and how behind theme.json',
					'lectures' => [
						47756763 => [],
					],
				],
			],
			$this->course_data->get_sections(),
		);
	}

	/**
	 * Verify the extraction of section data from the course endpoint response.
	 */
	public function test_extract_lecture_data(): void {
		$this->course_data->extract_sections_from_course( get_course_endpoint_response() );

		$this->assertEquals(
			[
				'name' => "What you'll learn in this section",
			],
			$this->course_data->extract_lecture_data( get_lecture_endpoint_response() ),
		);
	}

	/**
	 * Verify the retrieval of a single section.
	 */
	public function test_get_section(): void {
		$this->course_data->extract_sections_from_course( get_course_endpoint_response() );

		$this->assertEquals(
			[
				'name' => 'The why and how behind theme.json',
				'lectures' => [
					47756763 => [],
				],
			],
			$this->course_data->get_section( 9082340 ),
		);
	}
}
