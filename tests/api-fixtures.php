<?php
/**
 * Test the Course_Data class.
 */

declare( strict_types=1 );

namespace WPDC\Teachable_Course_Curriculum\Tests;

/**
 * Get the course endpoint response.
 *
 * @return array The course endpoint response.
 */
function get_course_endpoint_response(): array {
	return [
		"course" => [
			"id"               => 2115932,
			"description"      => null,
			"name"             => "Theme.json Explained",
			"heading"          => "",
			"is_published"     => true,
			"image_url"        => "https://cdn.filestackcontent.com/gsAeLCYDQwyipDvADdis",
			"lecture_sections" => [
				[
					"id"           => 9082340,
					"name"         => "The why and how behind theme.json",
					"is_published" => true,
					"position"     => 1,
					"lectures"     => [
						[ "id" => 47756763, "position" => 1, "is_published" => true ],
						[ "id" => 47655140, "position" => 3, "is_published" => false ],
					]
				],
				[
					"id"           => 9082332,
					"name"         => "Settings and Styles explained",
					"is_published" => false,
					"position"     => 3,
					"lectures"     => []
				],
			],
			"author_bio"       => [
				"profile_image_url" => null,
				"bio"               => null,
				"name"              => "Frank Klein",
				"user_id"           => 27302704,
			],
		],
	];
}
