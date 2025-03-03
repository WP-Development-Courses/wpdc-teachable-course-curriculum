const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { resolve } = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry(),
		'refresh-curriculum': resolve(
			process.cwd(),
			'src/refresh-curriculum',
			'index.js'
		),
	},
};
