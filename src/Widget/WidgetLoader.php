<?php
/**
 * Widget Loader
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\Widget;

/**
 * Loads chat widget on frontend.
 */
class WidgetLoader {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_footer', array( $this, 'render_widget' ) );
		add_shortcode( 'chatcommerce_ai', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render widget in footer.
	 */
	public function render_widget() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		echo $this->get_widget_html();
	}

	/**
	 * Render shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		return $this->get_widget_html();
	}

	/**
	 * Get widget HTML.
	 *
	 * @return string
	 */
	private function get_widget_html() {
		ob_start();
		?>
		<div id="chatcommerce-ai-widget-container"></div>
		<?php
		return ob_get_clean();
	}
}
