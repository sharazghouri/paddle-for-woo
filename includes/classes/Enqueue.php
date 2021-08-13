<?php
/**
 *  Plugin main class for adding paddle payment gateway
 * @package Pdfw
 * @subpackage Padfw/Classes
 * @since 1.0.0
 */

namespace Pdfw\Classes;

class Enqueue {
	public function register() {

		add_action( 'wp_enqueue_scripts', array( $this, 'pfw_frontend_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'pfw_admin_enqueue_scripts' ) );
	}

	// Callback function for frontend styles and scripts
	public function pfw_frontend_enqueue_scripts() {
		wp_enqueue_style( 'pfw-style', PADDLE_WOO_URL . 'assets/css/frontend-style.css' );
		wp_enqueue_script( 'pn-script', PADDLE_WOO_URL . 'assets/js/frontend-script.js' );
	}

	// Callback function for admin styles and scripts
	public function pfw_admin_enqueue_scripts() {
		wp_enqueue_style( 'pfw-admin-style', PADDLE_WOO_URL . 'assets/css/admin-style.css' );
		wp_enqueue_script( 'pfw-admin-script', PADDLE_WOO_URL . 'assets/js/admin-script.js' );
	}
}
