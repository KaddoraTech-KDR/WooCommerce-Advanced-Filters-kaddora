<?php
/**
 * Plugin Name: WooCommerce Advanced Filters kaddora
 * Description: Modern, scalable, AJAX-powered WooCommerce product filters (price, category, attributes, rating, stock, search). It is modern, market ready advanced WooCommerce filter add-on plugin.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: kaddoraTech
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-advanced-filters-kaddora
 * Domain Path: /languages
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constants
 */
define( 'WAFKDR_VERSION', '1.0.0' );
define( 'WAFKDR_PLUGIN_FILE', __FILE__ );
define( 'WAFKDR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WAFKDR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WAFKDR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Minimum requirements
 */
define( 'WAFKDR_MIN_PHP', '7.4' );
define( 'WAFKDR_MIN_WP', '6.2' );

/**
 * Autoload core files (simple includes for now)
 */
require_once WAFKDR_PLUGIN_DIR . 'includes/core/helpers.php';

/**
 * Check requirements and WooCommerce dependency
 */
function wafkdr_meets_requirements_kdr(): bool {
	global $wp_version;

	if ( version_compare( PHP_VERSION, WAFKDR_MIN_PHP, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, WAFKDR_MIN_WP, '<' ) ) {
		return false;
	}

	// WooCommerce must be active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}

	return true;
}

/**
 * Admin notice helper
 */
function wafkdr_admin_notice_kdr( string $message, string $type = 'error' ): void {
	if ( ! is_admin() ) {
		return;
	}

	add_action(
		'admin_notices',
		static function () use ( $message, $type ) {
			printf(
				'<div class="notice notice-%1$s"><p>%2$s</p></div>',
				esc_attr( $type ),
				wp_kses_post( $message )
			);
		}
	);
}

/**
 * Bootstrap plugin
 */
function wafkdr_bootstrap_kdr(): void {
	if ( ! wafkdr_meets_requirements_kdr() ) {
		$php_ok = version_compare( PHP_VERSION, WAFKDR_MIN_PHP, '>=' );

		$msg_parts = array();

		if ( ! $php_ok ) {
			$msg_parts[] = sprintf(
				/* translators: 1: required PHP version, 2: current PHP version */
				esc_html__( 'WooCommerce Advanced Filters kdr requires PHP %1$s or higher. Your site is running PHP %2$s.', 'woocommerce-advanced-filters-kdr' ),
				esc_html( WAFKDR_MIN_PHP ),
				esc_html( PHP_VERSION )
			);
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			$msg_parts[] = esc_html__( 'WooCommerce Advanced Filters kdr requires WooCommerce to be installed and active.', 'woocommerce-advanced-filters-kdr' );
		}

		if ( empty( $msg_parts ) ) {
			$msg_parts[] = esc_html__( 'WooCommerce Advanced Filters kdr requirements are not met.', 'woocommerce-advanced-filters-kdr' );
		}

		wafkdr_admin_notice_kdr( implode( ' ', $msg_parts ), 'error' );
		return;
	}

	// Load translations.
	add_action(
		'plugins_loaded',
		static function () {
			load_plugin_textdomain( 'woocommerce-advanced-filters-kdr', false, dirname( WAFKDR_PLUGIN_BASENAME ) . '/languages' );
		}
	);

	// Load core classes.
	require_once WAFKDR_PLUGIN_DIR . 'includes/core/loader.php';
	require_once WAFKDR_PLUGIN_DIR . 'includes/core/plugin.php';

	// Kick off plugin.
	if ( function_exists( 'wafkdr_plugin_kdr' ) ) {
		wafkdr_plugin_kdr()->run();
	}
}

add_action( 'plugins_loaded', 'wafkdr_bootstrap_kdr', 9 );

/**
 * Activation / Deactivation hooks (kept lightweight)
 */
function wafkdr_activate_kdr(): void {
	// Placeholder: later we can add custom tables (index table), defaults, etc.
	flush_rewrite_rules();
}

function wafkdr_deactivate_kdr(): void {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'wafkdr_activate_kdr' );
register_deactivation_hook( __FILE__, 'wafkdr_deactivate_kdr' );

/**
 * HPOS Compatibility (WooCommerce High-Performance Order Storage)
 * Not required for filtering, but safe to declare compatibility when WC is loaded.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);