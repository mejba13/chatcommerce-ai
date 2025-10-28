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
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[enabled]"
					id="enabled"
					value="1"
					<?php checked( ! empty( $settings['enabled'] ) ); ?>
				/>
				<?php esc_html_e( 'Enable the chatbot on your website', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Turn on or off the chatbot widget on the frontend.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="position"><?php esc_html_e( 'Widget Position', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[position]" id="position">
				<option value="bottom-right" <?php selected( $settings['position'] ?? 'bottom-right', 'bottom-right' ); ?>>
					<?php esc_html_e( 'Bottom Right', 'chatcommerce-ai' ); ?>
				</option>
				<option value="bottom-left" <?php selected( $settings['position'] ?? 'bottom-right', 'bottom-left' ); ?>>
					<?php esc_html_e( 'Bottom Left', 'chatcommerce-ai' ); ?>
				</option>
			</select>
			<p class="setting-description">
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
				class="large-text"><?php echo esc_textarea( $settings['welcome_message'] ?? __( 'Hi! How can I help you today?', 'chatcommerce-ai' ) ); ?></textarea>
			<p class="setting-description">
				<?php esc_html_e( 'The initial message shown when the chat widget is opened.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="primary_color"><?php esc_html_e( 'Primary Color', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="color-picker-group">
				<input
					type="color"
					name="chatcommerce_ai[primary_color]"
					id="primary_color"
					value="<?php echo esc_attr( $settings['primary_color'] ?? '#0073aa' ); ?>"
				/>
				<span><?php echo esc_html( $settings['primary_color'] ?? '#0073aa' ); ?></span>
			</div>
			<p class="setting-description">
				<?php esc_html_e( 'Primary color for the chat widget (buttons, header, etc.).', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="bg_color"><?php esc_html_e( 'Background Color', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="color-picker-group">
				<input
					type="color"
					name="chatcommerce_ai[bg_color]"
					id="bg_color"
					value="<?php echo esc_attr( $settings['bg_color'] ?? '#ffffff' ); ?>"
				/>
				<span><?php echo esc_html( $settings['bg_color'] ?? '#ffffff' ); ?></span>
			</div>
			<p class="setting-description">
				<?php esc_html_e( 'Background color for the chat widget.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="text_color"><?php esc_html_e( 'Text Color', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="color-picker-group">
				<input
					type="color"
					name="chatcommerce_ai[text_color]"
					id="text_color"
					value="<?php echo esc_attr( $settings['text_color'] ?? '#000000' ); ?>"
				/>
				<span><?php echo esc_html( $settings['text_color'] ?? '#000000' ); ?></span>
			</div>
			<p class="setting-description">
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
