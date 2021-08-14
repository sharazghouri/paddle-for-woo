<?php

class WC_Pdfw extends WC_Payment_Gateway {


	/**
	 * Class constructor exectue when object created.
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id                 = 'woo_paddle';
		$this->method_title       = __( 'Paddle', 'pfwoo' );
		$this->method_description = __( 'WooCommerce Payment Gateway for digital products.', 'pfwoo' );
		$this->title              = __( 'Woo Paddle', 'pfwoo' );
		$this->has_fields         = false;

		$this->supports = array( 'products' );
		$this->init_form_fields();
		$this->init_settings();

			// Define user set variables
			$this->title            = $this->get_option( 'title' );
			$this->description      = $this->get_option( 'description' );
			$this->vendor_id        = $this->get_option( 'vendor_id' );
			$this->vendor_auth_code = $this->get_option( 'vendor_auth_code' );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Paddle setting fields.
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function init_form_fields() {
		if ( $this->get_option( 'vendor_id' ) && $this->get_option( 'vendor_auth_code' ) ) {
			$connection_button = '<p style=\'color:green\'>Your paddle account has already been connected</p>' .
			'<a class=\'button-primary open_paddle_integration_window\'>' . esc_html__( 'Reconnect your Paddle Account', 'pfwoo' ) . '</a>';
		} else {
			$connection_button = '<a class=\'button-primary open_paddle_integration_window\'>' . esc_html__( 'Connect your Paddle Account', 'pfwoo' ) . '</a>';
		}

		$this->form_fields = array(

			'enabled'          => array(
				'title'   => esc_html__( 'Enable/Disable', 'pfwoo' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable', 'pfwoo' ),
				'default' => 'yes',
			),

			'title'            => array(
				'title'       => esc_html__( 'Title', 'pfwoo' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title for the payment method the customer sees during checkout.', 'paddle' ),
				'default'     => esc_html__( 'Paddle', 'pfwoo' ),
				'desc_tip'    => true,
			),

			'paddle_showlink'  => array(
				'title'   => 'Vendor Account',
				'content' => $connection_button . '<br /><p class = "description"><a href="#!" id=\'manualEntry\'>' . esc_html__( 'Click here to enter your account details manually', 'pfwoo' ) . '</a></p>',
				'type'    => 'raw',
			),

			'vendor_id'        => array(
				'title'       => esc_html__( 'Vendor ID', 'pfwoo' ),
				'type'        => 'text',
				'description' => '<a href="https://vendors.paddle.com/authentication" target="_blank">' . esc_html__( 'Get Vendor ID.', 'pfwoo' ) . '</a>',
			),

			'vendor_auth_code' => array(
				'title'       => esc_html__( 'Vendor Auth Code', 'pfwoo' ),
				'type'        => 'text',
				'description' => '<a href="https://vendors.paddle.com/authentication" target="_blank">' . esc_html__( 'Get Auth Code.', 'pfwoo' ) . '</a>',
			),
			'description'      => array(
				'title'       => esc_html__( 'Description', 'pfwoo' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'pfwoo' ),
				'default'     => esc_html__( 'Pay using Visa, Mastercard, Maestro, American Express, Discover, Diners Club, JCB, UnionPay, Mada or PayPal via Paddle', 'pfwoo' ),
			),
		);
	}
}
