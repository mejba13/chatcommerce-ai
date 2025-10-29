<?php
/**
 * Admin Controller
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\Admin;

/**
 * Handles admin interface and settings.
 */
class AdminController {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		add_action( 'admin_head', array( $this, 'hide_woocommerce_notices' ) );
		add_action( 'wp_ajax_chatcommerce_ai_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
	}

	/**
	 * Add admin menu pages.
	 */
	public function add_admin_menu() {
		// Main menu page.
		add_menu_page(
			__( 'ChatCommerce AI', 'chatcommerce-ai' ),
			__( 'ChatCommerce AI', 'chatcommerce-ai' ),
			'manage_options',
			'chatcommerce-ai',
			array( $this, 'render_dashboard_page' ),
			'dashicons-format-chat',
			56
		);

		// Dashboard submenu.
		add_submenu_page(
			'chatcommerce-ai',
			__( 'Dashboard', 'chatcommerce-ai' ),
			__( 'Dashboard', 'chatcommerce-ai' ),
			'manage_options',
			'chatcommerce-ai',
			array( $this, 'render_dashboard_page' )
		);

		// Settings submenu.
		add_submenu_page(
			'chatcommerce-ai',
			__( 'Settings', 'chatcommerce-ai' ),
			__( 'Settings', 'chatcommerce-ai' ),
			'manage_options',
			'chatcommerce-ai-settings',
			array( $this, 'render_settings_page' )
		);

		// Conversations submenu.
		add_submenu_page(
			'chatcommerce-ai',
			__( 'Conversations', 'chatcommerce-ai' ),
			__( 'Conversations', 'chatcommerce-ai' ),
			'manage_options',
			'chatcommerce-ai-conversations',
			array( $this, 'render_conversations_page' )
		);

		// Leads submenu.
		add_submenu_page(
			'chatcommerce-ai',
			__( 'Leads', 'chatcommerce-ai' ),
			__( 'Leads', 'chatcommerce-ai' ),
			'manage_options',
			'chatcommerce-ai-leads',
			array( $this, 'render_leads_page' )
		);

		// Sync submenu.
		add_submenu_page(
			'chatcommerce-ai',
			__( 'Content Sync', 'chatcommerce-ai' ),
			__( 'Content Sync', 'chatcommerce-ai' ),
			'manage_options',
			'chatcommerce-ai-sync',
			array( $this, 'render_sync_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'chatcommerce_ai_settings',
			'chatcommerce_ai_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// General Settings Section.
		add_settings_section(
			'chatcommerce_ai_general',
			__( 'General Settings', 'chatcommerce-ai' ),
			null,
			'chatcommerce_ai_general'
		);

		// AI Settings Section.
		add_settings_section(
			'chatcommerce_ai_ai',
			__( 'AI Settings', 'chatcommerce-ai' ),
			null,
			'chatcommerce_ai_ai'
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Settings input.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$output = array();

		// General settings.
		$output['enabled']         = ! empty( $input['enabled'] );
		$output['position']        = sanitize_text_field( $input['position'] ?? 'bottom-right' );
		$output['primary_color']   = sanitize_hex_color( $input['primary_color'] ?? '#0073aa' );
		$output['bg_color']        = sanitize_hex_color( $input['bg_color'] ?? '#ffffff' );
		$output['text_color']      = sanitize_hex_color( $input['text_color'] ?? '#000000' );
		$output['welcome_message'] = sanitize_textarea_field( $input['welcome_message'] ?? '' );
		$output['brand_logo']      = esc_url_raw( $input['brand_logo'] ?? '' );

		// AI settings.
		if ( ! empty( $input['openai_api_key'] ) ) {
			$output['openai_api_key'] = $this->encrypt_api_key( sanitize_text_field( $input['openai_api_key'] ) );
		} else {
			$output['openai_api_key'] = $input['openai_api_key_encrypted'] ?? '';
		}

		$output['openai_model'] = sanitize_text_field( $input['openai_model'] ?? 'gpt-4-turbo-preview' );
		$output['temperature']  = floatval( $input['temperature'] ?? 0.7 );
		$output['max_tokens']   = intval( $input['max_tokens'] ?? 500 );

		// Sync settings.
		$output['sync_post_types'] = isset( $input['sync_post_types'] ) && is_array( $input['sync_post_types'] )
			? array_map( 'sanitize_text_field', $input['sync_post_types'] )
			: array( 'post', 'page', 'product' );

		$output['sync_schedule'] = sanitize_text_field( $input['sync_schedule'] ?? 'hourly' );

		// Lead capture settings.
		$output['lead_capture_enabled'] = ! empty( $input['lead_capture_enabled'] );
		$output['lead_fields']          = isset( $input['lead_fields'] ) && is_array( $input['lead_fields'] )
			? array_map( 'sanitize_text_field', $input['lead_fields'] )
			: array( 'name', 'email' );

		// Feedback settings.
		$output['feedback_enabled'] = ! empty( $input['feedback_enabled'] );

		// Privacy settings.
		$output['telemetry_enabled']   = ! empty( $input['telemetry_enabled'] );
		$output['data_retention_days'] = intval( $input['data_retention_days'] ?? 30 );

		return $output;
	}

	/**
	 * Encrypt API key.
	 *
	 * @param string $key API key.
	 * @return string
	 */
	private function encrypt_api_key( $key ) {
		if ( function_exists( 'openssl_encrypt' ) ) {
			$encryption_key = wp_salt( 'auth' );
			$iv_length      = openssl_cipher_iv_length( 'AES-256-CBC' );
			$iv             = openssl_random_pseudo_bytes( $iv_length );
			$encrypted      = openssl_encrypt( $key, 'AES-256-CBC', $encryption_key, 0, $iv );

			return base64_encode( $iv . $encrypted );
		}

		return $key; // Fallback to plain text if OpenSSL not available.
	}

	/**
	 * Decrypt API key.
	 *
	 * @param string $encrypted_key Encrypted API key.
	 * @return string
	 */
	public static function decrypt_api_key( $encrypted_key ) {
		if ( function_exists( 'openssl_decrypt' ) && ! empty( $encrypted_key ) ) {
			$encryption_key = wp_salt( 'auth' );
			$data           = base64_decode( $encrypted_key );
			$iv_length      = openssl_cipher_iv_length( 'AES-256-CBC' );
			$iv             = substr( $data, 0, $iv_length );
			$encrypted      = substr( $data, $iv_length );

			return openssl_decrypt( $encrypted, 'AES-256-CBC', $encryption_key, 0, $iv );
		}

		return $encrypted_key; // Return as-is if not encrypted.
	}

	/**
	 * Show admin notices.
	 */
	public function show_admin_notices() {
		// Show onboarding notice if not dismissed.
		if ( get_option( 'chatcommerce_ai_show_onboarding' ) && ! get_user_meta( get_current_user_id(), 'chatcommerce_ai_dismissed_onboarding', true ) ) {
			$this->render_onboarding_notice();
		}

		// Check if OpenAI API key is set.
		$settings = get_option( 'chatcommerce_ai_settings', array() );
		if ( empty( $settings['openai_api_key'] ) && $this->is_plugin_page() && ! get_user_meta( get_current_user_id(), 'chatcommerce_ai_dismissed_api_key_notice', true ) ) {
			?>
			<div class="notice notice-warning is-dismissible chatcommerce-ai-notice" data-notice-id="api_key_notice">
				<p>
					<?php
					printf(
						/* translators: %s: settings page URL */
						__( 'ChatCommerce AI requires an OpenAI API key to function. <a href="%s">Configure it now</a>.', 'chatcommerce-ai' ),
						esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=ai' ) )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render onboarding notice.
	 */
	private function render_onboarding_notice() {
		?>
		<div class="notice notice-success is-dismissible chatcommerce-ai-notice" data-notice-id="onboarding">
			<p>
				<?php
				printf(
					/* translators: %s: settings page URL */
					__( 'Welcome to ChatCommerce AI! <a href="%s">Complete the setup</a> to get started.', 'chatcommerce-ai' ),
					esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Check if current page is a plugin page.
	 *
	 * @return bool
	 */
	private function is_plugin_page() {
		$screen = get_current_screen();
		return $screen && strpos( $screen->id, 'chatcommerce-ai' ) !== false;
	}

	/**
	 * Hide WooCommerce notices on ChatCommerce AI pages.
	 */
	public function hide_woocommerce_notices() {
		// Only hide on our plugin pages.
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		// Remove WooCommerce admin notices at multiple hook points.
		remove_action( 'admin_notices', array( 'WC_Admin_Notices', 'output_notices' ), 99 );
		remove_action( 'admin_notices', 'woocommerce_output_all_notices', 99 );

		// Remove specific WooCommerce notice types.
		global $wp_filter;
		if ( isset( $wp_filter['admin_notices'] ) ) {
			foreach ( $wp_filter['admin_notices']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_id => $callback ) {
					if ( is_array( $callback['function'] ) && isset( $callback['function'][0] ) ) {
						$class_name = is_object( $callback['function'][0] ) ? get_class( $callback['function'][0] ) : '';
						if ( strpos( $class_name, 'WooCommerce' ) !== false || strpos( $class_name, 'WC_' ) !== false ) {
							remove_action( 'admin_notices', $callback['function'], $priority );
						}
					}
				}
			}
		}

		// Hide WooCommerce notices with CSS as a comprehensive fallback.
		?>
		<style>
			/* Hide all WooCommerce-related notices */
			.woocommerce-message,
			.woocommerce-error,
			.woocommerce-info,
			div.notice.wc-admin-notice,
			div.notice[class*="woocommerce"],
			div.notice[class*="wc-"],
			.notice:has(a[href*="woocommerce"]),
			#wpbody-content > .notice:not(.chatcommerce-ai-notice) {
				display: none !important;
			}

			/* Only show ChatCommerce AI notices */
			.chatcommerce-ai-notice {
				display: flex !important;
			}
		</style>
		<?php
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard_page() {
		require_once CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		require_once CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings.php';
	}

	/**
	 * Render conversations page.
	 */
	public function render_conversations_page() {
		require_once CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/conversations.php';
	}

	/**
	 * Render leads page.
	 */
	public function render_leads_page() {
		require_once CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/leads.php';
	}

	/**
	 * Render sync page.
	 */
	public function render_sync_page() {
		require_once CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/sync.php';
	}

	/**
	 * AJAX handler to dismiss admin notices.
	 */
	public function ajax_dismiss_notice() {
		// Verify nonce.
		check_ajax_referer( 'chatcommerce_ai_dismiss_notice', 'nonce' );

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'chatcommerce-ai' ) ) );
		}

		// Get notice ID.
		$notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( $_POST['notice_id'] ) : '';

		if ( empty( $notice_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid notice ID.', 'chatcommerce-ai' ) ) );
		}

		// Save dismissed state.
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'chatcommerce_ai_dismissed_' . $notice_id, true );

		// If dismissing onboarding, also remove the global flag.
		if ( $notice_id === 'onboarding' ) {
			delete_option( 'chatcommerce_ai_show_onboarding' );
		}

		wp_send_json_success( array( 'message' => __( 'Notice dismissed.', 'chatcommerce-ai' ) ) );
	}
}
