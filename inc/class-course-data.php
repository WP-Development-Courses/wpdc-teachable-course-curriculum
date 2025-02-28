<?php
/**
 * Fetch course data from the Teachable API, parse it, and store it.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum;

use Exception;

class Course_Data {
	/**
	 * ID of the post for which to handle post data.
	 *
	 * @var int
	 */
	public int $post_id;

	/**
	 * Teachable ID of the course to fetch data for.
	 *
	 * @var int
	 */
	public int $course_id;

	/**
	 * Array of section data.
	 *
	 * @var array
	 */
	public array $sections = [];

	/**
	 * Course fetcher instance to interact with the Teachable API.
	 *
	 * @var Course_Fetcher
	 */
	public Course_Fetcher $course_fetcher;

	/**
	 * Constructor.
	 *
	 * @param int $post_id   Post ID.
	 * @param int $course_id Course ID.
	 */
	public function __construct( int $post_id, int $course_id ) {
		$this->post_id = $post_id;
		$this->course_id = $course_id;
		$this->course_fetcher = new Course_Fetcher( $course_id );
	}

	/**
	 * Refresh the course data.
	 *
	 * @throws Exception If anything goes wrong during the refresh.
	 */
	public function refresh_course_data(): void {
		// First get the course data, which contains the list of sections and lectures.
		$course_data = $this->course_fetcher->fetch_course_data();

		// Second extract the sections from the course data.
		$this->extract_sections_from_course( $course_data );

		// Third get the lecture data for each section.
		$this->fetch_course_lectures_data();

		// Fourth store the data in post meta.
		$this->store_course_data();
	}

	/**
	 * Get the stored course data.
	 *
	 * @return array Course data.
	 */
	public function get_stored_course_data(): array {
		return (array) get_post_meta( $this->post_id, 'teachable_curriculum_data', true );
	}

	/**
	 * Store the course data in post meta.
	 */
	public function store_course_data(): void {
		update_post_meta( $this->post_id, 'teachable_curriculum_data', $this->sections );
	}

	/**
	 * Fetch the data for all lectures in all sections of the course.
	 *
	 * @throws Exception If anything goes wrong during the lecture fetching.
	 */
	public function fetch_course_lectures_data(): void {
		foreach( $this->sections as $section_id => $section_data ) {
			$lectures = $this->fetch_lectures_for_section( $section_id );

			$this->add_section_data( $section_id, [ 'lectures' => $lectures ] );
		}
	}

	/**
	 * Fetch the data for all lectures in a section.
	 *
	 * @param int $section_id Section ID.
	 *
	 * @return array Lectures data.
	 * @throws Exception If the section is not found.
	 */
	public function fetch_lectures_for_section( int $section_id ): array {
		$section_data = $this->get_section( $section_id );
		$section_lectures = [];

		foreach( $section_data['lectures'] as $lecture_id => $lecture_data ) {
			$raw_data = $this->course_fetcher->fetch_lecture_data( $lecture_id );

			$section_lectures[ $lecture_id ] = $this->extract_lecture_data( $raw_data );
		}

		return $section_lectures;
	}

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

	public function extract_lecture_data( array $lecture ): array {
		return [
			'name' => $lecture['lecture']['name'],
		];
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

	/**
	 * Get a section by ID.
	 *
	 * @return array Section data.
	 * @throws Exception If the section is not found.
	 */
	public function get_section( int $section_id): array {
		if ( ! isset( $this->sections[ $section_id ] ) ) {
			throw new Exception( 'Section with id ' . $section_id . ' not found in course data.' );
		}

		return $this->sections[ $section_id ];
	}
}
