const defaults                          = require( '@wordpress/scripts/config/webpack.config' );
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require( 'path' );

const depMap = {
	'@yith/components': [ 'ywparUI', 'components' ],
	'@yith/date': [ 'ywparUI', 'date' ],
	'@yith/styles': [ 'ywparUI', 'styles' ],
};

const handleMap = {
	'@yith/components': 'ywpar-ui-components',
	'@yith/date': 'ywpar-ui-date',
	'@yith/styles': 'ywpar-ui-styles',
};

const requestToExternal = ( request ) => {
	if ( depMap[ request ] ) {
		return depMap[ request ];
	}
};

const requestToHandle = ( request ) => {
	if ( handleMap[ request ] ) {
		return handleMap[ request ];
	}
};

const config = {
	...defaults,
	optimization: {
        ...defaults.optimization,
        splitChunks: {
            cacheGroups: {
                default: false,
                editor: {
                    chunks: 'all',
                    enforce: true,
                    name: 'editor',
                    test: /editor.s[ac]ss$/i,
                },
                style: {
                    chunks: 'all',
                    enforce: true,
                    name: 'style',
                    test: /style.s[ac]ss$/i,
                },
            },
        },
    },
	entry: {
		blocks: './includes/blocks/src/index.js',
		"frontend_blocks": [
			path.resolve(__dirname, './includes/blocks/src/blocks/ywpar-cart-points-message/frontend.js'),
			path.resolve(__dirname, './includes/blocks/src/blocks/ywpar-rewards-points-message/frontend.js'),
		],
		"checkout_blocks": './includes/blocks/src/blocks/birthdate-checkout/index.js',
		"checkout_blocks_frontend": './includes/blocks/src/blocks/birthdate-checkout/frontend.js',
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets/js/blocks/' ),
		libraryTarget: 'window',
	},
	plugins: [
		...defaults.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin(
			{
				injectPolyfill: true,
				requestToExternal,
				requestToHandle,
			}
		),
	],
};

module.exports = { ...config };
