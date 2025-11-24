<?php
/**
 * Product table Empty state.
 *
 * @package YITH\BulkProductEditing\Views
 * @var string $id               The empty state ID.
 * @var string $icon             The icon name.
 * @var string $message          The empty state message.
 * @var string $cta              The empty state call to action.
 * @var string $cta_button_class The class of the CTA button.
 *
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div id="<?php echo ! empty( $id ) ? esc_attr( $id ) : ''; ?>" class="yith-wcbep-empty-state-container">
	<div class="yith-wcbep-empty-state-icon">
		<?php yith_wcbep_get_icon( $icon, true ); ?>
	</div>
	<?php if ( ! empty( $message ) ) : ?>
		<div class="yith-wcbep-empty-state-text">
			<?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $cta ) ) : ?>
		<div class="yith-wcbep-empty-state-cta">
			<button class="yith-wcbep-empty-state-cta yith-plugin-fw__button--primary <?php echo ! empty( $cta_button_class ) ? esc_attr( $cta_button_class ) : ''; ?>"><?php echo esc_html( $cta ); ?></button>
		</div>
	<?php endif; ?>
</div>
