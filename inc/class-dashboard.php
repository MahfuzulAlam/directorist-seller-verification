<?php

/**
 * Dashboard functionality for Directorist - Seller Verification.
 *
 * @package Directorist_Seller_Verification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Directorist_Seller_Verification_Dashboard' ) ) {

	/**
	 * Handles Seller Verification fields on the user dashboard.
	 */
	class Directorist_Seller_Verification_Dashboard {

		/**
		 * Bootstraps hooks.
		 *
		 * @return void
		 */
		public static function init() {

			add_filter( 'directorist_dashboard_tabs', array( __CLASS__, 'directorist_dashboard_tabs' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_dashboard_assets' ) );
			add_action( 'wp_ajax_directorist_sv_save_documents', array( __CLASS__, 'ajax_save_documents' ) );
			add_action( 'wp_ajax_nopriv_directorist_sv_save_documents', array( __CLASS__, 'ajax_save_documents_nopriv' ) );
		}

		/**
		 * Adds Seller Verification tab to the Directorist dashboard.
		 *
		 * @param array $tabs Existing dashboard tabs.
		 *
		 * @return array
		 */
		public static function directorist_dashboard_tabs( $tabs ) {
			if ( ! is_user_logged_in() ) {
				return $tabs;
			}

			$tabs['directorist_documents'] = array(
				'title'   => __( 'My Documents', 'directorist' ),
				'content' => self::get_documents_tab_content(),
				'icon'    => 'las la-file-alt',
			);

			return $tabs;
		}

		/**
		 * Gets the HTML content for the Seller Verification documents tab.
		 *
		 * @return string
		 */
		protected static function get_documents_tab_content() {
			ob_start();

			$file = trailingslashit( DIRECTORIST_SELLER_VERIFICATION_DIR ) . 'templates/tab-documents.php';

			if ( file_exists( $file ) ) {
				// Make sure we are in a safe scope.
				include $file;
			}

			return ob_get_clean();
		}

		/**
		 * Checks whether the current request is on the Directorist user dashboard page.
		 *
		 * @return bool
		 */
		protected static function is_user_dashboard_page() {
			if ( is_admin() ) {
				return false;
			}

			// Prefer checking against the configured Directorist dashboard page.
			if ( function_exists( 'get_directorist_option' ) ) {
				$dashboard_page_id = (int) get_directorist_option( 'user_dashboard' );
				if ( $dashboard_page_id > 0 && is_page( $dashboard_page_id ) ) {
					return true;
				}
			}

			// Fallback: detect shortcode on the current post.
			$post = get_post();
			if ( $post && has_shortcode( $post->post_content, 'directorist_user_dashboard' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Enqueues scripts needed for the dashboard document uploader.
		 *
		 * @return void
		 */
		public static function enqueue_dashboard_assets() {
			if ( ! is_user_logged_in() || ! self::is_user_dashboard_page() ) {
				return;
			}

			// Load WP media library on the frontend for uploader modal.
			wp_enqueue_media();

			wp_enqueue_script(
				'directorist-seller-verification-dashboard',
				DIRECTORIST_SELLER_VERIFICATION_URI . 'assets/js/dashboard.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);

			wp_localize_script(
				'directorist-seller-verification-dashboard',
				'DirectoristSellerVerification',
				array(
					'ajaxurl'      => admin_url( 'admin-ajax.php' ),
					'nonce'        => wp_create_nonce( 'directorist_sv_dashboard_ajax' ),
					'title'        => __( 'Select Document', 'directorist-seller-verification' ),
					'button_text'  => __( 'Use this document', 'directorist-seller-verification' ),
					'view_text'    => __( 'View Document', 'directorist-seller-verification' ),
					'saving_text'  => __( 'Saving...', 'directorist-seller-verification' ),
					'saved_text'   => __( 'Saved successfully.', 'directorist-seller-verification' ),
					'error_text'   => __( 'Something went wrong. Please try again.', 'directorist-seller-verification' ),
				)
			);
		}

		/**
		 * AJAX: Not logged in handler.
		 *
		 * @return void
		 */
		public static function ajax_save_documents_nopriv() {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in.', 'directorist-seller-verification' ),
				),
				401
			);
		}

		/**
		 * AJAX: Saves document fields from the dashboard tab.
		 *
		 * @return void
		 */
		public static function ajax_save_documents() {
			if ( ! is_user_logged_in() ) {
				wp_send_json_error(
					array(
						'message' => __( 'You must be logged in.', 'directorist-seller-verification' ),
					),
					401
				);
			}

			check_ajax_referer( 'directorist_sv_dashboard_ajax', 'nonce' );

			$user_id = get_current_user_id();

			// Save document type.
			$document_types = array();
			if ( class_exists( 'Directorist_Seller_Verification_Admin' ) && method_exists( 'Directorist_Seller_Verification_Admin', 'get_document_types' ) ) {
				$document_types = Directorist_Seller_Verification_Admin::get_document_types();
			}

			$document_type = '';
			if ( isset( $_POST['seller_document_type'] ) ) {
				$document_type = sanitize_text_field( wp_unslash( $_POST['seller_document_type'] ) );
			}

			if ( ! empty( $document_types ) && ! array_key_exists( $document_type, $document_types ) ) {
				$document_type = '';
			}

			update_user_meta( $user_id, '_seller_document_type', $document_type );

			// Save document attachment IDs.
			$document_fields = array(
				'seller_document_front' => '_seller_document_front',
				'seller_document_back'  => '_seller_document_back',
			);

			foreach ( $document_fields as $field_name => $meta_key ) {
				$attachment_id = 0;

				if ( isset( $_POST[ $field_name ] ) && '' !== $_POST[ $field_name ] ) {
					$attachment_id = absint( wp_unslash( $_POST[ $field_name ] ) );
				}

				if ( $attachment_id > 0 ) {
                    update_user_meta( $user_id, $meta_key, $attachment_id );
				}
			}

			wp_send_json_success(
				array(
					'message' => __( 'Saved successfully.', 'directorist-seller-verification' ),
				)
			);
		}
	}

	Directorist_Seller_Verification_Dashboard::init();

}