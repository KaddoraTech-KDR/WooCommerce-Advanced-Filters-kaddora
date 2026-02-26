<?php
// File: /includes/admin/admin-ui.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin UI for WooCommerce Advanced Filters kdr
 * Creates menu pages and a shortcode generator (v1).
 */
class WAFKDR_Admin_UI {

	/**
	 * Parent slug for menu.
	 *
	 * @var string
	 */
	private string $menu_slug = 'wafkdr-settings-kdr';

	/**
	 * Register admin menu.
	 */
	public function register_menu_kdr(): void {

		add_menu_page(
			__( 'Advanced Filters kdr', 'woocommerce-advanced-filters-kdr' ),
			__( 'Filters kdr', 'woocommerce-advanced-filters-kdr' ),
			'manage_woocommerce',
			$this->menu_slug,
			[ $this, 'render_settings_page_kdr' ],
			'dashicons-filter',
			56
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Settings', 'woocommerce-advanced-filters-kdr' ),
			__( 'Settings', 'woocommerce-advanced-filters-kdr' ),
			'manage_woocommerce',
			$this->menu_slug,
			[ $this, 'render_settings_page_kdr' ]
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Shortcodes', 'woocommerce-advanced-filters-kdr' ),
			__( 'Shortcodes', 'woocommerce-advanced-filters-kdr' ),
			'manage_woocommerce',
			'wafkdr-shortcodes-kdr',
			[ $this, 'render_shortcodes_page_kdr' ]
		);

		// Future:
		// add_submenu_page(... Filter Sets / Layout Builder ...)
		// add_submenu_page(... Analytics ...)
	}

	/**
	 * Settings page (v1 placeholder).
	 */
	public function render_settings_page_kdr(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'WooCommerce Advanced Filters kdr', 'woocommerce-advanced-filters-kdr' ); ?></h1>

			<p><?php echo esc_html__( 'This is the settings area. Next steps will add Filter Sets, Layout Builder, Smart Filtering, SEO settings, and Analytics.', 'woocommerce-advanced-filters-kdr' ); ?></p>

			<h2><?php echo esc_html__( 'Quick Start', 'woocommerce-advanced-filters-kdr' ); ?></h2>
			<ol>
				<li><?php echo esc_html__( 'Add the shortcode to your Shop page (or any page that shows products).', 'woocommerce-advanced-filters-kdr' ); ?></li>
				<li><?php echo esc_html__( 'Use AJAX filtering for instant results without reload.', 'woocommerce-advanced-filters-kdr' ); ?></li>
			</ol>

			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wafkdr-shortcodes-kdr' ) ); ?>">
					<?php echo esc_html__( 'Open Shortcode Generator', 'woocommerce-advanced-filters-kdr' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Shortcodes page (v1 generator).
	 */
	public function render_shortcodes_page_kdr(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$layout = isset( $_GET['layout'] ) ? sanitize_key( (string) $_GET['layout'] ) : 'sidebar'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$target = isset( $_GET['target'] ) ? sanitize_text_field( (string) $_GET['target'] ) : '.products'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$pp     = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$shortcode = '[wafkdr_filters';
		$shortcode .= ' layout="' . esc_attr( $layout ) . '"';
		$shortcode .= ' target="' . esc_attr( $target ) . '"';
		if ( $pp > 0 ) {
			$shortcode .= ' per_page="' . esc_attr( (string) $pp ) . '"';
		}
		$shortcode .= ']';
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Shortcodes', 'woocommerce-advanced-filters-kdr' ); ?></h1>

			<p><?php echo esc_html__( 'Generate a shortcode and paste it into a page (Shop page recommended).', 'woocommerce-advanced-filters-kdr' ); ?></p>

			<form method="get" action="">
				<input type="hidden" name="page" value="wafkdr-shortcodes-kdr" />

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="wafkdr-layout"><?php echo esc_html__( 'Layout', 'woocommerce-advanced-filters-kdr' ); ?></label>
							</th>
							<td>
								<select name="layout" id="wafkdr-layout">
									<?php
									$layouts = [
										'sidebar'     => __( 'Sidebar', 'woocommerce-advanced-filters-kdr' ),
										'horizontal'  => __( 'Horizontal Bar', 'woocommerce-advanced-filters-kdr' ),
										'dropdown'    => __( 'Dropdowns', 'woocommerce-advanced-filters-kdr' ),
										'modal'       => __( 'Mobile Modal', 'woocommerce-advanced-filters-kdr' ),
										'sticky'      => __( 'Sticky Filters', 'woocommerce-advanced-filters-kdr' ),
									];
									foreach ( $layouts as $key => $label ) {
										printf(
											'<option value="%1$s" %2$s>%3$s</option>',
											esc_attr( $key ),
											selected( $layout, $key, false ),
											esc_html( $label )
										);
									}
									?>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="wafkdr-target"><?php echo esc_html__( 'Products Container Selector', 'woocommerce-advanced-filters-kdr' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text" id="wafkdr-target" name="target" value="<?php echo esc_attr( $target ); ?>" />
								<p class="description">
									<?php echo esc_html__( 'Default is .products (WooCommerce loop). Change only if your theme uses a different selector.', 'woocommerce-advanced-filters-kdr' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="wafkdr-per-page"><?php echo esc_html__( 'Products Per Page (optional)', 'woocommerce-advanced-filters-kdr' ); ?></label>
							</th>
							<td>
								<input type="number" min="0" class="small-text" id="wafkdr-per-page" name="per_page" value="<?php echo esc_attr( (string) $pp ); ?>" />
								<p class="description">
									<?php echo esc_html__( 'Leave 0 to use the current WooCommerce setting.', 'woocommerce-advanced-filters-kdr' ); ?>
								</p>
							</td>
						</tr>

					</tbody>
				</table>

				<?php submit_button( __( 'Generate', 'woocommerce-advanced-filters-kdr' ) ); ?>
			</form>

			<h2><?php echo esc_html__( 'Your Shortcode', 'woocommerce-advanced-filters-kdr' ); ?></h2>
			<p>
				<input type="text" class="large-text code" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select();" />
			</p>

			<p class="description">
				<?php echo esc_html__( 'Paste it into the Shop page or any page where WooCommerce products appear.', 'woocommerce-advanced-filters-kdr' ); ?>
			</p>

			<h2><?php echo esc_html__( 'Available Shortcodes (v1)', 'woocommerce-advanced-filters-kdr' ); ?></h2>
			<ul>
				<li><code>[wafkdr_filters]</code> — <?php echo esc_html__( 'Main filters UI.', 'woocommerce-advanced-filters-kdr' ); ?></li>
				<li><code>[wafkdr_filter_set id="123"]</code> — <?php echo esc_html__( 'Reserved for Filter Sets (coming next).', 'woocommerce-advanced-filters-kdr' ); ?></li>
			</ul>
		</div>
		<?php
	}
}