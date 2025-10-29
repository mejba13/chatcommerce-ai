<?php
/**
 * Modern Professional Chat Widget Template
 *
 * @package ChatCommerceAI
 */

$settings = get_option( 'chatcommerce_ai_settings', array() );
?>

<div
	id="chatcommerce-ai-widget"
	x-data="chatWidget"
	x-init="init()"
	class="fixed z-[9999] bottom-6 right-6"
	style="position: fixed !important; bottom: 1.5rem !important; right: 1.5rem !important; left: auto !important; z-index: 999999 !important;"
	x-cloak
	role="region"
	aria-label="Chat widget"
>
	<!-- Chat Launcher Button -->
	<button
		@click="toggle()"
		@keydown.enter="toggle()"
		@keydown.space.prevent="toggle()"
		x-show="!isOpen"
		x-ref="launcher"
		x-transition:enter="transition ease-out duration-200"
		x-transition:enter-start="opacity-0 scale-90"
		x-transition:enter-end="opacity-100 scale-100"
		x-transition:leave="transition ease-in duration-150"
		x-transition:leave-start="opacity-100 scale-100"
		x-transition:leave-end="opacity-0 scale-90"
		class="group relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-500/30"
		aria-label="Open chat"
		aria-expanded="false"
		aria-haspopup="dialog"
		aria-controls="chatcommerce-panel"
		title="Chat with us"
	>
		<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
			<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
		</svg>
	</button>

	<!-- Chat Window -->
	<div
		id="chatcommerce-panel"
		x-show="isOpen"
		x-ref="panel"
		x-trap.inert.noscroll="isOpen"
		@keydown.escape="close()"
		x-transition:enter="transition ease-out duration-200"
		x-transition:enter-start="opacity-0 translate-y-4 scale-96"
		x-transition:enter-end="opacity-100 translate-y-0 scale-100"
		x-transition:leave="transition ease-in duration-180"
		x-transition:leave-start="opacity-100 translate-y-0 scale-100"
		x-transition:leave-end="opacity-0 translate-y-4 scale-96"
		class="absolute right-0 bottom-20 w-[380px] h-[550px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-200/60"
		style="box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12), 0 0 1px rgba(0, 0, 0, 0.08);"
		role="dialog"
		aria-modal="true"
		aria-labelledby="chat-header-title"
		@click.away="close()"
	>
		<!-- Header -->
		<div class="flex items-center justify-between px-4 py-3.5 bg-white border-b border-gray-100">
			<div class="flex items-center space-x-3">
				<div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center flex-shrink-0">
					<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
						<path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
						<path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
					</svg>
				</div>
				<div>
					<h3 id="chat-header-title" class="text-sm font-semibold text-gray-900"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h3>
					<p class="text-xs text-gray-500 flex items-center" x-show="!isTyping">
						<span class="inline-block w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5" aria-hidden="true"></span>
						Online
					</p>
					<p class="text-xs text-gray-500" x-show="isTyping" aria-live="polite">Typing...</p>
				</div>
			</div>
			<button
				@click="close()"
				class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors flex-shrink-0"
				aria-label="Close chat"
			>
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>
		</div>

		<!-- Messages -->
		<div
			x-ref="messages"
			@scroll="onScroll()"
			class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-gray-50/50"
			role="log"
			aria-live="polite"
			aria-label="Chat messages"
		>
			<template x-for="message in messages" :key="message.id">
				<div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
					<div :class="message.role === 'user' ? 'max-w-[80%]' : 'max-w-[85%]'">
						<div
							:class="{
								'bg-primary-500 text-white rounded-2xl rounded-br-sm': message.role === 'user',
								'bg-white text-gray-900 rounded-2xl rounded-bl-sm border border-gray-200': message.role === 'assistant'
							}"
							class="px-4 py-2.5 shadow-sm"
						>
							<p class="text-sm leading-relaxed" x-html="message.content"></p>
						</div>
						<div class="mt-1 px-1">
							<span class="text-xs text-gray-400" x-text="formatTime(message.timestamp)"></span>
						</div>
					</div>
				</div>
			</template>
		</div>

		<!-- Input Area -->
		<div class="px-4 py-3 bg-white border-t border-gray-100">
			<div class="flex items-center space-x-2">
				<input
					x-ref="input"
					x-model="inputMessage"
					@keydown.enter="sendMessage()"
					type="text"
					placeholder="Type a message..."
					class="flex-1 px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors"
				/>
				<button
					@click="sendMessage()"
					:disabled="!inputMessage.trim() || isLoading"
					:class="inputMessage.trim() && !isLoading ? 'bg-primary-500 hover:bg-primary-600' : 'bg-gray-300 cursor-not-allowed'"
					class="p-2.5 text-white rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20"
					aria-label="Send message"
				>
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
						<path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
					</svg>
				</button>
			</div>
			<div class="mt-2 text-center">
				<p class="text-xs text-gray-400">Powered by ChatCommerce AI</p>
			</div>
		</div>
	</div>
</div>
