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
		add_action( 'wc_ajax_ajax_process_checkout', array( $this, 'pdfw_ajax_process_checkout' ) );
		add_action( 'wc_ajax_nopriv_ajax_process_checkout', array( $this, 'pdfw_ajax_process_checkout' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'pfw_frontend_enqueue_scripts' ) );
	}

	
	// Callback function for frontend styles and scripts
	public function pfw_frontend_enqueue_scripts() {
		wp_enqueue_style( 'pfw-style', PADDLE_WOO_URL . 'assets/css/frontend-style.css' );
		
		$paddle_w = new \WC_Pdfw();
		$endpoint = is_wc_endpoint_url('order-pay') ? '?wc-ajax=ajax_process_payment' : '?wc-ajax=ajax_process_checkout';
		wp_enqueue_script( 'paddle', 'https://cdn.paddle.com/paddle/paddle.js', array('jquery'));
		wp_enqueue_script( 'pfw-script', PADDLE_WOO_URL . 'assets/js/frontend-script.js', ('jquery') );
		wp_localize_script('pfw-script','Pdfw', array(
		'process_checkout'=> home_url( '/'.$endpoint),
		'vendor_id'=> $paddle_w->vendor_id
	));
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

	/**
	 * WooCommerce Checkout ajax callback.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function pdfw_ajax_process_checkout() {
		WC()->checkout()->process_checkout();
		// hit "process_payment()"
	}
}
