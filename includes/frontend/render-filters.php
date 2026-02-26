<?php
// File: /includes/frontend/render-filters.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend renderer + shortcodes for WooCommerce Advanced Filters kdr
 */
class WAFKDR_Render_Filters {

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes_kdr(): void {
		add_shortcode( 'wafkdr_filters', [ $this, 'shortcode_filters_kdr' ] );
		add_shortcode( 'wafkdr_filter_set', [ $this, 'shortcode_filter_set_kdr' ] ); // future: filter set by id
	}

	/**
	 * Main shortcode: [wafkdr_filters]
	 *
	 * Attributes:
	 * - layout: sidebar|horizontal|dropdown|modal|sticky (default sidebar)
	 * - target: CSS selector for products container (default .products)
	 * - per_page: products per page override (optional)
	 */
	public function shortcode_filters_kdr( $atts = [] ): string {
		if ( ! function_exists( 'WC' ) ) {
			return '';
		}

		$atts = shortcode_atts(
			[
				'layout'   => 'sidebar',
				'target'   => '.products',
				'per_page' => '',
			],
			(array) $atts,
			'wafkdr_filters'
		);

		$layout   = sanitize_key( (string) $atts['layout'] );
		$target   = sanitize_text_field( (string) $atts['target'] );
		$per_page = $atts['per_page'] !== '' ? absint( $atts['per_page'] ) : 0;

		// Enqueue assets only when shortcode is used.
		$this->enqueue_shortcode_assets_kdr( $layout );

		$context = [
			'layout'   => $layout,
			'target'   => $target,
			'per_page' => $per_page,
		];

		return $this->render_filters_shell_kdr( $context );
	}

	/**
	 * Future shortcode: [wafkdr_filter_set id="123"]
	 * For v1 it behaves like [wafkdr_filters] but keeps API stable.
	 */
	public function shortcode_filter_set_kdr( $atts = [] ): string {
		$atts = shortcode_atts(
			[
				'id'       => 0,
				'layout'   => 'sidebar',
				'target'   => '.products',
				'per_page' => '',
			],
			(array) $atts,
			'wafkdr_filter_set'
		);

		// For now, ignore id (Filter Sets come in admin phase).
		return $this->shortcode_filters_kdr( $atts );
	}

	/**
	 * Enqueue assets when shortcode is present.
	 */
	private function enqueue_shortcode_assets_kdr( string $layout ): void {
		/**
		 * We'll rely on core assets class to enqueue the actual files.
		 * Here we only set a flag for conditional enqueue if you implement it later.
		 */
		if ( ! wp_script_is( 'wafkdr-frontend-kdr', 'enqueued' ) ) {
			// Provide a basic inline config even before JS file exists.
			wp_register_script( 'wafkdr-frontend-kdr', '', [], WAFKDR_VERSION, true );
			wp_enqueue_script( 'wafkdr-frontend-kdr' );

			wp_localize_script(
				'wafkdr-frontend-kdr',
				'WAFKDR_DATA',
				[
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'wafkdr_filter_nonce_kdr' ),
					'layout'  => $layout,
				]
			);
		}

		if ( ! wp_style_is( 'wafkdr-frontend-kdr', 'enqueued' ) ) {
			wp_register_style( 'wafkdr-frontend-kdr', '', [], WAFKDR_VERSION );
			wp_enqueue_style( 'wafkdr-frontend-kdr' );
		}
	}

	/**
	 * Render the UI shell (filter panel + results wrapper).
	 * In next steps we will inject actual filter templates.
	 */
	private function render_filters_shell_kdr( array $context ): string {
		$layout   = $context['layout'] ?? 'sidebar';
		$target   = $context['target'] ?? '.products';
		$per_page = isset( $context['per_page'] ) ? (int) $context['per_page'] : 0;

		ob_start();
		?>
		<div class="wafkdr-wrap wafkdr-layout-<?php echo esc_attr( $layout ); ?>"
		     data-layout="<?php echo esc_attr( $layout ); ?>"
		     data-target="<?php echo esc_attr( $target ); ?>"
		     data-per-page="<?php echo esc_attr( (string) $per_page ); ?>">

			<div class="wafkdr-filters">
				<div class="wafkdr-filters__header">
					<span class="wafkdr-title"><?php echo esc_html__( 'Filters', 'woocommerce-advanced-filters-kdr' ); ?></span>
					<button type="button" class="wafkdr-clear" data-action="clear">
						<?php echo esc_html__( 'Clear', 'woocommerce-advanced-filters-kdr' ); ?>
					</button>
				</div>

				<div class="wafkdr-filters__body">
					<!-- Next step: render actual filters here (category, attributes, etc.) -->
					<div class="wafkdr-placeholder">
						<?php echo esc_html__( 'Filter UI will render here (next step).', 'woocommerce-advanced-filters-kdr' ); ?>
					</div>
				</div>

				<div class="wafkdr-active-filters" aria-live="polite">
					<!-- Next step: active filter tags/chips -->
				</div>
			</div>

			<div class="wafkdr-results">
				<div class="wafkdr-results__status" aria-live="polite"></div>
				<div class="wafkdr-results__container">
					<!-- We do not output products here; we update an existing products loop on the page via AJAX -->
				</div>
			</div>

		</div>
		<?php
		return (string) ob_get_clean();
	}
}