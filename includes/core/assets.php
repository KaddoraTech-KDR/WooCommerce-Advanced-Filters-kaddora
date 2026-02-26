<?php
// File: /includes/core/assets.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles frontend/admin assets for WooCommerce Advanced Filters kdr
 */
class WAFKDR_Core_Assets {

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_kdr(): void {
		$js_rel  = 'assets/js/frontend.js';
		$css_rel = 'assets/css/frontend.css';

		$js_path  = WAFKDR_PLUGIN_DIR . $js_rel;
		$css_path = WAFKDR_PLUGIN_DIR . $css_rel;

		// JS
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'wafkdr-frontend-kdr',
				WAFKDR_PLUGIN_URL . $js_rel,
				[ 'jquery' ],
				WAFKDR_VERSION,
				true
			);
		} else {
			// Safe fallback: register empty handle so localize_script works
			wp_register_script( 'wafkdr-frontend-kdr', '', [ 'jquery' ], WAFKDR_VERSION, true );
			wp_enqueue_script( 'wafkdr-frontend-kdr' );
		}

		// CSS
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'wafkdr-frontend-kdr',
				WAFKDR_PLUGIN_URL . $css_rel,
				[],
				WAFKDR_VERSION
			);
		} else {
			wp_register_style( 'wafkdr-frontend-kdr', '', [], WAFKDR_VERSION );
			wp_enqueue_style( 'wafkdr-frontend-kdr' );
		}

		// Localize (AJAX config)
		wp_localize_script(
			'wafkdr-frontend-kdr',
			'WAFKDR_DATA',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wafkdr_filter_nonce_kdr' ),
			]
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_kdr(): void {
		$css_rel = 'assets/css/admin.css';
		$js_rel  = 'assets/js/admin.js';

		$css_path = WAFKDR_PLUGIN_DIR . $css_rel;
		$js_path  = WAFKDR_PLUGIN_DIR . $js_rel;

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'wafkdr-admin-kdr',
				WAFKDR_PLUGIN_URL . $css_rel,
				[],
				WAFKDR_VERSION
			);
		}

		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'wafkdr-admin-kdr',
				WAFKDR_PLUGIN_URL . $js_rel,
				[ 'jquery' ],
				WAFKDR_VERSION,
				true
			);
		}
	}
}