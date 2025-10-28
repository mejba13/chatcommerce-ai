<?php
/**
 * Privacy Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h2><?php esc_html_e( 'Privacy & Data Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="data_retention_days"><?php esc_html_e( 'Data Retention Period', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[data_retention_days]" id="data_retention_days">
				<option value="7" <?php selected( $settings['data_retention_days'] ?? '30', '7' ); ?>>
					<?php esc_html_e( '7 days', 'chatcommerce-ai' ); ?>
				</option>
				<option value="30" <?php selected( $settings['data_retention_days'] ?? '30', '30' ); ?>>
					<?php esc_html_e( '30 days', 'chatcommerce-ai' ); ?>
				</option>
				<option value="60" <?php selected( $settings['data_retention_days'] ?? '30', '60' ); ?>>
					<?php esc_html_e( '60 days', 'chatcommerce-ai' ); ?>
				</option>
				<option value="90" <?php selected( $settings['data_retention_days'] ?? '30', '90' ); ?>>
					<?php esc_html_e( '90 days', 'chatcommerce-ai' ); ?>
				</option>
				<option value="365" <?php selected( $settings['data_retention_days'] ?? '30', '365' ); ?>>
					<?php esc_html_e( '1 year', 'chatcommerce-ai' ); ?>
				</option>
			</select>
			<p class="setting-description">
				<?php esc_html_e( 'Automatically delete chat sessions and messages older than this period.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="store_ip_address"><?php esc_html_e( 'Store IP Addresses', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[store_ip_address]"
					id="store_ip_address"
					value="1"
					<?php checked( ! empty( $settings['store_ip_address'] ) ); ?>
				/>
				<?php esc_html_e( 'Store user IP addresses for rate limiting and abuse prevention', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'IP addresses are anonymized and used only for security purposes.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="anonymize_leads"><?php esc_html_e( 'Anonymize Analytics', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[anonymize_analytics]"
					id="anonymize_analytics"
					value="1"
					<?php checked( ! empty( $settings['anonymize_analytics'] ) ); ?>
				/>
				<?php esc_html_e( 'Hash email addresses in analytics and exports', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Protects PII while still allowing lead tracking.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="telemetry_enabled"><?php esc_html_e( 'Usage Telemetry', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[telemetry_enabled]"
					id="telemetry_enabled"
					value="1"
					<?php checked( ! empty( $settings['telemetry_enabled'] ) ); ?>
				/>
				<?php esc_html_e( 'Send anonymous usage data to help improve the plugin', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Includes: PHP/WP versions, feature usage counts, error rates. No PII is collected.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="gdpr_mode"><?php esc_html_e( 'GDPR/CCPA Compliance', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[gdpr_mode]"
					id="gdpr_mode"
					value="1"
					<?php checked( ! empty( $settings['gdpr_mode'] ) || ! isset( $settings['gdpr_mode'] ) ); ?>
				/>
				<?php esc_html_e( 'Enable strict GDPR/CCPA mode', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Requires explicit consent before storing any PII, adds data export/deletion capabilities.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Privacy Policy', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<p>
				<?php
				printf(
					/* translators: %s: WordPress privacy policy URL */
					__( 'ChatCommerce AI respects user privacy. Make sure your <a href="%s">Privacy Policy</a> discloses that you use AI chat with OpenAI.', 'chatcommerce-ai' ),
					esc_url( admin_url( 'privacy.php' ) )
				);
				?>
			</p>
			<p class="setting-description">
				<strong><?php esc_html_e( 'Suggested Privacy Policy Text:', 'chatcommerce-ai' ); ?></strong><br/>
				<?php esc_html_e( '"We use an AI-powered chatbot to assist with customer inquiries. Conversations may be processed by third-party AI services (OpenAI) and stored temporarily for quality improvement. Do not share sensitive personal information in the chat."', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>

<div class="notice notice-warning inline" style="margin-top: 20px;">
	<p>
		<strong><?php esc_html_e( 'Important:', 'chatcommerce-ai' ); ?></strong>
		<?php esc_html_e( 'Chat data is sent to OpenAI for processing. Review OpenAI\'s privacy policy and ensure compliance with your local data protection regulations.', 'chatcommerce-ai' ); ?>
	</p>
</div>
