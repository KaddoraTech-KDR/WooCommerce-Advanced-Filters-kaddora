<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 * WooCommerce Advanced Filters kdr
 */
final class WAFKDR_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Loader instance.
	 *
	 * @var WAFKDR_Loader
	 */
	public WAFKDR_Loader $loader;

	/**
	 * Plugin slug / textdomain.
	 *
	 * @var string
	 */
	public string $slug = 'woocommerce-advanced-filters-kdr';

	/**
	 * Get singleton instance.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 * Keep it private (singleton).
	 */
	private function __construct() {
		$this->loader = new WAFKDR_Loader();

		$this->define_hooks_kdr();
	}

	/**
	 * Register all hooks via loader.
	 */
	private function define_hooks_kdr(): void {

		// Core assets (frontend/admin enqueue).
		require_once WAFKDR_PLUGIN_DIR . 'includes/core/assets.php';
		$assets = new WAFKDR_Core_Assets();
		$this->loader->add_action( 'wp_enqueue_scripts', $assets, 'enqueue_frontend_kdr', 20 );
		$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'enqueue_admin_kdr', 20 );

		// Frontend rendering (filters output).
		require_once WAFKDR_PLUGIN_DIR . 'includes/frontend/render-filters.php';
		$frontend = new WAFKDR_Render_Filters();
		$this->loader->add_action( 'init', $frontend, 'register_shortcodes_kdr', 20 );

		// AJAX endpoints (filtering).
		require_once WAFKDR_PLUGIN_DIR . 'includes/ajax/ajax-filter.php';
		$ajax = new WAFKDR_Ajax_Filter();
		$this->loader->add_action( 'wp_ajax_wafkdr_filter_products', $ajax, 'handle_kdr', 10 );
		$this->loader->add_action( 'wp_ajax_nopriv_wafkdr_filter_products', $ajax, 'handle_kdr', 10 );

		// Admin UI.
		if ( is_admin() ) {
			require_once WAFKDR_PLUGIN_DIR . 'includes/admin/admin-ui.php';
			$admin = new WAFKDR_Admin_UI();
			$this->loader->add_action( 'admin_menu', $admin, 'register_menu_kdr', 20 );
		}
	}

	/**
	 * Run plugin: attach hooks.
	 */
	public function run(): void {
		$this->loader->run();
	}
}

/**
 * Helper function (global accessor).
 */
function wafkdr_plugin_kdr(): WAFKDR_Plugin {
	return WAFKDR_Plugin::instance();
}