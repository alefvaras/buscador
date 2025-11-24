<?php 
 	$this->cpt_obj_orders->prepare_items();
	if ( $this->cpt_obj_orders->has_items() ) {
?>
	<div id="yith_woocommerce_recover_abandoned_cart_recovered" class="yith-plugin-ui--yith-pft-boxed-post_type yith-plugin-ui--boxed-wp-list-style">
		<div class="meta-box-sortables ui-sortable">
			<form method="post">
				<?php
					$this->cpt_obj_orders->prepare_items();
					$this->cpt_obj_orders->display();
				?>
			</form>
		</div>
	</div>
<?php
	} else {
		$submessage = '<p><small>' . esc_html__( "But don't worry, soon something cool will appear here.", 'yith-woocommerce-recover-abandoned-cart' ) . '</small></p>';
		yith_plugin_fw_get_component(
			array(
				'type'     => 'list-table-blank-state',
				'icon_url' => esc_url( YITH_YWRAC_ASSETS_URL ) . '/images/recovered-cart.svg',
				'message'  => esc_html__( 'You have no recovered carts yet.', 'yith-woocommerce-recover-abandoned-cart' ) . $submessage,
			)
		);
	}
?>