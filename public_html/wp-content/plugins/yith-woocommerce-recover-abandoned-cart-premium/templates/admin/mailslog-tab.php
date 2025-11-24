<?php
$this->cpt_obj_mailslog->prepare_items();
if ( $this->cpt_obj_mailslog->has_items() ) {
?>
<div id="yith_woocommerce_recover_abandoned_cart_mailslog" class="yith-plugin-ui--yith-pft-boxed-post_type yith-plugin-ui--boxed-wp-list-style">
	<div class="meta-box-sortables ui-sortable">
		<form method="post">
			<input type="hidden" name="page" value="yith_woocommerce_recover_abandoned_cart" />
			<?php $this->cpt_obj_mailslog->search_box( 'search', 'search_id' ); ?>
			<div style="clear: both;"></div>
		</form>
		<form method="post">
			<?php
				$this->cpt_obj_mailslog->display();
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
			'icon_url' => esc_url( YITH_YWRAC_ASSETS_URL ) . '/images/email.svg',
			'message'  => esc_html__( 'You have no emails sent yet.', 'yith-woocommerce-recover-abandoned-cart' ) . $submessage,
		)
	);
}
?>