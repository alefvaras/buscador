<?php
/**
 * Logger class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Recover Abandoned Cart
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class YITH_RAC_Logger
 */
class YITH_RAC_Logger {

	/**
	 * WC logger instance
	 *
	 * @var WC_Logger
	 */
	public static $logger = null;

	/**
	 * Init the WC Logger
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
		if ( is_null( self::$logger ) && class_exists( 'WC_Logger' ) ) {
			self::$logger = new WC_Logger();
		}
	}

	/**
	 * Log a message
	 *
	 * @since 1.0.0
	 * @param string $message The message to log.
	 * @param string $type The message type.
	 * @param string $context Optional context for the log; distinct contexts produce distinct log files.
	 * @return void
	 */
	public static function log( $message, $type = 'error', $context = '' ) {
		self::init();

		$source = 'yith-wrac-log';

		if ( $context ) {
			$source .= "-$context";
		}

		if ( ! is_null( self::$logger ) ) {
			self::$logger->log(
				$type,
				$message,
				array(
					'source' => $source,
				)
			);
		}
	}
}
