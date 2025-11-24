<?php
/**
 * HTML Template Email Recover Abandoned Cart
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author YITH <plugins@yithemes.com>
 */

extract( $args );
/**
 * DO_ACTION: woocommerce_email_header
 *
 * WooCommerce email header.
 *
 * @param string $email_heading
 * @param WC_Email $email
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<?php echo wpautop( $email_content ); //phpcs:ignore ?>

<?php
	/**
	 * DO_ACTION: woocommerce_email_footer
	 *
	 * WooCommerce email footer.
	 *
	 * @param WC_Email $email
	 */
	do_action( 'woocommerce_email_footer', $email );

