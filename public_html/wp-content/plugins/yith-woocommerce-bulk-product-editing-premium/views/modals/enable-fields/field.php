<?php
/**
 * The Field of the "Enable Fields" modal
 *
 * @var string $key           The field key.
 * @var string $label         The field label.
 * @var string $kind          The field kind.
 * @var array  $classes       The field classes.
 * @var array  $data          The field data.
 * @var bool   $enabled       Is the field enabled.
 * @var bool   $print_actions Has field actions.
 *
 * @package YITH\BulkProductEditing\Views\Modals\EnableFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div id="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-field-options="<?php echo $data ? esc_attr( wp_json_encode( $data ) ) : ''; ?>">
	<input type="checkbox" id="<?php echo esc_attr( 'yith-wcbep-show-column-' . $key ); ?>" class="yith-wcbep-enable-fields__field-input" <?php checked( $enabled ); ?> value="<?php echo esc_attr( $key ); ?>">
	<label for="<?php echo esc_attr( 'yith-wcbep-show-column-' . $key ); ?>" class="yith-wcbep-enable-fields__field-label"><?php echo esc_html( $label ); ?></label>
	<?php if ( in_array( $kind, array( 'custom_field', 'taxonomy' ), true ) ) : ?>
		<span class="yith-icon yith-icon-edit yith-wcbep-enable-fields__field-action yith-wcbep-enable-fields__field-action--edit" title="<?php echo esc_html_x( 'Edit', '[Enable fields popup] Custom fields action', 'yith-woocommerce-bulk-product-editing' ); ?>"></span>
		<span class="yith-icon yith-icon-trash yith-wcbep-enable-fields__field-action yith-wcbep-enable-fields__field-action--delete" title="<?php echo esc_html_x( 'Delete', '[Enable fields popup] Custom fields action', 'yith-woocommerce-bulk-product-editing' ); ?>"></span>
	<?php endif; ?>
	<input type="hidden" class="yith-wcbep-enable-field-kind" name="yith-wcbm-enable-fields[<?php echo esc_attr( $key ); ?>]['kind']" value="<?php echo esc_attr( $kind ); ?>">
	<?php if ( 'custom_field' === $kind ) : ?>
		<input type="hidden" class="yith-wcbep-enable-field-custom-field" name="yith-wcbm-enable-fields[<?php echo esc_attr( $key ); ?>]['custom_field']" value="<?php echo esc_attr( $data['type'] ); ?>">
		<input type="hidden" class="yith-wcbep-enable-field-type" name="yith-wcbm-enable-fields[<?php echo esc_attr( $key ); ?>]['type']" value="<?php echo esc_attr( $data['type'] ); ?>">
		<input type="hidden" class="yith-wcbep-enable-field-label" name="yith-wcbm-enable-fields[<?php echo esc_attr( $key ); ?>]['label']" value="<?php echo esc_attr( $data['label'] ); ?>">
	<?php elseif ( 'taxonomy' === $kind ) : ?>
		<input type="hidden" class="yith-wcbep-enable-field-taxonomy" name="yith-wcbm-enable-fields[<?php echo esc_attr( $key ); ?>]['taxonomy']" <?php echo esc_attr( $data['taxonomy'] ); ?>>
	<?php endif; ?>
</div>
