<?php
/**
 * @author  wpWax
 * @since   6.6
 * @version 7.3.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$alignment =  ! empty( $data['align'] ) ? $data['align'] : '' ;
$verified = get_user_meta( $listings->loop['author_id'], 'verify_seller', true );
$is_verified = ( 'yes' === $verified );

?>
<div class="directorist-thumb-listing-author directorist-alignment-<?php echo esc_attr( $alignment ) ?>" tooltip="<?php esc_attr_e( 'Verified seller', 'directorist-seller-verification' ); ?>">
    <a href="<?php echo esc_url( $listings->loop['author_link'] ); ?>" aria-label="Author Image" class="<?php echo esc_attr( $listings->loop['author_link_class'] ); ?>">
        <?php if ( $listings->loop['u_pro_pic'] ) { ?>
            <img src="<?php echo esc_url( $listings->loop['u_pro_pic'][0] ); ?>" alt="<?php esc_attr_e( 'Author Image', 'directorist' );?>">
            <?php
        } else {
            echo wp_kses_post( $listings->loop['avatar_img'] );
        }
        ?>
    </a>
    <?php if( $is_verified ) { echo '<span class="directorist-sv-verified-badge">' . directorist_icon('fa fa-check', false) . '</span>'; } ?>
</div>