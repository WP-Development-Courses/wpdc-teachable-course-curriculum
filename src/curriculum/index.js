import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import Save from './save';

registerBlockType( 'wpdc/teachable-course-curriculum', {
	title: 'Teachable Course Curriculum',
	edit: Edit,
	save: Save,
} );
