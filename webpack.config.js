const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = (env, argv) => {
    const isProduction = argv.mode === 'production';
    
    return {
        entry: {
            'customtube': './src/js/index.js',
            'admin': './src/js/admin/admin.js'
        },
        
        output: {
            path: path.resolve(__dirname, 'dist/js'),
            filename: isProduction ? '[name].min.js' : '[name].js',
            clean: true,
            // Expose CustomTube class globally for backward compatibility
            library: {
                name: 'CustomTube',
                type: 'umd',
                export: 'default'
            },
            globalObject: 'this'
        },
        
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['@babel/preset-env', {
                                    targets: {
                                        browsers: [
                                            '> 1%',
                                            'last 2 versions',
                                            'not dead',
                                            'not ie 11'
                                        ]
                                    },
                                    modules: false, // Let webpack handle modules
                                    useBuiltIns: 'usage',
                                    corejs: 3
                                }]
                            ]
                        }
                    }
                }
            ]
        },
        
        optimization: {
            minimize: isProduction,
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        compress: {
                            drop_console: isProduction, // Remove console.logs in production
                            drop_debugger: true,
                            pure_funcs: isProduction ? ['console.log', 'console.info'] : []
                        },
                        format: {
                            comments: false, // Remove comments in production
                        },
                    },
                    extractComments: false, // Don't create separate license file
                })
            ],
            // Split chunks for better caching (if needed in future)
            splitChunks: {
                chunks: 'all',
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                        minChunks: 2
                    }
                }
            }
        },
        
        devtool: isProduction ? false : 'source-map',
        
        resolve: {
            extensions: ['.js'],
            alias: {
                '@core': path.resolve(__dirname, 'src/js/core'),
                '@components': path.resolve(__dirname, 'src/js/components'),
                '@pages': path.resolve(__dirname, 'src/js/pages'),
                '@utils': path.resolve(__dirname, 'src/js/core/utils.js')
            }
        },
        
        externals: {
            // jQuery is provided by WordPress
            'jquery': 'jQuery',
            '$': 'jQuery'
        },
        
        performance: {
            hints: isProduction ? 'warning' : false,
            maxEntrypointSize: 250000, // 250kb
            maxAssetSize: 250000
        },
        
        stats: {
            colors: true,
            modules: false,
            chunks: false,
            chunkModules: false
        },
        
        watchOptions: {
            ignored: /node_modules/,
            aggregateTimeout: 300,
            poll: 1000
        }
    };
};
