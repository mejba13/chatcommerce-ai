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
	 * Get widget HTML (Modern Alpine.js + Tailwind CSS version).
	 *
	 * @return string
	 */
	private function get_widget_html() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );
		$position = $settings['position'] ?? 'bottom-right';

		ob_start();
		?>
		<div
			id="chatcommerce-ai-widget"
			x-data="chatWidget"
			x-init="init()"
			class="fixed z-[9999] <?php echo esc_attr( $position === 'bottom-left' ? 'bottom-6 left-6' : 'bottom-6 right-6' ); ?>"
			x-cloak
		>
			<!-- Chat Toggle Button -->
			<button
				@click="toggle()"
				x-show="!isOpen"
				x-transition:enter="transition ease-out duration-300"
				x-transition:enter-start="opacity-0 scale-0"
				x-transition:enter-end="opacity-100 scale-100"
				x-transition:leave="transition ease-in duration-200"
				x-transition:leave-start="opacity-100 scale-100"
				x-transition:leave-end="opacity-0 scale-0"
				class="group relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white rounded-full shadow-2xl hover:shadow-glow transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-300"
				aria-label="Open chat"
			>
				<!-- Icon -->
				<svg class="w-7 h-7 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
				</svg>

				<!-- Notification Badge (optional) -->
				<span class="absolute -top-1 -right-1 flex h-5 w-5">
					<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-400 opacity-75"></span>
					<span class="relative inline-flex rounded-full h-5 w-5 bg-accent-500 text-white text-xs items-center justify-center font-semibold">1</span>
				</span>
			</button>

			<!-- Chat Window -->
			<div
				x-show="isOpen"
				x-transition:enter="transition ease-out duration-300"
				x-transition:enter-start="opacity-0 translate-y-4 scale-95"
				x-transition:enter-end="opacity-100 translate-y-0 scale-100"
				x-transition:leave="transition ease-in duration-200"
				x-transition:leave-start="opacity-100 translate-y-0 scale-100"
				x-transition:leave-end="opacity-0 translate-y-4 scale-95"
				class="absolute <?php echo esc_attr( $position === 'bottom-left' ? 'left-0' : 'right-0' ); ?> bottom-20 w-[420px] max-w-[calc(100vw-2rem)] h-[640px] max-h-[calc(100vh-8rem)] bg-white rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-gray-100"
				@click.away="close()"
			>
				<!-- Header -->
				<div class="relative flex items-center justify-between px-6 py-5 bg-gradient-to-r from-primary-600 to-primary-700 text-white">
					<!-- Brand -->
					<div class="flex items-center space-x-3">
						<?php if ( ! empty( $settings['brand_logo'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['brand_logo'] ); ?>" alt="Logo" class="w-10 h-10 rounded-full ring-2 ring-white/50">
						<?php else : ?>
							<div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
								<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
								</svg>
							</div>
						<?php endif; ?>

						<div>
							<h3 class="text-lg font-semibold tracking-tight"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h3>
							<p class="text-xs text-primary-100" x-show="!isTyping">Online now</p>
							<p class="text-xs text-primary-100 flex items-center" x-show="isTyping">
								<span class="flex space-x-1">
									<span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce" style="animation-delay: 0ms"></span>
									<span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce" style="animation-delay: 150ms"></span>
									<span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce" style="animation-delay: 300ms"></span>
								</span>
								<span class="ml-2">Typing</span>
							</p>
						</div>
					</div>

					<!-- Close Button -->
					<button
						@click="close()"
						class="p-2 rounded-xl hover:bg-white/10 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/50"
						aria-label="Close chat"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
						</svg>
					</button>
				</div>

				<!-- Messages Container -->
				<div
					x-ref="messages"
					@scroll="onScroll()"
					class="flex-1 overflow-y-auto px-6 py-6 space-y-4 chatcommerce-scrollbar bg-gradient-to-b from-gray-50/50 to-white"
				>
					<!-- Message Loop -->
					<template x-for="message in messages" :key="message.id">
						<div
							class="animate-slide-up"
							:class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'"
						>
							<div :class="message.role === 'user' ? 'max-w-[80%]' : 'max-w-[85%]'">
								<!-- Message Bubble -->
								<div
									class="relative group"
									:class="{
										'bg-gradient-to-br from-primary-600 to-primary-700 text-white rounded-2xl rounded-br-md shadow-lg': message.role === 'user',
										'bg-white text-gray-800 rounded-2xl rounded-bl-md shadow-soft border border-gray-100': message.role === 'assistant'
									}"
								>
									<div class="px-5 py-3.5">
										<p
											class="text-sm leading-relaxed whitespace-pre-wrap break-words"
											:class="message.role === 'user' ? 'text-white' : 'text-gray-800'"
											x-html="message.content"
										></p>
									</div>

									<!-- Streaming indicator -->
									<div x-show="message.isStreaming" class="px-5 pb-2">
										<div class="flex space-x-1">
											<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
											<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
											<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
										</div>
									</div>
								</div>

								<!-- Timestamp & Feedback -->
								<div
									class="mt-1.5 px-1 flex items-center"
									:class="message.role === 'user' ? 'justify-end' : 'justify-between'"
								>
									<span class="text-xs text-gray-400" x-text="formatTime(message.timestamp)"></span>

									<!-- Feedback Buttons (Assistant messages only) -->
									<div
										x-show="message.role === 'assistant' && !message.isStreaming"
										class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
									>
										<button
											@click="submitFeedback(message.id, 1)"
											:class="message.feedback === 1 ? 'text-primary-600' : 'text-gray-400 hover:text-primary-600'"
											class="p-1.5 rounded-lg hover:bg-gray-100 transition-all duration-200"
											title="Helpful"
										>
											<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
											</svg>
										</button>
										<button
											@click="submitFeedback(message.id, 0)"
											:class="message.feedback === 0 ? 'text-red-600' : 'text-gray-400 hover:text-red-600'"
											class="p-1.5 rounded-lg hover:bg-gray-100 transition-all duration-200"
											title="Not helpful"
										>
											<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
											</svg>
										</button>
									</div>
								</div>
							</div>
						</div>
					</template>
				</div>

				<!-- Scroll to Bottom Button -->
				<div
					x-show="showScrollButton"
					x-transition
					class="absolute bottom-24 left-1/2 transform -translate-x-1/2"
				>
					<button
						@click="scrollToBottom()"
						class="px-4 py-2 bg-white text-primary-600 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2 border border-gray-200"
					>
						<span class="text-sm font-medium">New messages</span>
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
						</svg>
					</button>
				</div>

				<!-- Input Area -->
				<div class="px-6 py-5 bg-white border-t border-gray-100">
					<div class="flex items-end space-x-3">
						<!-- Text Input -->
						<div class="flex-1 relative">
							<textarea
								x-ref="input"
								x-model="inputMessage"
								@keydown="onKeyPress($event)"
								placeholder="Type your message..."
								rows="1"
								class="block w-full px-4 py-3 pr-12 text-sm text-gray-900 placeholder-gray-400 bg-gray-50 border border-gray-200 rounded-2xl resize-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
								style="max-height: 120px; field-sizing: content;"
							></textarea>

							<!-- Character count (optional) -->
							<div class="absolute right-3 bottom-3 text-xs text-gray-400" x-show="inputMessage.length > 0">
								<span x-text="inputMessage.length"></span>/1000
							</div>
						</div>

						<!-- Send Button -->
						<button
							@click="sendMessage()"
							:disabled="!inputMessage.trim() || isLoading"
							:class="inputMessage.trim() && !isLoading ? 'bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 shadow-lg hover:shadow-glow scale-100 hover:scale-105' : 'bg-gray-300 cursor-not-allowed'"
							class="flex-shrink-0 w-12 h-12 flex items-center justify-center text-white rounded-2xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-primary-300"
						>
							<svg
								x-show="!isLoading"
								class="w-5 h-5 transform rotate-90"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
							</svg>

							<!-- Loading Spinner -->
							<svg
								x-show="isLoading"
								class="w-5 h-5 animate-spin"
								fill="none"
								viewBox="0 0 24 24"
							>
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</button>
					</div>

					<!-- Powered By (optional) -->
					<div class="mt-3 text-center">
						<p class="text-xs text-gray-400">
							Powered by <span class="font-semibold text-gray-600">ChatCommerce AI</span>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
