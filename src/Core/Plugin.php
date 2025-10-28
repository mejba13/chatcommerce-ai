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
		$this->init_hooks();
		$this->init_components();
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

		// Enqueue widget CSS.
		wp_enqueue_style(
			'chatcommerce-ai-widget',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/widget.css',
			array(),
			CHATCOMMERCE_AI_VERSION
		);

		// Enqueue widget JS.
		wp_enqueue_script(
			'chatcommerce-ai-widget',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/js/widget.js',
			array(),
			CHATCOMMERCE_AI_VERSION,
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

		// Enqueue admin CSS.
		wp_enqueue_style(
			'chatcommerce-ai-admin',
			CHATCOMMERCE_AI_PLUGIN_URL . 'assets/css/admin.css',
			array( 'wp-components' ),
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
				'apiUrl'   => rest_url( 'chatcommerce/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'settings' => get_option( 'chatcommerce_ai_settings', array() ),
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
