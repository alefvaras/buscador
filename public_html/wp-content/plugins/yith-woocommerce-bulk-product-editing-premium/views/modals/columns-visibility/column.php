<?php
/**
 * Columns visibility modal - single column template
 *
 * @var string $key  The column key.
 * @var string $name The column name.
 *
 * @package YITH\BulkProductEditing\Views\Modals\ColumnsVisibility
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-visibility-column yith-wcbep-visibility-column__<?php echo esc_attr( $key ); ?>">
	<input type="checkbox" id="yith-wcbep-visibility-column-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>">
	<label for="yith-wcbep-visibility-column-<?php echo esc_attr( $key ); ?>" class="yith-wcbep-visibility-column__label"><?php echo esc_html( $name ); ?></label>
</div>
