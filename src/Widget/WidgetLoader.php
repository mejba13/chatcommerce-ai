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
		// Force bottom-right position for modern UX
		$position = 'bottom-right';

		ob_start();
		?>
		<div
			id="chatcommerce-ai-widget"
			x-data="chatWidget"
			x-init="init()"
			class="fixed z-[9999] bottom-6 right-6"
			x-cloak
		>
			<!-- Chat Toggle Button - Enhanced Modern Design -->
			<button
				@click="toggle()"
				x-show="!isOpen"
				x-transition:enter="transition ease-out duration-300"
				x-transition:enter-start="opacity-0 scale-0"
				x-transition:enter-end="opacity-100 scale-100"
				x-transition:leave="transition ease-in duration-200"
				x-transition:leave-start="opacity-100 scale-100"
				x-transition:leave-end="opacity-0 scale-0"
				class="group relative flex items-center justify-center w-[72px] h-[72px] bg-gradient-to-br from-primary-600 via-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white rounded-full shadow-2xl hover:shadow-glow transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-primary-500 focus:ring-opacity-50"
				style="box-shadow: 0 10px 40px rgba(2, 132, 199, 0.3);"
				aria-label="Open chat"
			>
				<!-- Icon with better contrast -->
				<svg class="w-8 h-8 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
				</svg>

				<!-- Enhanced Notification Badge with better contrast -->
				<span class="absolute -top-1 -right-1 flex h-6 w-6">
					<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
					<span class="relative inline-flex rounded-full h-6 w-6 bg-red-500 text-white text-xs items-center justify-center font-bold shadow-lg ring-2 ring-white">1</span>
				</span>
			</button>

			<!-- Chat Window - Enhanced Modern Design -->
			<div
				x-show="isOpen"
				x-transition:enter="transition ease-out duration-300"
				x-transition:enter-start="opacity-0 translate-y-4 scale-95"
				x-transition:enter-end="opacity-100 translate-y-0 scale-100"
				x-transition:leave="transition ease-in duration-200"
				x-transition:leave-start="opacity-100 translate-y-0 scale-100"
				x-transition:leave-end="opacity-0 translate-y-4 scale-95"
				class="absolute right-0 bottom-24 w-[440px] max-w-[calc(100vw-2rem)] h-[680px] max-h-[calc(100vh-8rem)] bg-white rounded-3xl shadow-2xl flex flex-col overflow-hidden border-2 border-gray-200"
				style="box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);"
				@click.away="close()"
			>
				<!-- Header - Enhanced with better contrast -->
				<div class="relative flex items-center justify-between px-6 py-6 bg-gradient-to-br from-primary-600 via-primary-600 to-primary-700 text-white border-b-2 border-primary-800/20">
					<!-- Brand -->
					<div class="flex items-center space-x-3">
						<?php if ( ! empty( $settings['brand_logo'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['brand_logo'] ); ?>" alt="Logo" class="w-12 h-12 rounded-full ring-2 ring-white/50 shadow-lg">
						<?php else : ?>
							<div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center ring-2 ring-white/30 shadow-lg">
								<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
								</svg>
							</div>
						<?php endif; ?>

						<div>
							<h3 class="text-lg font-bold tracking-tight"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h3>
							<p class="text-xs text-white/90 font-medium mt-0.5" x-show="!isTyping">
								<span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
								Online now
							</p>
							<p class="text-xs text-white/90 font-medium flex items-center mt-0.5" x-show="isTyping">
								<span class="flex space-x-1 mr-2">
									<span class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0ms"></span>
									<span class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 150ms"></span>
									<span class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 300ms"></span>
								</span>
								Typing...
							</p>
						</div>
					</div>

					<!-- Close Button - Enhanced -->
					<button
						@click="close()"
						class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm"
						aria-label="Close chat"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
						</svg>
					</button>
				</div>

				<!-- Messages Container - Enhanced with better spacing -->
				<div
					x-ref="messages"
					@scroll="onScroll()"
					class="flex-1 overflow-y-auto px-6 py-6 space-y-5 chatcommerce-scrollbar bg-gradient-to-b from-gray-50 to-white"
				>
					<!-- Message Loop -->
					<template x-for="message in messages" :key="message.id">
						<div
							class="animate-slide-up"
							:class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'"
						>
							<div :class="message.role === 'user' ? 'max-w-[82%]' : 'max-w-[85%]'">
								<!-- Message Bubble - Enhanced with better contrast -->
								<div
									class="relative group"
									:class="{
										'bg-gradient-to-br from-primary-600 via-primary-600 to-primary-700 text-white rounded-2xl rounded-br-md shadow-xl': message.role === 'user',
										'bg-white text-gray-900 rounded-2xl rounded-bl-md shadow-md border-2 border-gray-200': message.role === 'assistant'
									}"
								>
									<div class="px-5 py-4">
										<p
											class="text-[15px] font-medium leading-relaxed whitespace-pre-wrap break-words"
											:class="message.role === 'user' ? 'text-white' : 'text-gray-900'"
											x-html="message.content"
											style="line-height: 1.6;"
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

								<!-- Timestamp & Feedback - Enhanced contrast -->
								<div
									class="mt-2 px-1 flex items-center"
									:class="message.role === 'user' ? 'justify-end' : 'justify-between'"
								>
									<span class="text-xs font-semibold text-gray-500" x-text="formatTime(message.timestamp)"></span>

									<!-- Feedback Buttons (Assistant messages only) - Enhanced visibility -->
									<div
										x-show="message.role === 'assistant' && !message.isStreaming"
										class="flex items-center space-x-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
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

				<!-- Scroll to Bottom Button - Enhanced -->
				<div
					x-show="showScrollButton"
					x-transition
					class="absolute bottom-28 left-1/2 transform -translate-x-1/2"
				>
					<button
						@click="scrollToBottom()"
						class="px-5 py-3 bg-white text-primary-600 rounded-full shadow-xl hover:shadow-2xl transition-all duration-200 flex items-center space-x-2 border-2 border-primary-200 hover:border-primary-300 hover:bg-primary-50"
					>
						<span class="text-sm font-bold">New messages</span>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
						</svg>
					</button>
				</div>

				<!-- Input Area - Enhanced with better contrast -->
				<div class="px-6 py-5 bg-white border-t-2 border-gray-200">
					<div class="flex items-end space-x-3">
						<!-- Text Input - Enhanced typography and contrast -->
						<div class="flex-1 relative">
							<textarea
								x-ref="input"
								x-model="inputMessage"
								@keydown="onKeyPress($event)"
								placeholder="Type your message..."
								rows="1"
								class="block w-full px-5 py-4 pr-12 text-[15px] font-medium text-gray-900 placeholder-gray-500 bg-gray-50 border-2 border-gray-300 rounded-2xl resize-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:bg-white transition-all duration-200 shadow-sm"
								style="max-height: 120px; field-sizing: content; line-height: 1.5;"
							></textarea>

							<!-- Character count (optional) -->
							<div class="absolute right-3 bottom-3 text-xs text-gray-400" x-show="inputMessage.length > 0">
								<span x-text="inputMessage.length"></span>/1000
							</div>
						</div>

						<!-- Send Button - Enhanced with better contrast -->
						<button
							@click="sendMessage()"
							:disabled="!inputMessage.trim() || isLoading"
							:class="inputMessage.trim() && !isLoading ? 'bg-gradient-to-br from-primary-600 via-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 shadow-xl hover:shadow-glow scale-100 hover:scale-105 ring-2 ring-primary-500/20' : 'bg-gray-400 cursor-not-allowed shadow-md'"
							class="flex-shrink-0 w-14 h-14 flex items-center justify-center text-white rounded-2xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-primary-500/50"
						>
							<svg
								x-show="!isLoading"
								class="w-6 h-6 transform rotate-90"
								fill="currentColor"
								viewBox="0 0 20 20"
								stroke-width="0.5"
							>
								<path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
							</svg>

							<!-- Loading Spinner - Enhanced -->
							<svg
								x-show="isLoading"
								class="w-6 h-6 animate-spin"
								fill="none"
								viewBox="0 0 24 24"
							>
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</button>
					</div>

					<!-- Powered By - Enhanced typography -->
					<div class="mt-4 text-center">
						<p class="text-xs font-medium text-gray-500">
							Powered by <span class="font-bold text-gray-700">ChatCommerce AI</span>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
