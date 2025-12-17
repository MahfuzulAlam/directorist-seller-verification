<?php

/**
 * Helper functions for Directorist - Seller Verification.
 *
 * @package Directorist_Seller_Verification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper to render a single document preview block.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $label         Label to display.
 *
 * @return void
 */
if ( ! function_exists( 'directorist_seller_verification_render_document_preview' ) ) {
	function directorist_seller_verification_render_document_preview( $attachment_id, $label ) {
		$attachment_id = (int) $attachment_id;
		?>
		<div class="directorist-seller-document-item">
			<h4><?php echo esc_html( $label ); ?></h4>
			<?php
			if ( $attachment_id > 0 ) {
				$file_url  = wp_get_attachment_url( $attachment_id );
				$file_type = $file_url ? wp_check_filetype( $file_url ) : array();

				if ( $file_url ) {
					$ext = isset( $file_type['ext'] ) ? strtolower( $file_type['ext'] ) : '';

					if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ), true ) ) {
						?>
						<div class="directorist-seller-document-preview">
							<img src="<?php echo esc_url( $file_url ); ?>" alt="<?php echo esc_attr( $label ); ?>" style="max-width: 320px; height: auto;" />
						</div>
						<?php
					} else {
						?>
						<p>
							<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'View Document', 'directorist-seller-verification' ); ?>
							</a>
						</p>
						<?php
					}
				} else {
					?>
					<p><?php esc_html_e( 'Document not available.', 'directorist-seller-verification' ); ?></p>
					<?php
				}
			} else {
				?>
				<p><?php esc_html_e( 'No document uploaded yet.', 'directorist-seller-verification' ); ?></p>
				<?php
			}
			?>
		</div>
		<?php
	}
}

function allow_subscriber_uploads() {
    $subscriber = get_role('subscriber');
    if ($subscriber) {
        $subscriber->add_cap('upload_files');
    }
}
add_action('init', 'allow_subscriber_uploads');