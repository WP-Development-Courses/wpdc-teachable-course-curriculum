import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

export default function Edit( { clientId, attributes, setAttributes } ) {
	const [ postMeta ] = useEntityProp( 'postType', 'course', 'meta' );
	const ids = getCurriculumKeys( postMeta );

	// Arrange the curriculum sections into columns of 3.
	const columnsTemplate = [];
	for ( let i = 0; i < ids.length; i += 3 ) {
		const columns = ids
			.slice( i, i + 3 )
			.map( ( id ) => [
				'core/column',
				{},
				[
					[
						'wpdc/teachable-course-section',
						{ sectionId: parseInt( id, 10 ) },
					],
				],
			] );

		// Fill empty columns if needed, else the layout switched to one or two column displays which looks off.
		while ( columns.length < 3 ) {
			columns.push( [ 'core/column', {}, [] ] );
		}

		columnsTemplate.push( [ 'core/columns', {}, columns ] );
	}

	// Set a locked template for the inner blocks.
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: [ 'core/columns' ],
		template: columnsTemplate,
		templateLock: 'all',
	} );

	return <div { ...innerBlocksProps } />;
}

/**
 * Extract the IDs of the curriculum sections from the post meta.
 *
 * @param meta {Object} The post meta object.
 *
 * @returns {string[]|*[]} Array of curriculum section IDs.
 */
function getCurriculumKeys( meta ) {
	if (
		! meta?.teachable_curriculum_data ||
		typeof meta.teachable_curriculum_data !== 'object'
	) {
		return [];
	}

	return Object.keys( meta.teachable_curriculum_data );
}
