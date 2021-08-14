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
		$this->icon               = PADDLE_WOO_URL . '/assets/images/paddle.png';
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

		$this->form_fields = array(

			'title'            => array(
				'title'       => esc_html__( 'Title', 'pfwoo' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title for the payment method the customer sees during checkout.', 'paddle' ),
				'default'     => esc_html__( 'Paddle', 'pfwoo' ),
				'desc_tip'    => true,
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

	/**
	 * Process the order payment.
	 *
	 * @uses Execute ajax_process_checkout
	 * @since 1.0.0
	 * @param int $order_id order id.
	 * @return void
	 */
	public function process_payment( $order_id ) {

		$order = new WC_Order( $order_id );

		foreach ( $order->get_items() as $item ) {
			$product_name[] = $item->get_name();
			$product_id     = $item->get_product_id();
		}

		$response = wp_remote_retrieve_body(
			wp_remote_post(
				'https://vendors.paddle.com/api/2.0/product/generate_pay_link',
				array(
					'method'      => 'POST',
					'timeout'     => 30,
					'httpversion' => '1.1',
					'body'        => array(
						'vendor_id'        => $this->get_option( 'vendor_id' ),
						'vendor_auth_code' => $this->get_option( 'vendor_auth_code' ),
						'title'            => implode( ', ', $product_name ),
						'image_url'        => wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), array( '220', '220' ), true )[0],
						'prices'           => array( get_woocommerce_currency() . ':' . $order->get_total() ),
						'customer_email'   => $order->get_billing_email(),
						'return_url'       => $order->get_checkout_order_received_url(),
						'webhook_url'      => get_bloginfo( 'url' ) . '/wc-api/' . $this->id . '?order_id=' . $order_id,

					),
				)
			)
		);

		$api_response = json_decode( $response );
		wc_add_notice( json_decode( get_bloginfo( 'url' ) . '/wc-api/' . $this->id . '?order_id=' . $order_id ) );

		if ( $api_response && $api_response->success === true ) {
			// We got a valid response
			echo json_encode(
				array(
					'result'       => 'success',
					'order_id'     => $order->get_id(),
					'checkout_url' => $api_response->response->url,
					'email'        => $order->get_billing_email(),
				)
			);
			// Exit is important
			exit;
		} else {
			// We got a response, but it was an error response
			wc_add_notice( __( 'Something went wrong getting checkout url. Check if gateway is integrated.', 'pfwoo' ), 'error' );
			if ( is_object( $api_response ) ) {
				error_log( __( 'Paddle error. Error response from API. Method: ' . __METHOD__ . ' Errors: ', 'pfwoo' ) . print_r( $api_response->error, true ) );
			} else {
				error_log( __( 'Paddle error. Error response from API. Method: ' . __METHOD__ . ' Response: ', 'pfwoo' ) . print_r( $response, true ) );
			}
			return json_encode(
				array(
					'result' => 'failure',
					'errors' => __( 'Something went wrong. Check if Paddle account is properly integrated.', 'pfwoo' ),
				)
			);
		}
	}


	/**
	 * Callback webhook after payment process complete.
	 *
	 * @uses "https://localhost.com/wc-api/woo_paddle?order_id=xx"
	 * @since 1.0.0
	 * @return void
	 */
	public function webhook_response() {

		$public_key_response = wp_remote_retrieve_body(
			wp_remote_post(
				'https://vendors.paddle.com/api/2.0/user/get_public_key',
				array(
					'method'      => 'POST',
					'timeout'     => 30,
					'httpversion' => '1.1',
					'body'        => array(
						'vendor_id'        => $this->get_option( 'vendor_id' ),
						'vendor_auth_code' => $this->get_option( 'vendor_auth_code' ),
					),
				)
			)
		);

		$api_response_public_key = json_decode( $public_key_response );
		$public_key              = $api_response_public_key->response->public_key;

		if ( $api_response_public_key->success === true ) {

			if ( empty( $public_key ) ) {
							error_log( __( 'Paddle error. Unable to verify webhook callback - vendor_public_key is not set.', 'pfwoo' ) );
				return -1;
			}

			// Copy get input to separate variable to not modify superglobal array
			$webhook_data = $_POST;
			foreach ( $webhook_data as $k => $v ) {
				$webhook_data[ $k ] = stripslashes( $v );
			}

			// Pop signature from webhook data
			$signature = base64_decode( $webhook_data['p_signature'] );
			unset( $webhook_data['p_signature'] );

			// Check signature and return result
			ksort( $webhook_data );
			$data = serialize( $webhook_data );

			// Verify the signature
			$verification = openssl_verify( $data, $signature, $public_key, OPENSSL_ALGO_SHA1 );

			if ( $verification == 1 ) {
				$order_id = sanitize_text_field( $_GET['order_id'] );
				if ( is_numeric( $order_id ) && (int) $order_id == $order_id ) {
					$order = new WC_Order( $order_id );
					if ( is_object( $order ) && $order instanceof WC_Order ) {
						$order->payment_complete();
						status_header( 200 );
						exit;
					} else {
						error_log( __( 'Paddle error. Unable to complete payment - order ', 'pfwoo' ) . $order_id . __( ' does not exist', 'pfwoo' ) );
					}
				} else {
					error_log( __( 'Paddle error. Unable to complete payment - order_id is not integer. Got \'', 'pfwoo' ) . $order_id . '\'.' );
				}
			} else {
				error_log( __( 'The signature is invalid!', 'pfwoo' ) );
			}
		}
	}
}
