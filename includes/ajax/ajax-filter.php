<?php
// File: /includes/ajax/ajax-filter.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler for WooCommerce Advanced Filters kdr
 */
class WAFKDR_Ajax_Filter {

	/**
	 * Main AJAX callback.
	 * Action:
	 * - wp_ajax_wafkdr_filter_products
	 * - wp_ajax_nopriv_wafkdr_filter_products
	 */
	public function handle_kdr(): void {

		// Nonce check.
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( (string) $_REQUEST['nonce'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! wp_verify_nonce( $nonce, 'wafkdr_filter_nonce_kdr' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Security check failed.', 'woocommerce-advanced-filters-kdr' ),
				],
				403
			);
		}

		// Ensure WooCommerce context.
		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'WooCommerce is not available.', 'woocommerce-advanced-filters-kdr' ),
				],
				400
			);
		}

		/**
		 * Payload: we accept a "filters" array for future structured requests.
		 * For now, accept either:
		 * - filters[category][]=slug
		 * - category=slug1,slug2 (compat)
		 * - s=search
		 */
		$filters = [];
		if ( isset( $_REQUEST['filters'] ) && is_array( $_REQUEST['filters'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters = $this->sanitize_filters_kdr( $_REQUEST['filters'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} else {
			// Fallback: use the whole request as potential filter source.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters = $this->sanitize_filters_kdr( $_REQUEST );
		}

		$page     = isset( $_REQUEST['page'] ) ? max( 1, absint( $_REQUEST['page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$per_page = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Build query args (placeholder now; next step will use Query Builder + filter classes).
		$query_args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page > 0 ? $per_page : (int) get_option( 'posts_per_page' ),
			'tax_query'      => [],
			'meta_query'     => [],
			's'              => isset( $filters['search'] ) ? (string) $filters['search'] : '',
		];

		// Very basic v0 examples (safe starter):
		// Category filtering by product_cat slugs.
		if ( ! empty( $filters['category'] ) ) {
			$cats = is_array( $filters['category'] ) ? $filters['category'] : [ (string) $filters['category'] ];
			$cats = array_values( array_filter( array_map( 'sanitize_title', $cats ) ) );

			if ( ! empty( $cats ) ) {
				$query_args['tax_query'][] = [
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $cats,
					'operator' => 'IN',
				];
			}
		}

		// Stock status filtering (instock/outofstock/onbackorder).
		if ( ! empty( $filters['stock'] ) ) {
			$stock = sanitize_key( (string) $filters['stock'] );
			if ( in_array( $stock, [ 'instock', 'outofstock', 'onbackorder' ], true ) ) {
				$query_args['meta_query'][] = [
					'key'     => '_stock_status',
					'value'   => $stock,
					'compare' => '=',
				];
			}
		}

		// Run query.
		$q = new WP_Query( $query_args );

		// Render products HTML using Woo templates.
		$products_html = $this->render_products_html_kdr( $q );

		// Placeholder: counts, active tags, etc (next steps).
		$response = [
			'productsHtml' => $products_html,
			'foundPosts'   => (int) $q->found_posts,
			'maxPages'     => (int) $q->max_num_pages,
			'page'         => (int) $page,
			'filters'      => $filters,
		];

		wp_send_json_success( $response );
	}

	/**
	 * Sanitize filter payload recursively (strings + arrays only).
	 */
	private function sanitize_filters_kdr( $raw ): array {
		$filters = [];

		if ( ! is_array( $raw ) ) {
			return $filters;
		}

		foreach ( $raw as $key => $value ) {
			$k = sanitize_key( (string) $key );

			// Ignore noise keys commonly present in AJAX requests.
			if ( in_array( $k, [ 'action', 'nonce', '_wpnonce', '_wp_http_referer', 'page', 'per_page' ], true ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$clean = [];
				foreach ( $value as $v ) {
					if ( is_scalar( $v ) ) {
						$vv = sanitize_text_field( (string) $v );

						// Support comma-separated strings.
						if ( false !== strpos( $vv, ',' ) ) {
							$parts = array_filter( array_map( 'trim', explode( ',', $vv ) ) );
							foreach ( $parts as $p ) {
								$clean[] = sanitize_text_field( (string) $p );
							}
						} else {
							$clean[] = $vv;
						}
					}
				}
				$filters[ $k ] = array_values( array_filter( $clean, static fn( $x ) => $x !== '' ) );
			} else {
				$vv = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
				$filters[ $k ] = $vv;
			}
		}

		// Back-compat alias: allow "s" to map to "search"
		if ( empty( $filters['search'] ) && ! empty( $filters['s'] ) ) {
			$filters['search'] = $filters['s'];
			unset( $filters['s'] );
		}

		return $filters;
	}

	/**
	 * Render products loop HTML safely.
	 */
	private function render_products_html_kdr( WP_Query $q ): string {
		if ( ! function_exists( 'wc_get_template_part' ) ) {
			return '';
		}

		ob_start();

		if ( $q->have_posts() ) {
			woocommerce_product_loop_start();

			while ( $q->have_posts() ) {
				$q->the_post();
				wc_get_template_part( 'content', 'product' );
			}

			woocommerce_product_loop_end();
		} else {
			// Default Woo "no products found" template.
			wc_no_products_found();
		}

		wp_reset_postdata();

		return (string) ob_get_clean();
	}
}