<?php
/**
 *
 * @package plugin-new
 * @since 1.0.0
 */

 namespace Pdfw;

class Init {
	 /**
	  * Store all the classes inside an array
	  *
	  * @return array Full list of classes
	  */
	public static function get_services() {
		return [
			Classes\Enqueue::class,
		];
	}

	/**
	 * Loop through the classes, initialize them,
	 * and call the register() method if it exists
	 *
	 * @return
	 */
	public function __construct() {
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}

		add_action( 'plugins_loaded', array( $this, 'init_pdfw_gateway_class' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_pdfw_gateway_class' ) );
	}

	/**
	 * Initialize the class
	 *
	 * @param  class $class class from the services array
	 * @return class instance new instance of the class
	 */
	private static function instantiate( $class ) {
		 $service = new $class();
		return $service;
	}

	/**
	 * Init our payment gateway class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_pdfw_gateway_class() {
		require_once PADDLE_WOO_INC . 'classes/WC_Pdfw.php';
	}

	/**
	 * Tell WooCommerce about our payment gateway class.
	 *
	 * @since 1.0.0
	 * @param array $methods already payment gateway mapped classes.
	 * @return array $methods
	 */
	public function add_pdfw_gateway_class( $methods ) {
		$methods[] = 'WC_Pdfw';
		return $methods;
	}
}
