<?php
/**
 * Plugin Name: ChatCommerce AI
 * Plugin URI: https://www.mejba.me/chatcommerce-ai
 * Description: 24/7 AI support and lead capture for WooCommerce with OpenAI-powered chat, product-aware responses, and modern, customizable UI.
 * Version: 1.0.0
 * Author: Engr Mejba Ahmed
 * Author URI: https://www.mejba.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: chatcommerce-ai
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * WC requires at least: 8.0
 * WC tested up to: 10.3
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
define( 'CHATCOMMERCE_AI_VERSION', '1.0.0' );
define( 'CHATCOMMERCE_AI_PLUGIN_FILE', __FILE__ );
define( 'CHATCOMMERCE_AI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHATCOMMERCE_AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CHATCOMMERCE_AI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Minimum requirements.
define( 'CHATCOMMERCE_AI_MIN_PHP_VERSION', '8.1' );
define( 'CHATCOMMERCE_AI_MIN_WP_VERSION', '6.4' );
define( 'CHATCOMMERCE_AI_MIN_WC_VERSION', '8.0' );

/**
 * Autoloader for plugin classes.
 *
 * @param string $class The fully-qualified class name.
 */
spl_autoload_register(
	function ( $class ) {
		// Project-specific namespace prefix.
		$prefix = 'ChatCommerceAI\\';

		// Base directory for the namespace prefix.
		$base_dir = __DIR__ . '/src/';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, $len );

		// Replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php.
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Check if WooCommerce is active and meets minimum version requirement.
 *
 * @return bool
 */
function is_woocommerce_compatible() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, CHATCOMMERCE_AI_MIN_WC_VERSION, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Display admin notice for WooCommerce dependency.
 */
function woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce minimum version */
				esc_html__( 'ChatCommerce AI requires WooCommerce %s or higher to be installed and active.', 'chatcommerce-ai' ),
				esc_html( CHATCOMMERCE_AI_MIN_WC_VERSION )
			);
			?>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Install WooCommerce', 'chatcommerce-ai' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * Display admin notice for PHP version.
 */
function php_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: Current PHP version, 2: Required PHP version */
				esc_html__( 'ChatCommerce AI requires PHP version %2$s or higher. Your current version is %1$s.', 'chatcommerce-ai' ),
				esc_html( PHP_VERSION ),
				esc_html( CHATCOMMERCE_AI_MIN_PHP_VERSION )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Display admin notice for WordPress version.
 */
function wordpress_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: Current WordPress version, 2: Required WordPress version */
				esc_html__( 'ChatCommerce AI requires WordPress version %2$s or higher. Your current version is %1$s.', 'chatcommerce-ai' ),
				esc_html( get_bloginfo( 'version' ) ),
				esc_html( CHATCOMMERCE_AI_MIN_WP_VERSION )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Check all plugin requirements.
 *
 * @return bool
 */
function check_requirements() {
	$meets_requirements = true;

	// Check PHP version.
	if ( version_compare( PHP_VERSION, CHATCOMMERCE_AI_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\php_version_notice' );
		$meets_requirements = false;
	}

	// Check WordPress version.
	if ( version_compare( get_bloginfo( 'version' ), CHATCOMMERCE_AI_MIN_WP_VERSION, '<' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\wordpress_version_notice' );
		$meets_requirements = false;
	}

	// Check WooCommerce.
	if ( ! is_woocommerce_compatible() ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\woocommerce_missing_notice' );
		$meets_requirements = false;
	}

	return $meets_requirements;
}

/**
 * Initialize the plugin.
 */
function init() {
	// Load text domain for translations.
	load_plugin_textdomain( 'chatcommerce-ai', false, dirname( CHATCOMMERCE_AI_PLUGIN_BASENAME ) . '/languages' );

	// Check requirements before initializing.
	if ( ! check_requirements() ) {
		return;
	}

	// Initialize core plugin.
	Core\Plugin::instance();
}

// Hook into plugins_loaded to ensure WooCommerce is loaded first.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );

/**
 * Add plugin action links (Settings, etc).
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) ),
		esc_html__( 'Settings', 'chatcommerce-ai' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . CHATCOMMERCE_AI_PLUGIN_BASENAME, __NAMESPACE__ . '\\plugin_action_links' );

/**
 * Activation hook.
 */
function activate() {
	// Check requirements on activation.
	if ( ! check_requirements() ) {
		deactivate_plugins( CHATCOMMERCE_AI_PLUGIN_BASENAME );
		wp_die(
			esc_html__( 'ChatCommerce AI could not be activated. Please check the requirements.', 'chatcommerce-ai' ),
			esc_html__( 'Plugin Activation Error', 'chatcommerce-ai' ),
			array( 'back_link' => true )
		);
	}

	// Run activation tasks.
	Core\Activator::activate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

/**
 * Deactivation hook.
 */
function deactivate() {
	Core\Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );
