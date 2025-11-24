<?php
/**
 * AJAX Handler - Simplified
 *
 * @package WC_Chilean_RUT_Validator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Chilean_RUT_Ajax_Handler
 */
class WC_Chilean_RUT_Ajax_Handler {

	private $validator;

	public function __construct( $validator ) {
		$this->validator = $validator;
		// No AJAX needed - validation happens on submit
	}
}
