<div id="yith_woocommerce_recover_abandoned_cart_pending_orders" class="yith-plugin-ui--yith-pft-boxed-post_type yith-plugin-ui--boxed-wp-list-style">
    <div id="ywrac-pending-orders-content" class="wrap">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">		
				<?php
				$this->cpt_obj_pending_orders->prepare_items();
				if ( $this->cpt_obj_pending_orders->has_items() ) : ?>
					<form method="post">
						<?php

						$this->cpt_obj_pending_orders->display();
						?>
					</form>
					<?php else : ?>
						<?php 
								$submessage = '<p><small>' . esc_html__( "But don't worry, soon something cool will appear here.", 'yith-woocommerce-recover-abandoned-cart' ) . '</small></p>';
								yith_plugin_fw_get_component(
									array(
										'type'     => 'list-table-blank-state',
										'icon_url' => esc_url( YITH_YWRAC_ASSETS_URL ) . '/images/abandoned-cart.svg',
										'message'  => esc_html__( 'You have no pending orders yet.', 'yith-woocommerce-recover-abandoned-cart' ) . $submessage,
									)
								);
						?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>
