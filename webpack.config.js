const path = require('path');
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const scriptsLocation = './assets/scripts/js/';
const stylesLocation = './assets/styles/scss/';

const findModules = (folderPath, modules) => {
	const jsFiles = [];

	for (const m of modules) {
		if (!m.includes('.php')) {
			if (fs.existsSync(path.resolve(__dirname, folderPath + '/' + m))) {
				const allFiles = fs.readdirSync(folderPath + '/' + m);
				for (const fileName of allFiles) {
					jsFiles.push(
						path.resolve(__dirname, folderPath + '/' + m + '/' + fileName)
					);
				}
			}
		}
	}

	return jsFiles;
}

const jsFiles = [
	path.resolve(__dirname, scriptsLocation + 'index.js'),
	...findModules('./src/js/Core', fs.readdirSync('./src/js/Core')),
];

const adminJsFiles = [
	path.resolve(__dirname, scriptsLocation + 'admin.js'),
	...findModules('./src/js/Admin', fs.readdirSync( './src/js/Admin' )),
]

const entries = {
	// Scripts.
	custom: jsFiles,
	admin: adminJsFiles,
	// Styles.
	styles: [path.resolve(__dirname, stylesLocation + 'main.scss')],
	'styles-admin': [path.resolve(__dirname, stylesLocation + 'admin.scss')],
};

module.exports = {
	entry: entries,
	resolve: {
		alias: {
			Modules: path.resolve(__dirname, './assets/js/modules'),
		},
	},
	output: {
		path: path.resolve(__dirname, './assets/build/'),
		filename: '[name].js',
	},
	optimization: {
		minimize: true,
		// splitChunks: {
		// 	minChunks: Infinity,
		// 	chunks: 'all',
		// },
	},
	module: {
		rules: [
			{
				test: /\.scss$/,
				exclude: '/node-modules/',
				use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
			},
			{
				test: '/\.svg/',
				use: {
					loader: 'svg-url-loader',
					options: {},
				}
			}
		],
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: '[name].css',
		}),
	],
};
