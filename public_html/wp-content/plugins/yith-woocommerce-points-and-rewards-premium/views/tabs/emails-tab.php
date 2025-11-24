<?php
$emails = array(
	'expiring-points' => array(
		'name'       => esc_html__( 'Expiring Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'       => esc_html__( 'Send an email before the expiration date', 'yith-woocommerce-points-and-rewards' ),
		'enable_option_key' => 'send_email_before_expiration_date',
		'options'    => array(

			'send_email_days_before'   => array(
				'title'   => esc_html__( 'Days before points expire', 'yith-woocommerce-points-and-rewards' ),
				'name'    => 'send_email_days_before',
				'desc'    => esc_html__(
					'Number of days before \'points expiration\' when the email will be sent',
					'yith-woocommerce-points-and-rewards'
				),
				'type'    => 'number',
				'default' => 2,
				'id'      => 'ywpar_send_email_days_before',
				'value'   => ywpar_get_option( 'send_email_days_before', 2 ),
			),

			'expiration_email_content' => array(
				'title'   => esc_html__( 'Email content', 'yith-woocommerce-points-and-rewards' ),
				'name'    => 'expiration_email_content',
				'desc'    => _x(
					'<b>You can use following placeholders:</b><br>
                {username} = customer\'s username <br>
                {first_name} = customer\'s  first name <br>
                {last_name} = customer\'s last name <br>
                {expiring_points} = expiring points <br>
                {label_points} = label for points <br>
                {expiring_date} = points expiry date<br>
                {total_points} = current balance<br>
                {shop_url} = URL of the shop<br>
                {discount} = value of the discount<br>
				{website_name} = website name',
					'do not translate the text inside the brackets',
					'yith-woocommerce-points-and-rewards'
				),
				'type'    => 'textarea-editor',
				'default' => _x(
					'Hi {username}!
			
                                We send you this message because the <b>{expiring_points} {label_points}</b> you\'ve already earned in our shop will expire on {expiring_date}.
                                
                                <div class="points_banner">
                                Use them now to get a <b>discount of {discount}</b> in your purchase!
                                <a href="{shop_url}">Check our shop now ></a>
                                </div>
                                
                                Regards,
                                {website_name} staff
                              ',
					'do not translate the text inside the brackets',
					'yith-woocommerce-points-and-rewards'
				),
				'id'      => 'ywpar_expiration_email_content',
				'value'   => ywpar_get_option( 'expiration_email_content', 2 ),
			),
		),
	),

	'update_points'   => array(
		'name'      => esc_html__( 'Updated Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'       => esc_html__(
			"Send an email when points are updated",
			'yith-woocommerce-points-and-rewards'
		),
		'enable_option_key' => 'enable_update_point_email',
		'options'    => array(

			'update_point_mail_time'            => array(
				'title'   => esc_html__( 'Send email', 'yith-woocommerce-points-and-rewards' ),
				'name'    => 'update_point_mail_time',
				'type'    => 'radio',
				'options' => array(
					'daily'        => esc_html__(
						'Once a day if points have been updated',
						'yith-woocommerce-points-and-rewards'
					),
					'every_update' => esc_html__(
						'As soon as points are updated',
						'yith-woocommerce-points-and-rewards'
					),
				),
				'default' => 'daily',
				'id'      => 'ywpar_update_point_mail_time',
				'value'   => ywpar_get_option( 'update_point_mail_time' ),
			),

			'update_point_mail_on_admin_action' => array(
				'title'   => esc_html__(
					'Avoid email sending for manual update',
					'yith-woocommerce-points-and-rewards'
				),
				'name'    => 'update_point_mail_on_admin_action',
				'desc'    => esc_html__(
					'Enable to not send the email when the admin updates points manually',
					'yith-woocommerce-points-and-rewards'
				),
				'type'    => 'onoff',
				'default' => 'yes',
				'id'      => 'ywpar_update_point_mail_on_admin_action',
				'value'   => ywpar_get_option( 'update_point_mail_on_admin_action' ),

			),

			'update_point_email_content'        => array(
				'title'   => esc_html__( 'Email content', 'yith-woocommerce-points-and-rewards' ),
				'name'    => 'update_point_email_content',
				'desc'    => sprintf(
					'%s %s {username} = %s {first_name} = %s {last_name} = %s {latest_updates} = %s {total_points} = %s %s {discount} = %s {shop_url} = %s {website_name} = %s',
					'<b>',
                    esc_html__(
						'You can use following placeholders:',
						'yith-woocommerce-points-and-rewards'
					) . '</b><br>',
					esc_html__( "customer's username", 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( "customer's first name", 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( "customer's last name", 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( 'latest updates of your points', 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( 'label for points', 'yith-woocommerce-points-and-rewards' ),
					esc_html__( 'current balance', 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( 'value of the discount', 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( 'URL of the shop', 'yith-woocommerce-points-and-rewards' ) . '<br>',
					esc_html__( 'website name', 'yith-woocommerce-points-and-rewards' )
				),
				'type'    => 'textarea-editor',
				'default' => _x(
					'Hi {username}!
			
Here you can find the updated balance of your {label_points}.
 
 <div class="points_banner">
 Total points collected: <b>{total_points}</b>
 <a href="{shop_url}">Redeem them in your next order ></a>
</div>

 Well done!
 {website_name} staff',
					'do not translate the text inside the brackets',
					'yith-woocommerce-points-and-rewards'
				),
				'id'      => 'ywpar_update_point_email_content',
				'value'   => ywpar_get_option( 'update_point_email_content' ),
			),
		),
	),
);

?>

<div class="yith-plugin-fw yit-admin-panel-container" id="ywcpar-emails-wrapper">
	<div id="ywcpar-table-emails">
		<div class="heading-table ywcpar-row">
			<span class="ywcpar-column email"><?php esc_html_e( 'Email', 'yith-woocommerce-points-and-rewards' ); ?></span>
			<span class="ywcpar-column recipient">
			<?php
			esc_html_e(
				'Description',
				'yith-woocommerce-points-and-rewards'
			);
			?>
					</span>
            <span class="ywcpar-column status">
                <?php
                esc_html_x(
	                'Active',
	                '[ADMIN] Column name table emails',
	                'yith-woocommerce-points-and-rewards'
                );
                ?>
			</span>
			<span class="ywcpar-column action"></span>

		</div>
		<div class="content-table">
			<?php foreach ( $emails as $email_key => $email ) : ?>
				<div class="ywcpar-row">
					<span class="ywcpar-column email">
						<?php esc_html_e( $email['name'] ); ?>
					</span>
					<span class="ywcpar-column recipient">
						<?php echo esc_html( $email['desc'] ); ?>
					</span>

					<span class="ywcpar-column status">
						<?php
						$email_status = array(
							'id'      => 'ywpar-email-status',
                            'name'    => $email['enable_option_key'],
							'type'    => 'onoff',
							'default' => 'yes',
							'value'   => ywpar_get_option( $email['enable_option_key'], 'no' ),
							'data'    => array(
								'email_key' => $email_key,
							),
						);

						yith_plugin_fw_get_field( $email_status, true );
						?>
					</span>

                    <span class="ywcpar-column action">
						<?php
						yith_plugin_fw_get_component(
							array(
								'title'  => __( 'Edit', 'yith-woocommerce-points-and-rewards' ),
								'type'   => 'action-button',
								'action' => 'edit',
								'icon'   => 'edit',
								'data'   => array(
									'target' => $email_key,
								),
								'class'  => 'toggle-settings',
							)
						);
						?>
					</span>
					<form class="email-settings" id="<?php echo esc_attr( $email_key ); ?>">
						<?php
						foreach ( $email['options'] as $key => $option ) {
							?>
							<div class="yith-plugin-fw  yit-admin-panel-container">
								<table class="form-table">
									<tbody>
									<tr>
										<th><?php esc_html_e( $option['title'] ); ?></th>
										<td>
											<?php yith_plugin_fw_get_field( $option, true ); ?>
											<p>
												<?php echo isset( $option['desc'] ) ? wp_kses_post( $option['desc'] ) : ''; ?>
											<p>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
							<?php
						}
						?>

						<p class="submit">
							<button name="save"
									class="button-primary yith-plugin-fw__button--xl ywpar-save-settings"
									type="submit">
								<?php esc_html_e( 'Save', 'yith-woocommerce-points-and-rewards' ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endforeach; ?>

		</div>
	</div>

</div>
