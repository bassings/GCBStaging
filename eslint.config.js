// ESLint flat config for WordPress development
export default [
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'wordpress/wp-admin/**',
      'wordpress/wp-includes/**',
      '**/node_modules/**',
      '**/vendor/**',
      // Exclude third-party plugins
      'wordpress/wp-content/plugins/advanced-custom-fields-pro/**',
      'wordpress/wp-content/plugins/akismet/**',
      'wordpress/wp-content/plugins/amp/**',
      'wordpress/wp-content/plugins/classic-editor/**',
      'wordpress/wp-content/plugins/coblocks/**',
      'wordpress/wp-content/plugins/code-snippets/**',
      'wordpress/wp-content/plugins/crowdsignal-forms/**',
      'wordpress/wp-content/plugins/duplicate-page/**',
      'wordpress/wp-content/plugins/fusion-builder/**',
      'wordpress/wp-content/plugins/fusion-core/**',
      'wordpress/wp-content/plugins/fusion-white-label-branding/**',
      'wordpress/wp-content/plugins/gutenberg/**',
      'wordpress/wp-content/plugins/header-footer/**',
      'wordpress/wp-content/plugins/health-check/**',
      'wordpress/wp-content/plugins/hello.php',
      'wordpress/wp-content/plugins/insert-headers-and-footers/**',
      'wordpress/wp-content/plugins/instagram-feed/**',
      'wordpress/wp-content/plugins/jetpack/**',
      'wordpress/wp-content/plugins/LayerSlider/**',
      'wordpress/wp-content/plugins/layout-grid/**',
      'wordpress/wp-content/plugins/onesignal-free-web-push-notifications/**',
      'wordpress/wp-content/plugins/page-optimize/**',
      'wordpress/wp-content/plugins/polldaddy/**',
      'wordpress/wp-content/plugins/popup-maker/**',
      'wordpress/wp-content/plugins/pwa/**',
      'wordpress/wp-content/plugins/qc-simple-link-directory/**',
      'wordpress/wp-content/plugins/revslider/**',
      'wordpress/wp-content/plugins/rss-importer/**',
      'wordpress/wp-content/plugins/snapshot/**',
      'wordpress/wp-content/plugins/taxonomy-terms-order/**',
      'wordpress/wp-content/plugins/wp-category-permalink/**',
      'wordpress/wp-content/plugins/wp-smush-pro/**',
      'wordpress/wp-content/plugins/wpmu-dev-seo/**',
      'wordpress/wp-content/plugins/wpmudev-updates/**',
      // Exclude third-party themes
      'wordpress/wp-content/themes/twentytwenty*/**',
      'wordpress/wp-content/themes/Avada/**',
      'wordpress/wp-content/themes/goodz-magazine/**',
      // Exclude uploads directory (generated/cached files)
      'wordpress/wp-content/uploads/**'
    ]
  },
  {
    files: ['**/*.js', '**/*.mjs'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        // Browser globals
        window: 'readonly',
        document: 'readonly',
        navigator: 'readonly',
        console: 'readonly',
        // WordPress globals
        wp: 'readonly',
        jQuery: 'readonly',
        $: 'readonly',
        // Node.js globals for scripts
        process: 'readonly',
        __dirname: 'readonly',
        __filename: 'readonly',
        require: 'readonly',
        module: 'readonly',
        exports: 'readonly'
      }
    },
    rules: {
      // Basic rules - adjust as needed
      'no-console': 'off', // Allow console in WordPress
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      'semi': ['error', 'always'],
      'quotes': ['error', 'single', { avoidEscape: true }]
    }
  }
];
