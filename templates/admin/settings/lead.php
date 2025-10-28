<?php
/**
 * Lead Capture Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h2><?php esc_html_e( 'Lead Capture Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="lead_capture_enabled"><?php esc_html_e( 'Enable Lead Capture', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[lead_capture_enabled]"
					id="lead_capture_enabled"
					value="1"
					<?php checked( ! empty( $settings['lead_capture_enabled'] ) ); ?>
				/>
				<?php esc_html_e( 'Allow the AI to capture lead information', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'When enabled, the AI can ask for contact information with explicit user consent.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Lead Fields', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<fieldset>
				<label>
					<input
						type="checkbox"
						name="chatcommerce_ai[lead_fields][]"
						value="name"
						<?php checked( in_array( 'name', $settings['lead_fields'] ?? array( 'name', 'email' ), true ) ); ?>
					/>
					<?php esc_html_e( 'Name', 'chatcommerce-ai' ); ?>
				</label><br/>

				<label>
					<input
						type="checkbox"
						name="chatcommerce_ai[lead_fields][]"
						value="email"
						<?php checked( in_array( 'email', $settings['lead_fields'] ?? array( 'name', 'email' ), true ) ); ?>
					/>
					<?php esc_html_e( 'Email (required)', 'chatcommerce-ai' ); ?>
				</label><br/>

				<label>
					<input
						type="checkbox"
						name="chatcommerce_ai[lead_fields][]"
						value="phone"
						<?php checked( in_array( 'phone', $settings['lead_fields'] ?? array( 'name', 'email' ), true ) ); ?>
					/>
					<?php esc_html_e( 'Phone', 'chatcommerce-ai' ); ?>
				</label>
			</fieldset>
			<p class="setting-description">
				<?php esc_html_e( 'Select which fields to request when capturing leads.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="lead_consent_text"><?php esc_html_e( 'Consent Text', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<textarea
				name="chatcommerce_ai[lead_consent_text]"
				id="lead_consent_text"
				rows="3"
				class="large-text"><?php echo esc_textarea( $settings['lead_consent_text'] ?? __( 'I agree to receive communications from this store.', 'chatcommerce-ai' ) ); ?></textarea>
			<p class="setting-description">
				<?php esc_html_e( 'Text shown for the consent checkbox when capturing leads.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="lead_notification_email"><?php esc_html_e( 'Notification Email', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<input
				type="email"
				name="chatcommerce_ai[lead_notification_email]"
				id="lead_notification_email"
				value="<?php echo esc_attr( $settings['lead_notification_email'] ?? get_option( 'admin_email' ) ); ?>"
				class="regular-text"
			/>
			<p class="setting-description">
				<?php esc_html_e( 'Email address to notify when a new lead is captured.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="lead_webhook_url"><?php esc_html_e( 'Webhook URL (Optional)', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<input
				type="url"
				name="chatcommerce_ai[lead_webhook_url]"
				id="lead_webhook_url"
				value="<?php echo esc_url( $settings['lead_webhook_url'] ?? '' ); ?>"
				class="large-text"
				placeholder="https://example.com/webhook"
			/>
			<p class="setting-description">
				<?php esc_html_e( 'POST lead data to an external webhook (e.g., Zapier, Make.com).', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>
