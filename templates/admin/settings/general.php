<?php
/**
 * General Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h2><?php esc_html_e( 'General Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="enabled"><?php esc_html_e( 'Enable Chatbot', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label style="display: flex; align-items: center; gap: var(--cc-space-3);">
				<input
					type="checkbox"
					name="chatcommerce_ai[enabled]"
					id="enabled"
					value="1"
					<?php checked( ! empty( $settings['enabled'] ) ); ?>
					style="width: 18px; height: 18px; cursor: pointer;"
				/>
				<span style="font-weight: var(--cc-font-medium);">
					<?php esc_html_e( 'Enable the chatbot on your website', 'chatcommerce-ai' ); ?>
				</span>
			</label>
			<p class="description">
				<?php esc_html_e( 'Turn on or off the chatbot widget on the frontend.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="position"><?php esc_html_e( 'Widget Position', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[position]" id="position" class="cc-form-select" style="max-width: 300px;">
				<option value="bottom-right" <?php selected( $settings['position'] ?? 'bottom-right', 'bottom-right' ); ?>>
					<?php esc_html_e( '↘ Bottom Right (Recommended)', 'chatcommerce-ai' ); ?>
				</option>
				<option value="bottom-left" <?php selected( $settings['position'] ?? 'bottom-right', 'bottom-left' ); ?>>
					<?php esc_html_e( '↙ Bottom Left', 'chatcommerce-ai' ); ?>
				</option>
			</select>
			<p class="description">
				<?php esc_html_e( 'Choose where the chat widget appears on your site.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="welcome_message"><?php esc_html_e( 'Welcome Message', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<textarea
				name="chatcommerce_ai[welcome_message]"
				id="welcome_message"
				rows="3"
				class="large-text"
				placeholder="<?php esc_attr_e( 'Hi! How can I help you today?', 'chatcommerce-ai' ); ?>"><?php echo esc_textarea( $settings['welcome_message'] ?? __( 'Hi! How can I help you today?', 'chatcommerce-ai' ) ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'The initial message shown when the chat widget is opened.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="primary_color"><?php esc_html_e( 'Primary Color', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="cc-color-picker-group">
				<input
					type="color"
					name="chatcommerce_ai[primary_color]"
					id="primary_color"
					value="<?php echo esc_attr( $settings['primary_color'] ?? '#0073aa' ); ?>"
					class="cc-color-input"
				/>
				<input
					type="text"
					value="<?php echo esc_attr( $settings['primary_color'] ?? '#0073aa' ); ?>"
					readonly
					class="cc-color-display"
					style="max-width: 120px;"
				/>
			</div>
			<p class="description">
				<?php esc_html_e( 'Primary color for the chat widget (buttons, header, etc.).', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="bg_color"><?php esc_html_e( 'Background Color', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="cc-color-picker-group">
				<input
					type="color"
					name="chatcommerce_ai[bg_color]"
					id="bg_color"
					value="<?php echo esc_attr( $settings['bg_color'] ?? '#ffffff' ); ?>"
					class="cc-color-input"
				/>
				<input
					type="text"
					value="<?php echo esc_attr( $settings['bg_color'] ?? '#ffffff' ); ?>"
					readonly
					class="cc-color-display"
					style="max-width: 120px;"
				/>
			</div>
			<p class="description">
				<?php esc_html_e( 'Background color for the chat widget.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="text_color"><?php esc_html_e( 'Text Color', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="cc-color-picker-group">
				<input
					type="color"
					name="chatcommerce_ai[text_color]"
					id="text_color"
					value="<?php echo esc_attr( $settings['text_color'] ?? '#000000' ); ?>"
					class="cc-color-input"
				/>
				<input
					type="text"
					value="<?php echo esc_attr( $settings['text_color'] ?? '#000000' ); ?>"
					readonly
					class="cc-color-display"
					style="max-width: 120px;"
				/>
			</div>
			<p class="description">
				<?php esc_html_e( 'Text color for the chat widget.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="brand_logo"><?php esc_html_e( 'Brand Logo URL', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<input
				type="url"
				name="chatcommerce_ai[brand_logo]"
				id="brand_logo"
				value="<?php echo esc_url( $settings['brand_logo'] ?? '' ); ?>"
				class="large-text"
				placeholder="https://example.com/logo.png"
			/>
			<p class="setting-description">
				<?php esc_html_e( 'Optional logo to display in the chat widget header.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>
