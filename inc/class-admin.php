<?php

/**
 * Admin functionality for Directorist - Seller Verification.
 *
 * @package Directorist_Seller_Verification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Directorist_Seller_Verification_Admin' ) ) {

	/**
	 * Handles Seller Verification fields on the user edit/profile screens.
	 */
	class Directorist_Seller_Verification_Admin {

		/**
		 * Bootstraps hooks.
		 *
		 * @return void
		 */
		public static function init() {
			// Display fields on user profile screens.
			add_action( 'show_user_profile', array( __CLASS__, 'render_seller_verification_fields' ) );
			add_action( 'edit_user_profile', array( __CLASS__, 'render_seller_verification_fields' ) );

			// Save fields on user update.
			add_action( 'personal_options_update', array( __CLASS__, 'save_seller_verification_fields' ) );
			add_action( 'edit_user_profile_update', array( __CLASS__, 'save_seller_verification_fields' ) );

			// Enqueue media uploader scripts.
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_media_scripts' ) );
		}

		/**
		 * Enqueues media uploader scripts on user profile pages.
		 *
		 * @param string $hook_suffix Current admin page hook.
		 *
		 * @return void
		 */
		public static function enqueue_media_scripts( $hook_suffix ) {
			if ( 'profile.php' !== $hook_suffix && 'user-edit.php' !== $hook_suffix ) {
				return;
			}

			wp_enqueue_media();
			wp_enqueue_script(
				'directorist-seller-verification-admin',
				DIRECTORIST_SELLER_VERIFICATION_URI . 'assets/js/admin.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);
		}

		/**
		 * Returns supported document types for verification.
		 *
		 * @return array
		 */
		public static function get_document_types() {
			$types = array(
				''               => __( 'Select document type', 'directorist-seller-verification' ),
				'national_id'    => __( 'National ID', 'directorist-seller-verification' ),
				'passport'       => __( 'Passport', 'directorist-seller-verification' ),
				'driving_license'=> __( 'Driving License', 'directorist-seller-verification' ),
				'residence_permit' => __( 'Residence Permit', 'directorist-seller-verification' ),
				'utility_bill'   => __( 'Utility Bill', 'directorist-seller-verification' ),
				'bank_statement' => __( 'Bank Statement', 'directorist-seller-verification' ),
				'business_license' => __( 'Business License', 'directorist-seller-verification' ),
				'tax_id'         => __( 'Tax ID / VAT Certificate', 'directorist-seller-verification' ),
				'other'          => __( 'Other Government-issued Document', 'directorist-seller-verification' ),
			);

			/**
			 * Filters the list of seller verification document types.
			 *
			 * @param array $types Document types.
			 */
			return apply_filters( 'directorist_seller_verification_document_types', $types );
		}

		/**
		 * Renders Seller Verification fields on the user profile edit screens.
		 *
		 * @param WP_User $user User object.
		 *
		 * @return void
		 */
		public static function render_seller_verification_fields( $user ) {
			if ( ! ( $user instanceof WP_User ) ) {
				$user = get_userdata( $user );
			}

			if ( ! $user ) {
				return;
			}

			if ( ! current_user_can( 'edit_user', $user->ID ) ) {
				return;
			}

			$document_type  = get_user_meta( $user->ID, '_seller_document_type', true );
			$verified_value = get_user_meta( $user->ID, 'verify_seller', true );
			$document_front = get_user_meta( $user->ID, '_seller_document_front', true );
			$document_back  = get_user_meta( $user->ID, '_seller_document_back', true );

			$document_types = self::get_document_types();

			wp_nonce_field( 'directorist_seller_verification_save', 'directorist_seller_verification_nonce' );
			?>
			<h2><?php esc_html_e( 'Seller Verification', 'directorist-seller-verification' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="seller_document_type"><?php esc_html_e( 'Seller document type', 'directorist-seller-verification' ); ?></label>
					</th>
					<td>
						<select name="seller_document_type" id="seller_document_type">
							<?php foreach ( $document_types as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $document_type, $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Select the type of identification document provided by the seller.', 'directorist-seller-verification' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Upload documents', 'directorist-seller-verification' ); ?>
					</th>
					<td>
						<div class="directorist-seller-verification-upload">
							<label for="seller_document_front">
								<?php esc_html_e( 'Front side', 'directorist-seller-verification' ); ?>
							</label>
							<br />
							<input type="hidden" name="seller_document_front" id="seller_document_front" value="<?php echo esc_attr( $document_front ); ?>" />
							<button type="button" class="button directorist-upload-button" data-field="seller_document_front">
								<?php esc_html_e( 'Upload Document', 'directorist-seller-verification' ); ?>
							</button>
							<button type="button" class="button directorist-remove-button<?php echo empty( $document_front ) ? ' directorist-sv-hidden' : ''; ?>" data-field="seller_document_front">
								<?php esc_html_e( 'Remove', 'directorist-seller-verification' ); ?>
							</button>
							<div class="directorist-upload-preview<?php echo empty( $document_front ) ? ' directorist-sv-hidden' : ''; ?>" id="seller_document_front_preview">
								<?php
								if ( ! empty( $document_front ) ) {
									$front_url = wp_get_attachment_url( $document_front );
									if ( $front_url ) {
										$file_type = wp_check_filetype( $front_url );
										$ext       = isset( $file_type['ext'] ) ? strtolower( $file_type['ext'] ) : '';
										if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ), true ) ) {
											echo '<img class="directorist-sv-preview-image" src="' . esc_url( $front_url ) . '" alt="" />';
										} else {
											echo '<p class="directorist-sv-preview-link"><a href="' . esc_url( $front_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Document', 'directorist-seller-verification' ) . '</a></p>';
										}
									}
								}
								?>
							</div>
							<p class="description">
								<?php esc_html_e( 'Upload the front side of the document. Allowed types: JPG, PNG, GIF, PDF.', 'directorist-seller-verification' ); ?>
							</p>
						</div>

						<div class="directorist-seller-verification-upload directorist-sv-upload-block--back">
							<label for="seller_document_back">
								<?php esc_html_e( 'Back side', 'directorist-seller-verification' ); ?>
							</label>
							<br />
							<input type="hidden" name="seller_document_back" id="seller_document_back" value="<?php echo esc_attr( $document_back ); ?>" />
							<button type="button" class="button directorist-upload-button" data-field="seller_document_back">
								<?php esc_html_e( 'Upload Document', 'directorist-seller-verification' ); ?>
							</button>
							<button type="button" class="button directorist-remove-button<?php echo empty( $document_back ) ? ' directorist-sv-hidden' : ''; ?>" data-field="seller_document_back">
								<?php esc_html_e( 'Remove', 'directorist-seller-verification' ); ?>
							</button>
							<div class="directorist-upload-preview<?php echo empty( $document_back ) ? ' directorist-sv-hidden' : ''; ?>" id="seller_document_back_preview">
								<?php
								if ( ! empty( $document_back ) ) {
									$back_url = wp_get_attachment_url( $document_back );
									if ( $back_url ) {
										$file_type = wp_check_filetype( $back_url );
										$ext       = isset( $file_type['ext'] ) ? strtolower( $file_type['ext'] ) : '';
										if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ), true ) ) {
											echo '<img class="directorist-sv-preview-image" src="' . esc_url( $back_url ) . '" alt="" />';
										} else {
											echo '<p class="directorist-sv-preview-link"><a href="' . esc_url( $back_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Document', 'directorist-seller-verification' ) . '</a></p>';
										}
									}
								}
								?>
							</div>
							<p class="description">
								<?php esc_html_e( 'Upload the back side of the document. Allowed types: JPG, PNG, GIF, PDF.', 'directorist-seller-verification' ); ?>
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="verify_seller"><?php esc_html_e( 'Verified', 'directorist-seller-verification' ); ?></label>
					</th>
					<td>
						<label for="verify_seller">
							<input type="checkbox" name="verify_seller" id="verify_seller" value="yes" <?php checked( 'yes', $verified_value ); ?> />
							<?php esc_html_e( 'Vefiry the seller', 'directorist-seller-verification' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Check this box to mark this seller as verified.', 'directorist-seller-verification' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Saves Seller Verification fields from the user profile.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return void
		 */
		public static function save_seller_verification_fields( $user_id ) {
			if ( ! isset( $_POST['directorist_seller_verification_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['directorist_seller_verification_nonce'] ) ), 'directorist_seller_verification_save' ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return;
			}

			// Save document type.
			$document_types = self::get_document_types();
			$document_type  = '';

			if ( isset( $_POST['seller_document_type'] ) ) {
				$document_type = sanitize_text_field( wp_unslash( $_POST['seller_document_type'] ) );
			}

			if ( ! array_key_exists( $document_type, $document_types ) ) {
				$document_type = '';
			}

			update_user_meta( $user_id, '_seller_document_type', $document_type );

			// Save verified flag. Meta-key: verify_seller.
			$verified = isset( $_POST['verify_seller'] ) && 'yes' === $_POST['verify_seller'] ? 'yes' : 'no';
			update_user_meta( $user_id, 'verify_seller', $verified );

			// Save document attachment IDs.
			$document_fields = array(
				'seller_document_front' => '_seller_document_front',
				'seller_document_back'  => '_seller_document_back',
			);

			foreach ( $document_fields as $field_name => $meta_key ) {
				$attachment_id = 0;

				if ( isset( $_POST[ $field_name ] ) && ! empty( $_POST[ $field_name ] ) ) {
					$attachment_id = absint( $_POST[ $field_name ] );

					// Verify that the attachment exists and is a valid document type.
					if ( $attachment_id > 0 ) {
						$attachment = get_post( $attachment_id );

						if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
							$attachment_id = 0;
						} elseif ( ! current_user_can( 'edit_post', $attachment_id ) ) {
							$attachment_id = 0;
						} else {
							$file_url = wp_get_attachment_url( $attachment_id );
							if ( $file_url ) {
								$file_type    = wp_check_filetype( $file_url );
								$allowed_exts = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf' );
								$ext          = isset( $file_type['ext'] ) ? strtolower( $file_type['ext'] ) : '';

								if ( ! in_array( $ext, $allowed_exts, true ) ) {
									$attachment_id = 0;
								}
							} else {
								$attachment_id = 0;
							}
						}
					}
				}

				update_user_meta( $user_id, $meta_key, $attachment_id );
			}
		}
	}

	Directorist_Seller_Verification_Admin::init();
}


