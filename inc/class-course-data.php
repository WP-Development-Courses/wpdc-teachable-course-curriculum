<?php
/**
 * Fetch course data from the Teachable API, parse it, and store it.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum;

use Exception;

class Course_Data {
	protected array $sections = [];

	/**
	 * Extract sections from course data.
	 *
	 * @param array $course_data Raw course endpoint data from Teachable.
	 *
	 * @throws Exception If the course data does not contain any lecture sections.
	 */
	public function extract_sections_from_course( array $course_data ): void {
		if ( empty( $course_data['course']['lecture_sections'] ) || ! is_array( $course_data['course']['lecture_sections'] ) ) {
			throw new Exception( 'No lectures in course data.' );
		}

		foreach( $course_data['course']['lecture_sections'] as $lecture_section ) {
			if ( ! isset( $lecture_section['is_published'] ) || ! $lecture_section['is_published'] ) {
				continue;
			}

			$lectures = [];

			foreach( $lecture_section['lectures'] as $lecture ) {
				if ( ! $lecture['is_published'] ) {
					continue;
				}

				$lectures[ $lecture['id'] ] = [];
			}

			$this->add_section_data(
				$lecture_section['id'],
				[
					'name' => $lecture_section['name'],
					'lectures' => $lectures,
				]
			);
		}
	}

	/**
	 * Add a section.
	 *
	 * @param int $section_id Section ID.
	 */
	public function add_section( int $section_id ): void {
		$this->sections[ $section_id ] = [];
	}

	/**
	 * Add data to a section.
	 *
	 * @param int   $section_id Section ID.
	 * @param array $data       Data to add.
	 */
	public function add_section_data( int $section_id, array $data ): void {
		if ( ! isset( $this->sections[ $section_id ] ) ) {
			$this->add_section( $section_id );
		}

		$existing_data = $this->sections[ $section_id ];

		$this->sections[ $section_id ] = array_merge( $existing_data, $data );
	}

	/**
	 * Get the sections.
	 *
	 * @return array Section data.
	 */
	public function get_sections(): array {
		return $this->sections;
	}
}
