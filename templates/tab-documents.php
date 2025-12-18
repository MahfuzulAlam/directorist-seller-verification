<?php

/**
 * Dashboard tab: Seller Documents.
 *
 * Displays the seller verification document type and uploaded documents
 * connected to the user profile fields created in this plugin.
 *
 * @package Directorist_Seller_Verification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();

if ( ! $user_id ) {
	return;
}

// Get stored meta values.
$document_type_key = get_user_meta( $user_id, '_seller_document_type', true );
$document_front_id = (int) get_user_meta( $user_id, '_seller_document_front', true );
$document_back_id  = (int) get_user_meta( $user_id, '_seller_document_back', true );
$verified = get_user_meta( $user_id, 'verify_seller', true );
$is_verified = ( 'yes' === $verified );

// Document type options (must match admin side options).
$document_types = array();
if ( class_exists( 'Directorist_Seller_Verification_Admin' ) && method_exists( 'Directorist_Seller_Verification_Admin', 'get_document_types' ) ) {
	$document_types = Directorist_Seller_Verification_Admin::get_document_types();
}

if ( empty( $document_types ) ) {
	$document_types = array(
		''                 => __( 'Select document type', 'directorist-seller-verification' ),
		'national_id'      => __( 'National ID', 'directorist-seller-verification' ),
		'passport'         => __( 'Passport', 'directorist-seller-verification' ),
		'driving_license'  => __( 'Driving License', 'directorist-seller-verification' ),
		'residence_permit' => __( 'Residence Permit', 'directorist-seller-verification' ),
		'utility_bill'     => __( 'Utility Bill', 'directorist-seller-verification' ),
		'bank_statement'   => __( 'Bank Statement', 'directorist-seller-verification' ),
		'business_license' => __( 'Business License', 'directorist-seller-verification' ),
		'tax_id'           => __( 'Tax ID / VAT Certificate', 'directorist-seller-verification' ),
		'other'            => __( 'Other Government-issued Document', 'directorist-seller-verification' ),
	);
}

$document_type_label = isset( $document_types[ $document_type_key ] ) ? $document_types[ $document_type_key ] : $document_types[''];
?>

<div class="directorist-dashboard-content directorist-seller-verification-documents">
	<h3><?php esc_html_e( 'Seller Documents', 'directorist-seller-verification' ); ?></h3>

	<form method="post" class="directorist-seller-verification-form" autocomplete="off">

		<div class="directorist-form-group">
			<label for="seller_document_type"><strong><?php esc_html_e( 'Document Type', 'directorist-seller-verification' ); ?></strong></label>
			<br />
			<select name="seller_document_type" id="seller_document_type">
				<?php foreach ( $document_types as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $document_type_key, $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="directorist-form-group">
			<label><strong><?php esc_html_e( 'Upload Document', 'directorist-seller-verification' ); ?></strong></label>

			<div class="directorist-sv-upload-block directorist-sv-upload-block--front">
				<label for="seller_document_front"><?php esc_html_e( 'Front Side', 'directorist-seller-verification' ); ?></label>
				<br />
				<input type="hidden" name="seller_document_front" id="seller_document_front" value="<?php echo esc_attr( $document_front_id ); ?>" />
				<button type="button" class="directorist-sv-upload directorist-btn directorist-btn-sm" data-field="seller_document_front">
					<?php esc_html_e( 'Upload / Select', 'directorist-seller-verification' ); ?>
				</button>
				<button type="button" class="directorist-sv-remove directorist-btn directorist-btn-sm<?php echo empty( $document_front_id ) ? ' directorist-sv-hidden' : ''; ?>" data-field="seller_document_front">
					<?php esc_html_e( 'Remove', 'directorist-seller-verification' ); ?>
				</button>
				<div class="directorist-sv-preview<?php echo empty( $document_front_id ) ? ' directorist-sv-hidden' : ''; ?>" id="seller_document_front_preview">
					<?php
					if ( ! empty( $document_front_id ) ) {
						$file_url = wp_get_attachment_url( $document_front_id );
						if ( $file_url ) {
							$file_type = wp_check_filetype( $file_url );
							$ext       = isset( $file_type['ext'] ) ? strtolower( $file_type['ext'] ) : '';
							if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ), true ) ) {
								echo '<img class="directorist-sv-preview-image" src="' . esc_url( $file_url ) . '" alt="" />';
							} else {
								echo '<p class="directorist-sv-preview-link"><a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Document', 'directorist-seller-verification' ) . '</a></p>';
							}
						}
					}
					?>
				</div>
			</div>

			<div class="directorist-sv-upload-block directorist-sv-upload-block--back">
				<label for="seller_document_back"><?php esc_html_e( 'Back Side', 'directorist-seller-verification' ); ?></label>
				<br />
				<input type="hidden" name="seller_document_back" id="seller_document_back" value="<?php echo esc_attr( $document_back_id ); ?>" />
				<button type="button" class="directorist-sv-upload directorist-btn directorist-btn-sm" data-field="seller_document_back">
					<?php esc_html_e( 'Upload / Select', 'directorist-seller-verification' ); ?>
				</button>
				<button type="button" class="directorist-sv-remove directorist-btn directorist-btn-sm<?php echo empty( $document_back_id ) ? ' directorist-sv-hidden' : ''; ?>" data-field="seller_document_back">
					<?php esc_html_e( 'Remove', 'directorist-seller-verification' ); ?>
				</button>
				<div class="directorist-sv-preview<?php echo empty( $document_back_id ) ? ' directorist-sv-hidden' : ''; ?>" id="seller_document_back_preview">
					<?php
					if ( ! empty( $document_back_id ) ) {
						$file_url = wp_get_attachment_url( $document_back_id );
						if ( $file_url ) {
							$file_type = wp_check_filetype( $file_url );
							$ext       = isset( $file_type['ext'] ) ? strtolower( $file_type['ext'] ) : '';
							if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ), true ) ) {
								echo '<img class="directorist-sv-preview-image" src="' . esc_url( $file_url ) . '" alt="" />';
							} else {
								echo '<p class="directorist-sv-preview-link"><a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Document', 'directorist-seller-verification' ) . '</a></p>';
							}
						}
					}
					?>
				</div>
			</div>

            <!-- Display verification status -->
            <div class="directorist-form-group directorist-sv-status-block">
                <label><strong><?php esc_html_e( 'Verification Status', 'directorist-seller-verification' ); ?></strong></label>
                <br />
                <p class="directorist-sv-status <?php echo $is_verified ? 'directorist-sv-status--verified' : 'directorist-sv-status--pending'; ?>">
					<?php echo $is_verified ? esc_html__( 'Verified', 'directorist-seller-verification' ) : esc_html__( 'Pending', 'directorist-seller-verification' ); ?>
				</p>
            </div>
		</div>

        <div class="directorist-alert directorist-sv-hidden" id="directorist_sv_message"></div>

		<button type="button" class="directorist-btn directorist-btn-primary" id="directorist_sv_save">
			<?php esc_html_e( 'Save', 'directorist-seller-verification' ); ?>
		</button>
	</form>
</div>


