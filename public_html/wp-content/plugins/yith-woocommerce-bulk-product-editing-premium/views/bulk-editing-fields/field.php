<?php
/**
 * Field - Bulk editing
 *
 * @var array  $args  The field info.
 * @var string $id    The field id.
 * @var string $label The field label.
 * @var string $type  The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$has_advanced_options = 'attribute' === $type;

?>

<div class="yith-wcbep-bulk-editing-field yith-wcbep-bulk-editing-field-<?php echo esc_attr( $type ); ?> <?php echo $has_advanced_options ? 'yith-wcbep-bulk-editing-field--has-advanced-options' : ''; ?>" id="yith-wcbep-bulk-editing-field-<?php echo esc_attr( $id ); ?>" data-field-id="<?php echo esc_attr( $id ); ?>" data-field-type="<?php echo esc_attr( $type ); ?>">
	<span class="yith-wcbep-bulk-editing-field__label"><?php echo esc_html( $label ); ?></span>
	<div class="yith-wcbep-bulk-editing-field__options">
		<?php
		if ( file_exists( YITH_WCBEP_VIEWS_PATH . 'bulk-editing-fields/' . $type . '.php' ) ) {
			yith_wcbep_get_view( 'bulk-editing-fields/' . $type . '.php', compact( 'id', 'label', 'type', 'args' ) );
		} else {
			do_action( 'yith_wcbep_bulk_editing_field_' . $type, $id, $label, $args );
		}
		?>
	</div>
</div>
