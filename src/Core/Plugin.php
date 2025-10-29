<?php
/**
 * Main Plugin Class
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\Core;

use ChatCommerceAI\Admin\AdminController;
use ChatCommerceAI\API\Router;
use ChatCommerceAI\Widget\WidgetLoader;
use ChatCommerceAI\Sync\SyncManager;

/**
 * Main plugin class - singleton pattern.
 */
class Plugin {
	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Admin controller.
	 *
	 * @var AdminController
	 */
	public $admin;

	/**
	 * API router.
	 *
	 * @var Router
	 */
	public $api;

	/**
	 * Widget loader.
	 *
	 * @var WidgetLoader
	 */
	public $widget;

	/**
	 * Sync manager.
	 *
	 * @var SyncManager
	 */
	public $sync;

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->run_migrations();
		$this->init_hooks();
		$this->init_components();
	}

	/**
	 * Run database migrations.
	 */
	private function run_migrations() {
		$installed_version = get_option( 'chatcommerce_ai_version', '0.0.0' );
		$current_version   = CHATCOMMERCE_AI_VERSION;

		// Skip if already at current version.
		if ( version_compare( $installed_version, $current_version, '>=' ) ) {
			return;
		}

		// Migration to v1.1.0: Add ai_provider for existing OpenAI users.
		if ( version_compare( $installed_version, '1.1.0', '<' ) ) {
			$this->migrate_to_1_1_0();
		}

		// Update version.
		update_option( 'chatcommerce_ai_version', $current_version );
	}

	/**
	 * Migrate to version 1.1.0.
	 * Add ai_provider field for existing OpenAI users.
	 */
	private function migrate_to_1_1_0() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		// If settings exist and ai_provider is not set, but openai_api_key exists.
		if ( ! empty( $settings ) && ! isset( $settings['ai_provider'] ) ) {
			// Set ai_provider to 'openai' for existing installations.
			$settings['ai_provider'] = 'openai';

			// Also set default Hugging Face model for future use.
			if ( ! isset( $settings['hf_model'] ) ) {
				$settings['hf_model'] = 'HuggingFaceH4/zephyr-7b-beta';
			}

			// Update settings.
			update_option( 'chatcommerce_ai_settings', $settings );

			// Log migration.
			error_log( '[ChatCommerce AI] Migration to v1.1.0: Set ai_provider=openai for existing installation' );
		}
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Initialize plugin components.
	 */
	private function init_components() {
		// Admin interface.
		if ( is_admin() ) {
			$this->admin = new AdminController();
		}

		// REST API.
		$this->api = new Router();

		// Frontend widget.
		$this->widget = new WidgetLoader();

		// Sync manager.
		$this->sync = new SyncManager();
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'chatcommerce-ai',
			false,
			dirname( CHATCOMMERCE_AI_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes() {
		$this->api->register_routes();
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		// Check if chatbot is enabled.
		$settings = get_option( 'chatcommerce_ai_settings', array() );
		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		// Enqueue widget design tokens (CSS variables for theming).
		wp_enqueue_style(
			'chatcommerce-ai-widget-tokens',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/widget-tokens.css',
			array(),
			CHATCOMMERCE_AI_VERSION
		);

		// Enqueue compiled Tailwind CSS with custom theme.
		wp_enqueue_style(
			'chatcommerce-ai-widget',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/widget.css',
			array( 'chatcommerce-ai-widget-tokens' ),
			CHATCOMMERCE_AI_VERSION . '.' . time() // Cache bust during development
		);

		// Add x-cloak style to prevent flash of unstyled content.
		wp_add_inline_style(
			'chatcommerce-ai-widget',
			'[x-cloak] { display: none !important; }'
		);

		// Enqueue Alpine.js from CDN.
		wp_enqueue_script(
			'alpinejs',
			'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js',
			array(),
			'3.13.3',
			true
		);

		// Add defer attribute to Alpine.js.
		add_filter( 'script_loader_tag', function( $tag, $handle ) {
			if ( 'alpinejs' === $handle ) {
				return str_replace( ' src', ' defer src', $tag );
			}
			return $tag;
		}, 10, 2 );

		// Enqueue widget JS (Alpine.js component).
		wp_enqueue_script(
			'chatcommerce-ai-widget',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/js/widget-modern.js',
			array(),
			CHATCOMMERCE_AI_VERSION . '.' . time(), // Cache bust
			true
		);

		// Localize script with settings.
		wp_localize_script(
			'chatcommerce-ai-widget',
			'chatcommerceAI',
			array(
				'apiUrl'      => rest_url( 'chatcommerce/v1' ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'settings'    => $this->get_frontend_settings( $settings ),
				'i18n'        => $this->get_i18n_strings(),
			)
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our admin pages.
		if ( strpos( $hook, 'chatcommerce-ai' ) === false ) {
			return;
		}

		// Enqueue design tokens (CSS variables foundation).
		wp_enqueue_style(
			'chatcommerce-ai-design-tokens',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/design-tokens.css',
			array(),
			CHATCOMMERCE_AI_VERSION
		);

		// Enqueue component library.
		wp_enqueue_style(
			'chatcommerce-ai-components',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/components.css',
			array( 'chatcommerce-ai-design-tokens' ),
			CHATCOMMERCE_AI_VERSION
		);

		// Enqueue admin CSS (page-specific styles).
		wp_enqueue_style(
			'chatcommerce-ai-admin',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/admin.css',
			array( 'chatcommerce-ai-components', 'wp-components' ),
			CHATCOMMERCE_AI_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'chatcommerce-ai-admin',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/js/admin.js',
			array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
			CHATCOMMERCE_AI_VERSION,
			true
		);

		// Localize admin script.
		wp_localize_script(
			'chatcommerce-ai-admin',
			'chatcommerceAIAdmin',
			array(
				'apiUrl'       => rest_url( 'chatcommerce/v1' ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'dismissNonce' => wp_create_nonce( 'chatcommerce_ai_dismiss_notice' ),
				'settings'     => get_option( 'chatcommerce_ai_settings', array() ),
			)
		);
	}

	/**
	 * Get frontend settings (sanitized for public use).
	 *
	 * @param array $settings Full settings array.
	 * @return array
	 */
	private function get_frontend_settings( $settings ) {
		return array(
			'position'       => $settings['position'] ?? 'bottom-right',
			'theme'          => array(
				'primaryColor'   => $settings['primary_color'] ?? '#0073aa',
				'backgroundColor' => $settings['bg_color'] ?? '#ffffff',
				'textColor'      => $settings['text_color'] ?? '#000000',
			),
			'brandLogo'      => $settings['brand_logo'] ?? '',
			'welcomeMessage' => $settings['welcome_message'] ?? __( 'Hi! How can I help you today?', 'chatcommerce-ai' ),
			'leadCapture'    => array(
				'enabled' => ! empty( $settings['lead_capture_enabled'] ),
				'fields'  => $settings['lead_fields'] ?? array( 'name', 'email' ),
			),
		);
	}

	/**
	 * Get i18n strings for frontend.
	 *
	 * @return array
	 */
	private function get_i18n_strings() {
		return array(
			'send'               => __( 'Send', 'chatcommerce-ai' ),
			'typing'             => __( 'Typing...', 'chatcommerce-ai' ),
			'placeholder'        => __( 'Type your message...', 'chatcommerce-ai' ),
			'close'              => __( 'Close', 'chatcommerce-ai' ),
			'minimize'           => __( 'Minimize', 'chatcommerce-ai' ),
			'expand'             => __( 'Expand', 'chatcommerce-ai' ),
			'feedbackThumbsUp'   => __( 'Helpful', 'chatcommerce-ai' ),
			'feedbackThumbsDown' => __( 'Not helpful', 'chatcommerce-ai' ),
			'errorGeneric'       => __( 'Something went wrong. Please try again.', 'chatcommerce-ai' ),
			'errorNetwork'       => __( 'Network error. Please check your connection.', 'chatcommerce-ai' ),
		);
	}
}
