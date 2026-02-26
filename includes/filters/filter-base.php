<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Filter Base Class
 * All filters (price/category/attribute/rating/stock/search) must extend this.
 */
abstract class WAFKDR_Filter_Base {

	/**
	 * Unique filter key (used in request vars).
	 * Example: 'category', 'price', 'rating'
	 *
	 * @var string
	 */
	protected string $key;

	/**
	 * Human label shown in UI.
	 *
	 * @var string
	 */
	protected string $label;

	/**
	 * Template slug in /templates (without .php).
	 * Example: 'filter-checkbox', 'filter-dropdown', 'filter-price'
	 *
	 * @var string
	 */
	protected string $template = 'filter-checkbox';

	/**
	 * Filter display type (checkbox, dropdown, range, rating, etc).
	 *
	 * @var string
	 */
	protected string $display_type = 'checkbox';

	/**
	 * Settings for this filter instance (admin-configurable later).
	 *
	 * @var array
	 */
	protected array $settings = [];

	/**
	 * Active value parsed from request.
	 *
	 * @var mixed
	 */
	protected $active_value = null;

	/**
	 * Constructor.
	 *
	 * @param array $settings Filter settings.
	 */
	public function __construct( array $settings = [] ) {
		$this->settings = $settings;

		$this->boot_kdr();

		// Parse request early so render() can show active state.
		$this->active_value = $this->parse_request_kdr( $this->get_request_source_kdr() );
	}

	/**
	 * Boot method for child classes to set key/label/template etc.
	 */
	abstract protected function boot_kdr(): void;

	/**
	 * Return filter key.
	 */
	public function get_key_kdr(): string {
		return $this->key;
	}

	/**
	 * Return label.
	 */
	public function get_label_kdr(): string {
		return $this->label;
	}

	/**
	 * Return template slug.
	 */
	public function get_template_kdr(): string {
		return $this->template;
	}

	/**
	 * Return display type.
	 */
	public function get_display_type_kdr(): string {
		return $this->display_type;
	}

	/**
	 * Return settings.
	 */
	public function get_settings_kdr(): array {
		return $this->settings;
	}

	/**
	 * Return active value parsed from request.
	 */
	public function get_active_value_kdr() {
		return $this->active_value;
	}

	/**
	 * Whether this filter currently has an active value.
	 */
	public function is_active_kdr(): bool {
		if ( is_array( $this->active_value ) ) {
			return ! empty( $this->active_value );
		}
		return null !== $this->active_value && '' !== $this->active_value;
	}

	/**
	 * Render filter HTML (uses templates).
	 *
	 * @param array $context Optional extra data for template.
	 * @return string
	 */
	public function render_kdr( array $context = [] ): string {
		$defaults = [
			'filter'       => $this,
			'key'          => $this->get_key_kdr(),
			'label'        => $this->get_label_kdr(),
			'display_type' => $this->get_display_type_kdr(),
			'settings'     => $this->get_settings_kdr(),
			'active_value' => $this->get_active_value_kdr(),
			'options'      => $this->get_options_kdr(),
		];

		$context = array_merge( $defaults, $context );

		return wafkdr_get_template_kdr( $this->get_template_kdr() . '.php', $context );
	}

	/**
	 * Get selectable options for UI (checkbox/dropdown/rating lists).
	 * Override in child filters where needed.
	 *
	 * @return array
	 */
	public function get_options_kdr(): array {
		return [];
	}

	/**
	 * Apply this filter to the query args.
	 * Must return modified args.
	 *
	 * @param array $args {
	 *   @type array $tax_query
	 *   @type array $meta_query
	 *   @type string $s Search term (optional)
	 *   @type array $post__in
	 *   @type array $post__not_in
	 * }
	 *
	 * @return array
	 */
	abstract public function apply_to_query_args_kdr( array $args ): array;

	/**
	 * Parse filter value from request (GET/POST/AJAX payload).
	 * Override per filter if needed.
	 *
	 * @param array $request
	 * @return mixed
	 */
	public function parse_request_kdr( array $request ) {
		$key = $this->get_key_kdr();

		if ( ! isset( $request[ $key ] ) ) {
			return null;
		}

		$value = $request[ $key ];

		// Normalize arrays from comma-separated strings.
		if ( is_string( $value ) && false !== strpos( $value, ',' ) ) {
			$value = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		}

		// Basic sanitize.
		if ( is_array( $value ) ) {
			$value = array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
		} else {
			$value = sanitize_text_field( (string) $value );
		}

		return $value;
	}

	/**
	 * Where do we read request values from?
	 * - For normal page loads: $_GET
	 * - For AJAX: payload (later we’ll pass explicit array into constructor or setter)
	 *
	 * For now: use $_GET as default.
	 */
	protected function get_request_source_kdr(): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET ) && is_array( $_GET ) ? $_GET : [];
	}

	/**
	 * Helper: ensure args have required keys.
	 */
	protected function normalize_query_args_kdr( array $args ): array {
		if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
			$args['tax_query'] = [];
		}
		if ( ! isset( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) ) {
			$args['meta_query'] = [];
		}
		return $args;
	}
}