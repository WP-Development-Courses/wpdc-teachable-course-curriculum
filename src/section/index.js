import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

registerBlockType( 'wpdc/teachable-course-section', {
	title: 'Teachable Course Section',
	edit: Edit,
	save: () => {
		return null;
	},
} );
