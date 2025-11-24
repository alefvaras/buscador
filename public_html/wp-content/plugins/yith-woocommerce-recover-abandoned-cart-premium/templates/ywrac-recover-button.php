<?php
/**
 * HTML Template Recover Cart button
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author YITH <plugins@yithemes.com>
 */

?>

<?php if ( ! empty( $label ) && ! empty( $link ) ) : ?>
	<?php
	/**
	 * APPLY_FILTERS: ywrac_recover_button_color
	 *
	 * Recover button color.
	 *
	 * @param string $color
	 */
	$color = apply_filters( 'ywrac_recover_button_color', get_option( 'woocommerce_email_base_color', '#96588a' ) );
	/**
	 * APPLY_FILTERS: ywrac_recover_button_text_color
	 *
	 * Recover button text color.
	 *
	 * @param string $color
	 */
	$text_color = apply_filters( 'ywrac_recover_button_text_color', '#fff' );
	?>
	<a class="ywrac-cart-button" href="<?php echo esc_url( $link ); ?>"><?php echo wp_kses_post( $label ); ?></a>
	<style type="text/css">
		.ywrac-cart-button {
			padding: 15px 30px;
			line-height: 50px;
			background-color: <?php echo esc_html( $color ); ?>;
			text-transform: uppercase;
			text-decoration: none;
			border-radius: 3px;
			font-weight: bold;
			color: <?php echo esc_html( $text_color ); ?>;;
		}
	</style>
<?php endif; ?>
