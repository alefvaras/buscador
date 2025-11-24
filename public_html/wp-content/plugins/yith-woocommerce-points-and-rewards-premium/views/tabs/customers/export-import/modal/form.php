<?php
$forms = array(
	'export' =>
		array(
			'title'   => __( 'Export points', 'yith-woocommerce-point-and-rewards' ),
			'options' => array(
				'csv_format'    => array(
					'label'   => esc_html_x(
						'Choose the CSV format',
						'[Admin Import/Export modal] Input label.',
						'yith-woocommerce-points-and-rewards'
					),
					'desc'    => '',
					'type'    => 'radio',
					'options' => array(
						'id'    => esc_html_x(
							'User ID / Points',
							'[Admin Import/Export modal] Input radio value label.',
							'yith-woocommerce-points-and-rewards'
						),
						'email' => esc_html_x(
							'User Email / Points',
							'[Admin Import/Export modal] Input radio value label.',
							'yith-woocommerce-points-and-rewards'
						),
					),
					'default' => 'id',
					'value'   => 'id',
					'id'      => 'csv_format-export',
					'name'    => 'csv_format',
				),

				'csv_delimiter' => array(
					'label' => esc_html_x(
						'CSV Delimiter',
						'[Admin Import/Export modal] Field label',
						'yith-woocommerce-points-and-rewards',
					),
					'desc'  => esc_html_x(
						'Set character to use as delimiter for the CSV file',
						'[Admin Import/Export modal] Field description.',
						'yith-woocommerce-points-and-rewards'
					),
					'type'  => 'text',
					'value' => ',',
					'id'    => 'csv_delimiter',
					'name'  => 'csv_delimiter',
				),
			),

		),

	'import' => array(
		'title'   => __( 'Import points', 'yith-woocommerce-point-and-rewards' ),
		'options' => array(
			'csv_format'    => array(
				'label'   => esc_html_x(
					'Choose the CSV format',
					'[Admin Import/Export modal] Input label.',
					'yith-woocommerce-points-and-rewards'
				),
				'desc'    => '',
				'type'    => 'radio',
				'options' => array(
					'id'    => esc_html_x(
						'User ID / Points',
						'[Admin Import/Export modal] Input radio value label.',
						'yith-woocommerce-points-and-rewards'
					),
					'email' => esc_html_x(
						'User email / Points',
						'[Admin Import/Export modal] Input radio value label.',
						'yith-woocommerce-points-and-rewards'
					),
				),
				'default' => 'id',
				'value'   => 'id',
				'id'      => 'csv_format-import',
				'name'    => 'csv_format',
			),
			'action'        => array(
				'label'   => esc_html_x(
					'CSV import action',
					'[Admin Import/Export modal] Input label.',
					'yith-woocommerce-points-and-rewards'
				),
				'desc'    => esc_html_x(
					'Choose if imported points will be added to the current balance or will overwrite it.',
					'[Admin Import/Export modal] Input description.',
					'yith-woocommerce-points-and-rewards'
				),
				'type'    => 'radio',
				'options' => array(
					'add'    => esc_html_x(
						'Add points to current balance',
						'[Admin Import/Export modal] Input radio value label.',
						'yith-woocommerce-points-and-rewards'
					),
					'remove' => esc_html_x(
						'Overwrite points',
						'[Admin Import/Export modal] Input radio value label.',
						'yith-woocommerce-points-and-rewards'
					),
				),
				'default' => 'add',
				'value'   => 'add',
				'id'      => 'action',
				'name'    => 'action',
			),
			'csv_delimiter' => array(
				'label' => esc_html_x(
					'CSV Delimiter',
					'[Admin Import/Export modal] Field label',
					'yith-woocommerce-points-and-rewards'
				),
				'desc'  => esc_html_x(
					'Set character to use as delimiter for the CSV file',
					'[Admin Import/Export modal] Field description.',
					'yith-woocommerce-points-and-rewards'
				),
				'type'  => 'text',
				'value' => ',',
				'id'    => 'csv_delimiter',
				'name'  => 'csv_delimiter',
			),
			'csv_file'      => array(
				'label'            => esc_html_x(
					'Choose a CSV file',
					'[Admin Import/Export modal] Input label.',
					'yith-woocommerce-points-and-rewards'
				),
				'desc'             => esc_html_x(
					'Choose the CSV file to import.',
					'[Admin Import/Export modal] Field description.',
					'yith-woocommerce-points-and-rewards'
				),
				'type'             => 'media',
				'allow_custom_url' => false,
				'id'               => 'csv_file',
				'name'             => 'csv_file',
			),
		),

	),
);

foreach ( $forms as $form_key => $form ) : ?>
	<div id="<?php echo esc_attr( $form_key ); ?>" class="<?php echo esc_attr( $form_key ); ?>-configuration single-configuration">
		<h2><?php echo esc_html( $form['title'] ); ?></h2>
		<form id="<?php echo esc_attr( $form_key ); ?>" name="<?php echo esc_attr( $form_key ); ?>" enctype="multipart/form-data" method="POST">
			<?php foreach ( $form['options'] as $key => $option ) : ?>
				<div class="wrap-option <?php echo esc_attr( $key ); ?>">
					<span class="option-label left-side"><?php echo esc_html( $option['label'] ); ?></span>
					<span class="right-side">
					<?php yith_plugin_fw_get_field( $option, true ); ?>
				<p class="option-description"><?php echo wp_kses_post( $option['desc'] ); ?></p>
				</span>
				</div>
			<?php endforeach; ?>
		</form>
	</div>
<?php endforeach; ?>
