<?php
/**
 * Checkout Field Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Chilean_RUT_Checkout_Field {

	private $validator;

	public function __construct( $validator ) {
		$this->validator = $validator;
		$this->init_hooks();
	}

	private function init_hooks() {
		add_filter( 'woocommerce_billing_fields', array( $this, 'add_billing_field' ), 20 );
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_field' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_order_field' ), 10, 2 );

		// Prevenir que WooCommerce muestre el campo RUT automáticamente en TODOS los lugares
		add_filter( 'woocommerce_admin_billing_fields', array( $this, 'remove_rut_from_admin_display' ) );
		add_filter( 'woocommerce_customer_meta_fields', array( $this, 'remove_rut_from_customer_meta' ) );
		add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'remove_rut_from_formatted_address' ), 10, 2 );
		add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'remove_rut_from_replacements' ), 10, 2 );
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'remove_rut_from_formatted_address' ), 10, 3 );

		// Mostrar RUT en ubicación correcta (UNA SOLA VEZ)
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_admin_order_meta' ), 10, 1 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_billing_field( $fields ) {
		$fields['billing_rut'] = array(
			'type'              => 'text',
			'label'             => __( 'RUT', 'wc-chilean-rut' ),
			'placeholder'       => __( '12.345.678-9', 'wc-chilean-rut' ),
			'required'          => true,
			'class'             => array( 'form-row-wide' ),
			'priority'          => 25,
			'maxlength'         => 12,
			'custom_attributes' => array(
				'maxlength'    => '12',
				'autocomplete' => 'off',
			),
			// NO mostrar en dirección formateada
			'show'              => false,
		);
		return $fields;
	}

	public function remove_rut_from_admin_display( $fields ) {
		unset( $fields['billing_rut'] );
		return $fields;
	}

	public function remove_rut_from_customer_meta( $fields ) {
		if ( isset( $fields['billing']['fields']['billing_rut'] ) ) {
			unset( $fields['billing']['fields']['billing_rut'] );
		}
		return $fields;
	}

	public function remove_rut_from_formatted_address( $address, $order ) {
		// Eliminar RUT de la dirección formateada
		unset( $address['billing_rut'] );
		return $address;
	}

	public function remove_rut_from_replacements( $replacements, $args ) {
		// Eliminar cualquier reemplazo de RUT en la dirección
		if ( isset( $replacements['{billing_rut}'] ) ) {
			unset( $replacements['{billing_rut}'] );
		}
		if ( isset( $replacements['{billing_rut_upper}'] ) ) {
			unset( $replacements['{billing_rut_upper}'] );
		}
		return $replacements;
	}

	public function validate_checkout_field( $fields, $errors ) {
		$rut = isset( $fields['billing_rut'] ) ? sanitize_text_field( $fields['billing_rut'] ) : '';

		if ( empty( $rut ) ) {
			$errors->add( 'billing_rut_required', __( 'El RUT es obligatorio.', 'wc-chilean-rut' ) );
			return;
		}

		if ( ! $this->validator->validate( $rut ) ) {
			$errors->add( 'billing_rut_invalid', __( 'El RUT ingresado no es válido.', 'wc-chilean-rut' ) );
		}
	}

	public function save_order_field( $order, $data ) {
		if ( empty( $data['billing_rut'] ) ) {
			return;
		}

		$rut = sanitize_text_field( $data['billing_rut'] );
		$rut_normalized = $this->validator->normalize( $rut );
		$rut_formatted = $this->validator->format( $rut );
		
		// Guardar solo en order meta (HPOS compatible)
		$order->update_meta_data( 'billing_rut', $rut_normalized );
		$order->update_meta_data( '_billing_rut', $rut_normalized );
		$order->update_meta_data( 'billing_rut_formatted', $rut_formatted );
		
		// NO guardar como billing_rut en la dirección para evitar duplicados
		// Solo en meta data personalizada
	}

	public function display_admin_order_meta( $order ) {
		$rut = $order->get_meta( 'billing_rut_formatted' );
		if ( empty( $rut ) ) {
			$rut = $order->get_meta( 'billing_rut' );
		}
		if ( ! empty( $rut ) ) {
			echo '<p><strong>' . esc_html__( 'RUT', 'wc-chilean-rut' ) . ':</strong> ' . esc_html( $rut ) . '</p>';
		}
	}

	public function enqueue_scripts() {
		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_style(
			'wc-chilean-rut',
			WC_CHILEAN_RUT_PLUGIN_URL . 'assets/css/rut-checkout.css',
			array(),
			WC_CHILEAN_RUT_VERSION
		);

		wp_enqueue_script(
			'wc-chilean-rut',
			WC_CHILEAN_RUT_PLUGIN_URL . 'assets/js/rut-checkout.js',
			array( 'jquery' ),
			WC_CHILEAN_RUT_VERSION,
			true
		);

		wp_localize_script(
			'wc-chilean-rut',
			'wcChileanRUT',
			array(
				'messages' => array(
					'empty'   => __( 'Por favor ingresa tu RUT.', 'wc-chilean-rut' ),
					'invalid' => __( 'El RUT ingresado no es válido.', 'wc-chilean-rut' ),
					'valid'   => __( 'RUT válido.', 'wc-chilean-rut' ),
				),
			)
		);
	}
}
