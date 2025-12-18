<?php

/** 
 * @package  Directorist - Seller Verification
 */

/**
 * Plugin Name:       Directorist - Seller Verification
 * Plugin URI:        https://wpxplore.com/tools/directorist-seller-verification
 * Description:       Add seller verification functionality to your Directorist listings. Verify and display verified seller badges to build trust with your users.
 * Version:           2.0.0
 * Requires at least: 5.2
 * Author:            wpXplore
 * Author URI:        https://wpxplore.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       directorist-seller-verification
 * Domain Path:       /languages
 */

/* This is an extension for Directorist plugin. It adds seller verification functionality to help build trust with verified sellers.*/

/**
 * If this file is called directly, abort!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Directorist_Seller_Verification' ) ) {

	/**
	 * Main plugin class.
	 *
	 * @package Directorist_Seller_Verification
	 */
	final class Directorist_Seller_Verification {

		/**
		 * Plugin instance.
		 *
		 * @var Directorist_Seller_Verification
		 */
		private static $instance;

		/**
		 * Get plugin instance.
		 *
		 * @return Directorist_Seller_Verification
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Directorist_Seller_Verification ) ) {
				self::$instance = new Directorist_Seller_Verification();
				self::$instance->init();
			}
			return self::$instance;
		}

		/**
		 * Initialize plugin.
		 *
		 * @return void
		 */
		public function init() {
			$this->define_constants();
			$this->includes();
			$this->enqueues();
			$this->hooks();
		}

		/**
		 * Define plugin constants.
		 *
		 * @return void
		 */
		private function define_constants() {
			if ( ! defined( 'DIRECTORIST_SELLER_VERIFICATION_URI' ) ) {
				define( 'DIRECTORIST_SELLER_VERIFICATION_URI', plugin_dir_url( __FILE__ ) );
			}

			if ( ! defined( 'DIRECTORIST_SELLER_VERIFICATION_DIR' ) ) {
				define( 'DIRECTORIST_SELLER_VERIFICATION_DIR', plugin_dir_path( __FILE__ ) );
			}
		}

		/**
		 * Include required files.
		 *
		 * @return void
		 */
		private function includes() {
			include_once DIRECTORIST_SELLER_VERIFICATION_DIR . '/inc/functions.php';
			include_once DIRECTORIST_SELLER_VERIFICATION_DIR . '/inc/class-admin.php';
			include_once DIRECTORIST_SELLER_VERIFICATION_DIR . '/inc/class-dashboard.php';
		}

		/**
		 * Register enqueue hooks.
		 *
		 * @return void
		 */
		private function enqueues() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Register action and filter hooks.
		 *
		 * @return void
		 */
		private function hooks() {
			add_filter( 'directorist_template', array( $this, 'directorist_template' ), 10, 2 );
		}

		/**
		 * Enqueue JavaScript files.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_enqueue_script(
				'directorist-seller-verification-script',
				DIRECTORIST_SELLER_VERIFICATION_URI . 'assets/js/main.js',
				array( 'jquery' ),
				'2.0.0',
				true
			);
		}

		/**
		 * Enqueue CSS files.
		 *
		 * @return void
		 */
		public function enqueue_styles() {
			wp_enqueue_style(
				'directorist-seller-verification-style',
				DIRECTORIST_SELLER_VERIFICATION_URI . 'assets/css/dashboard.css',
				array(),
				'2.0.0'
			);
		}

/**
         * Template Exists
         */
        public function template_exists($template_file)
        {
            $file = DIRECTORIST_SELLER_VERIFICATION_DIR . '/templates/' . $template_file . '.php';

            if (file_exists($file)) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Get Template
         */
        public function get_template($template_file, $args = array())
        {
            if (is_array($args)) {
                extract($args);
            }
            $data = $args;

            if (isset($args['form'])) $listing_form = $args['form'];

            $file = DIRECTORIST_SELLER_VERIFICATION_DIR . '/templates/' . $template_file . '.php';

            if ($this->template_exists($template_file)) {
                include $file;
            }
        }

        /**
         * Directorist Template
         */
        public function directorist_template($template, $field_data)
        {
            if ($this->template_exists($template)) $template = $this->get_template($template, $field_data);
            return $template;
        }
	}

	/**
	 * Check if Directorist plugin is active.
	 *
	 * @param string $plugin Plugin basename.
	 * @return bool
	 */
	if ( ! function_exists( 'directorist_sv_is_plugin_active' ) ) {
		function directorist_sv_is_plugin_active( $plugin ) {
			if ( function_exists( 'directorist_is_plugin_active' ) ) {
				return directorist_is_plugin_active( $plugin );
			}
			return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || directorist_sv_is_plugin_active_for_network( $plugin );
		}
	}

	/**
	 * Check if plugin is active for network (multisite).
	 *
	 * @param string $plugin Plugin basename.
	 * @return bool
	 */
	if ( ! function_exists( 'directorist_sv_is_plugin_active_for_network' ) ) {
		function directorist_sv_is_plugin_active_for_network( $plugin ) {
			if ( ! is_multisite() ) {
				return false;
			}

			$plugins = get_site_option( 'active_sitewide_plugins' );
			return isset( $plugins[ $plugin ] );
		}
	}

	/**
	 * Get plugin instance.
	 *
	 * @return Directorist_Seller_Verification
	 */
	function directorist_seller_verification() {
		return Directorist_Seller_Verification::instance();
	}

	// Initialize plugin if Directorist is active.
	if ( directorist_sv_is_plugin_active( 'directorist/directorist-base.php' ) ) {
		directorist_seller_verification();
	}
}