<?php
/**
 * Enable Fields modal content
 *
 * @var array $column_list     The column list.
 * @var array $taxonomies      The taxonomies list.
 * @var array $columns         The column list.
 * @var array $enabled_columns The enabled columns.
 * @var array $custom_fields   The custom columns.
 *
 * @package YITH\BulkProductEditing\Views\Modals\EnableFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$add_custom_column_fields = yith_wcbep_get_enable_fields_add_custom_column_fields();

?>

<div class="yith-wcbep-modal-content-wrapper">
	<div class="yith-wcbep-enable-fields__list-container">

		<div class="yith-wcbep-enable-fields__actions">
			<div class="yith-wcbep-enable-fields__toggle-all">
				<input type="checkbox" id="yith-wcbep-enable-fields-toggle-all" <?php checked( ! array_diff( array_keys( $column_list ), $enabled_columns ) ); ?>>
				<label for="yith-wcbep-enable-fields-toggle-all"><?php esc_html_e( 'Enable/Disable all', 'yith-woocommerce-bulk-product-editing' ); ?></label>
			</div>
			<div class="yith-wcbep-enable-fields__search-column">
				<input type="text" id="yith-wcbep-enable-fields-search-column" class="yith-wcbep-enable-fields__search-column__input" placeholder="<?php esc_attr_e( 'Search for a field...', 'yith-woocommerce-bulk-product-editing' ); ?>">
				<span class="yith-wcbep-enable-fields__search-column__icon"></span>
			</div>
		</div>
		<div class="yith-wcbep-enable-fields__list-wrapper">
			<div class="yith-wcbep-enable-fields__list-wrapper__shadow"></div>
			<div class="yith-wcbep-enable-fields__list">
				<?php
				$current_kind = 'simple';
				foreach ( $columns as $kind => $column_list ) {
					if ( $current_kind !== $kind ) {
						$titles = array(
							'custom_field' => _x( 'Custom fields', 'Enable fields modal - field kinds separator title', 'yith-woocommerce-bulk-product-editing' ),
							'taxonomy'     => _x( 'Taxonomies', 'Enable fields modal - field kinds separator title', 'yith-woocommerce-bulk-product-editing' ),
						);
						echo '<div class="yith-wcbep-enable-fields__kind yith-wcbep-enable-fields__kind-' . esc_attr( $kind ) . '">' . esc_html( $titles[ $kind ] ) . '</div>';
						$current_kind = $kind;
					}
					foreach ( $column_list as $key => $field_data ) {
						$label         = $field_data['label'];
						$data          = array();
						$classes       = array( 'yith-wcbep-enable-fields__field' );
						$enabled       = in_array( $key, $enabled_columns, true );
						$print_actions = false;
						if ( in_array( $kind, array( 'custom_field', 'taxonomy' ), true ) ) {
							$print_actions = true;
							$classes[]     = 'yith-wcbep-enable-fields__' . ( 'taxonomy' === $kind ? 'taxonomy' : 'custom' ) . '-field';
							$data          = $field_data;
							if ( $data ) {
								$data['key'] = $key;
							}
						}
						$data['kind'] = $kind;
						yith_wcbep_get_view( 'modals/enable-fields/field.php', compact( 'key', 'label', 'kind', 'enabled', 'classes', 'print_actions', 'data' ) );
					}
				}
				?>
				<span class="yith-wcbep-enable-fields__list--empty-search-message">
					<?php esc_html_e( 'There are no columns matching your search', 'yith-woocommerce-bulk-product-editing' ); ?>
				</span>
			</div>
		</div>

	</div>

	<form class="yith-wcbep-add-custom-fields-container">
		<span class="yith-wcbep-add-custom-fields__title">
			<?php echo esc_html_x( 'Add custom field or taxonomy', '[Enable fields popup] Add custom fields - title', 'yith-woocommerce-bulk-product-editing' ); ?>
		</span>

		<table class="yith-wcbep-add-custom-fields__options">
			<?php foreach ( $add_custom_column_fields as $field ) : ?>
				<tr class="yith-wcbep-add-custom-field__option <?php echo esc_attr( $field['row_class'] ?? '' ); ?>">
					<td class="yith-wcbep-add-custom-field__option-label">
						<label for="<?php echo esc_attr( $field['id'] ?? '' ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
					</td>
					<td class="yith-wcbep-add-custom-field__option-input"><?php yith_plugin_fw_get_field( $field, true ); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>

		<button id="yith-wcbep-add-field-button" class="yith-wcbep-add-custom-fields__add-button yith-plugin-fw__button--secondary yith-plugin-fw__button--xl">
			<?php echo esc_html_x( 'Add', '[Enable fields popup] Add custom fields - button', 'yith-woocommerce-bulk-product-editing' ); ?>
		</button>
	</form>

</div>
