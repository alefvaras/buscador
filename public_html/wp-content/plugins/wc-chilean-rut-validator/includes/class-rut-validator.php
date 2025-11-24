<?php
/**
 * RUT Validator Core Class
 *
 * @package WC_Chilean_RUT_Validator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Chilean_RUT_Validator_Core
 */
class WC_Chilean_RUT_Validator_Core {

	/**
	 * Normalize RUT (sin puntos, con guión)
	 *
	 * @param string $rut RUT a normalizar.
	 * @return string RUT normalizado.
	 */
	public function normalize( $rut ) {
		if ( empty( $rut ) ) {
			return '';
		}

		$rut = trim( $rut );
		$rut = str_replace( array( '.', ' ' ), '', $rut );
		$rut = strtoupper( $rut );

		// Agregar guión si no existe.
		if ( strpos( $rut, '-' ) === false && strlen( $rut ) > 1 ) {
			$rut = substr( $rut, 0, -1 ) . '-' . substr( $rut, -1 );
		}

		return $rut;
	}

	/**
	 * Format RUT (con puntos y guión para mostrar)
	 *
	 * @param string $rut RUT a formatear.
	 * @return string RUT formateado.
	 */
	public function format( $rut ) {
		$normalized = $this->normalize( $rut );

		if ( empty( $normalized ) || strpos( $normalized, '-' ) === false ) {
			return $normalized;
		}

		list( $body, $dv ) = explode( '-', $normalized );
		$body = preg_replace( '/[^0-9]/', '', $body );

		if ( empty( $body ) ) {
			return $normalized;
		}

		// Agregar puntos.
		$body = number_format( (int) $body, 0, '', '.' );

		return $body . '-' . $dv;
	}

	/**
	 * Validate RUT usando algoritmo Module 11
	 *
	 * @param string $rut RUT a validar.
	 * @return bool True si es válido.
	 */
	public function validate( $rut ) {
		$normalized = $this->normalize( $rut );

		if ( empty( $normalized ) || strpos( $normalized, '-' ) === false ) {
			return false;
		}

		list( $body, $dv ) = explode( '-', $normalized );

		$body = preg_replace( '/[^0-9]/', '', $body );
		$dv   = strtoupper( preg_replace( '/[^0-9K]/i', '', $dv ) );

		if ( empty( $body ) || empty( $dv ) ) {
			return false;
		}

		// Validar rango.
		$body_int = (int) $body;
		if ( $body_int < 1000000 || $body_int > 99999999 ) {
			return false;
		}

		// Calcular dígito verificador.
		$sum    = 0;
		$factor = 2;

		for ( $i = strlen( $body ) - 1; $i >= 0; $i-- ) {
			$sum += (int) $body[ $i ] * $factor;
			$factor = ( $factor === 7 ) ? 2 : $factor + 1;
		}

		$remainder = $sum % 11;
		$calc      = 11 - $remainder;

		if ( $calc === 11 ) {
			$dv_calc = '0';
		} elseif ( $calc === 10 ) {
			$dv_calc = 'K';
		} else {
			$dv_calc = (string) $calc;
		}

		return $dv_calc === $dv;
	}
}
