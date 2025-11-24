<div id="pre-orders-product-modal" class="wc-po-modal" style="display: none;">
	<div class="wc-po-modal-content">
		<button class="modal-close">&times;</button>
		<h2 id="modal-title"></h2>
		<div id="modal-body" class="panel woocommerce_options_panel">
			<h4 id="modal-product-title"></h4>
			<div id="enable-message" class="form-field" style="display: none;">
				<p class="description">
					<?php esc_html_e( 'Configure pre-order settings below to allow customers to place pre-orders for this product.', 'woocommerce-pre-orders' ); ?>
				</p>
			</div>
			<div class="options_group">
				<p class="form-field _wc_pre_orders_availability_datetime_field" id="availability-field">
					<label for="pre-order-availability">
						<?php esc_html_e( 'Release date (optional)', 'woocommerce-pre-orders' ); ?>
					</label>
					<input type="text" class="short" name="_wc_pre_orders_availability_datetime" id="pre-order-availability" value="" placeholder="YYYY-MM-DD HH:MM" />
					<span class="woocommerce-help-tip" tabindex="0" data-tip="<?php esc_attr_e( '(Optional) Specify when the product will be available. If set, customers will see this release date at checkout.', 'woocommerce-pre-orders' ); ?>" aria-label="<?php esc_attr_e( '(Optional) Specify when the product will be available. If set, customers will see this release date at checkout.', 'woocommerce-pre-orders' ); ?>"></span>
				</p>
				<p class="form-field _wc_pre_orders_fee_field" id="fee-field">
					<label for="pre-order-fee">
						<?php
							printf(
								/* translators: %s: WooCommerce currency symbol from settings*/
								esc_html__( 'Pre-order fee (%s - optional)', 'woocommerce-pre-orders' ),
								esc_html( get_woocommerce_currency_symbol() )
							);
							?>
					</label>
					<input type="text" class="short wc_input_price" id="pre-order-fee" placeholder="0.00" name="_wc_pre_orders_fee" />
					<span class="woocommerce-help-tip" tabindex="0" data-tip="<?php esc_attr_e( '(Optional) Add an extra charge for pre-orders. Leave blank (or zero) if no additional fee is required.', 'woocommerce-pre-orders' ); ?>" aria-label="<?php esc_attr_e( 'Add an extra charge for pre-orders. Leave blank (or zero) if no additional fee is required.', 'woocommerce-pre-orders' ); ?>"></span>
				</p>
				<p class="form-field _wc_pre_orders_when_to_charge_field" id="charge-field">
					<label for="pre-order-charge">
						<?php esc_html_e( 'Customers will be charged', 'woocommerce-pre-orders' ); ?>
					</label>
					<select id="pre-order-charge" class="select short wc-enhanced-select">
						<option value="upon_release"><?php esc_html_e( 'Upon release (pay later)', 'woocommerce-pre-orders' ); ?></option>
						<option value="upfront"><?php esc_html_e( 'Upfront (pay now)', 'woocommerce-pre-orders' ); ?></option>
					</select>
					<span class="woocommerce-help-tip" tabindex="0" data-tip="<?php esc_attr_e( 'Select &quot;Upon Release&quot; to charge the entire pre-order amount (the product price + pre-order fee if applicable) when the pre-order becomes available. Select &quot;Upfront&quot; to charge the pre-order amount during the initial checkout.', 'woocommerce-pre-orders' ); ?>" aria-label="<?php esc_attr_e( 'Select &quot;Upon Release&quot; to charge the entire pre-order amount (the product price + pre-order fee if applicable) when the pre-order becomes available. Select &quot;Upfront&quot; to charge the pre-order amount during the initial checkout.', 'woocommerce-pre-orders' ); ?>"></span>
				</p>
				<div class="form-field" id="confirm-message" style="display: none;">
					<p></p>
				</div>
				<div class="form-field" id="bulk-note" style="display: none;">
					<p class="description">
						<?php esc_html_e( "This product has active pre-orders, so settings can't be changed while they're in progress. If you disable pre-orders, the active pre-orders will be cancelled.", 'woocommerce-pre-orders' ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="wc-po-modal-buttons">
			<button class="button button-primary" id="modal-confirm">
				<?php esc_html_e( 'Enable pre-orders for this product', 'woocommerce-pre-orders' ); ?>
			</button>
			<button class="button" id="modal-cancel">
				<?php esc_html_e( 'Cancel', 'woocommerce-pre-orders' ); ?>
			</button>
		</div>
	</div>
</div>