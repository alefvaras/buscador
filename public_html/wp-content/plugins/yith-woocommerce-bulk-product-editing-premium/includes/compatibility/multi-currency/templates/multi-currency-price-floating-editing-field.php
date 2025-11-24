<?php
/**
 * Product floating editing field.
 *
 * @var YITH_WCMCS_Currency[] $currencies       The list of currencies.
 * @var string[]              $currency_symbols The currency symbols.
 *
 * @package YITH\BulkProductEditing\Compatibility\MultiCurrency
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>
<div class="yith-wcbep-multi-currency-price-wrapper">
	<div class="yith-wcbep-multi-currency-prices">
		<?php foreach ( $currencies as $currency ) : ?>
			<label for="yith-wcbep-multi-currency-price-<?php echo esc_attr( $currency->get_id() ); ?>" class="yith-wcbep-multi-currency-symbol">
				<?php echo esc_html( $currency_symbols[ $currency->get_id() ] ); ?>
			</label>
			<div class="yith-wcbep-multi-currency-price">
				<input type="text" id="yith-wcbep-multi-currency-price-<?php echo esc_attr( $currency->get_id() ); ?>" class="wc_input_price yith-wcbep-multi-currency-price-value" value="{{data.<?php echo esc_attr( $currency->get_id() ); ?>}}" data-currency-id="<?php echo esc_attr( $currency->get_id() ); ?>">
			</div>
		<?php endforeach; ?>
	</div>
</div>
