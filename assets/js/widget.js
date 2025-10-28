/**
 * ChatCommerce AI Widget
 *
 * @package ChatCommerceAI
 */

(function() {
	'use strict';

	class ChatCommerceAI {
		constructor() {
			this.sessionId = null;
			this.isOpen = false;
			this.isLoading = false;
			this.container = null;
			this.messagesContainer = null;
			this.input = null;

			this.init();
		}

		async init() {
			// Create widget HTML
			this.createWidget();

			// Start session
			await this.startSession();

			// Bind events
			this.bindEvents();
		}

		createWidget() {
			const container = document.getElementById('chatcommerce-ai-widget-container');
			if (!container) return;

			const settings = window.chatcommerceAI?.settings || {};
			const i18n = window.chatcommerceAI?.i18n || {};
			const position = settings.position || 'bottom-right';

			container.innerHTML = `
				<div class="chatcommerce-ai-widget chatcommerce-ai-closed chatcommerce-ai-${position}">
					<button class="chatcommerce-ai-toggle" aria-label="${i18n.expand || 'Open chat'}">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
						</svg>
					</button>

					<div class="chatcommerce-ai-window">
						<div class="chatcommerce-ai-header">
							<div class="chatcommerce-ai-header-content">
								${settings.brandLogo ? `<img src="${settings.brandLogo}" alt="Logo" class="chatcommerce-ai-logo">` : ''}
								<h3>${i18n.title || 'Chat with us'}</h3>
							</div>
							<button class="chatcommerce-ai-close" aria-label="${i18n.close || 'Close'}">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="18" y1="6" x2="6" y2="18"/>
									<line x1="6" y1="6" x2="18" y2="18"/>
								</svg>
							</button>
						</div>

						<div class="chatcommerce-ai-messages"></div>

						<div class="chatcommerce-ai-input-area">
							<textarea
								class="chatcommerce-ai-input"
								placeholder="${i18n.placeholder || 'Type your message...'}"
								rows="1"
								aria-label="Chat message input"></textarea>
							<button class="chatcommerce-ai-send" aria-label="${i18n.send || 'Send'}">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="22" y1="2" x2="11" y2="13"/>
									<polygon points="22 2 15 22 11 13 2 9 22 2"/>
								</svg>
							</button>
						</div>
					</div>
				</div>
			`;

			this.container = container.querySelector('.chatcommerce-ai-widget');
			this.messagesContainer = container.querySelector('.chatcommerce-ai-messages');
			this.input = container.querySelector('.chatcommerce-ai-input');
		}

		bindEvents() {
			const toggle = this.container.querySelector('.chatcommerce-ai-toggle');
			const close = this.container.querySelector('.chatcommerce-ai-close');
			const send = this.container.querySelector('.chatcommerce-ai-send');

			toggle.addEventListener('click', () => this.toggleWidget());
			close.addEventListener('click', () => this.closeWidget());
			send.addEventListener('click', () => this.sendMessage());

			this.input.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' && !e.shiftKey) {
					e.preventDefault();
					this.sendMessage();
				}
			});
		}

		toggleWidget() {
			this.isOpen = !this.isOpen;
			if (this.isOpen) {
				this.container.classList.remove('chatcommerce-ai-closed');
				this.container.classList.add('chatcommerce-ai-open');
				this.input.focus();

				// Show welcome message
				if (this.messagesContainer.children.length === 0) {
					const welcomeMsg = window.chatcommerceAI?.settings?.welcomeMessage || 'Hi! How can I help you today?';
					this.addMessage('assistant', welcomeMsg);
				}
			} else {
				this.closeWidget();
			}
		}

		closeWidget() {
			this.isOpen = false;
			this.container.classList.remove('chatcommerce-ai-open');
			this.container.classList.add('chatcommerce-ai-closed');
		}

		async startSession() {
			try {
				const response = await fetch(`${window.chatcommerceAI.apiUrl}/session/start`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					}
				});

				const data = await response.json();

				if (data.success) {
					this.sessionId = data.session_id;
				}
			} catch (error) {
				console.error('Failed to start session:', error);
			}
		}

		async sendMessage() {
			if (this.isLoading) return;

			const message = this.input.value.trim();
			if (!message) return;

			this.addMessage('user', message);
			this.input.value = '';
			this.isLoading = true;

			this.showTypingIndicator();

			try {
				await this.streamChatResponse(message);
			} catch (error) {
				console.error('Chat error:', error);
				this.addMessage('assistant', 'Sorry, something went wrong. Please try again.');
			} finally {
				this.hideTypingIndicator();
				this.isLoading = false;
			}
		}

		async streamChatResponse(message) {
			const response = await fetch(`${window.chatcommerceAI.apiUrl}/chat/stream`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'text/event-stream',
				},
				body: JSON.stringify({
					session_id: this.sessionId,
					message: message,
				}),
			});

			// Check if SSE is supported
			if (response.headers.get('content-type')?.includes('text/event-stream')) {
				await this.handleSSEStream(response);
			} else {
				// Fallback to JSON response
				const data = await response.json();
				if (data.success) {
					this.addMessage('assistant', data.message);
				}
			}
		}

		async handleSSEStream(response) {
			const reader = response.body.getReader();
			const decoder = new TextDecoder();
			let assistantMessage = '';
			let messageElement = null;

			while (true) {
				const { done, value } = await reader.read();
				if (done) break;

				const chunk = decoder.decode(value);
				const lines = chunk.split('\n');

				for (const line of lines) {
					if (line.startsWith('data: ')) {
						const data = JSON.parse(line.substring(6));

						if (data.chunk) {
							assistantMessage += data.chunk;

							if (!messageElement) {
								messageElement = this.addMessage('assistant', assistantMessage);
							} else {
								messageElement.textContent = assistantMessage;
							}

							this.scrollToBottom();
						}
					}
				}
			}
		}

		addMessage(role, content) {
			const messageDiv = document.createElement('div');
			messageDiv.className = `chatcommerce-ai-message chatcommerce-ai-message-${role}`;

			const contentDiv = document.createElement('div');
			contentDiv.className = 'chatcommerce-ai-message-content';
			contentDiv.textContent = content;

			messageDiv.appendChild(contentDiv);

			if (role === 'assistant') {
				const feedbackDiv = document.createElement('div');
				feedbackDiv.className = 'chatcommerce-ai-feedback';
				feedbackDiv.innerHTML = `
					<button class="chatcommerce-ai-feedback-btn" data-rating="1" aria-label="Helpful">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
						</svg>
					</button>
					<button class="chatcommerce-ai-feedback-btn" data-rating="0" aria-label="Not helpful">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"/>
						</svg>
					</button>
				`;
				messageDiv.appendChild(feedbackDiv);

				feedbackDiv.querySelectorAll('button').forEach(btn => {
					btn.addEventListener('click', () => this.submitFeedback(btn.dataset.rating));
				});
			}

			this.messagesContainer.appendChild(messageDiv);
			this.scrollToBottom();

			return contentDiv;
		}

		showTypingIndicator() {
			const indicator = document.createElement('div');
			indicator.className = 'chatcommerce-ai-typing-indicator';
			indicator.innerHTML = '<span></span><span></span><span></span>';
			this.messagesContainer.appendChild(indicator);
			this.scrollToBottom();
		}

		hideTypingIndicator() {
			const indicator = this.messagesContainer.querySelector('.chatcommerce-ai-typing-indicator');
			if (indicator) {
				indicator.remove();
			}
		}

		scrollToBottom() {
			this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
		}

		async submitFeedback(rating) {
			try {
				await fetch(`${window.chatcommerceAI.apiUrl}/feedback`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						session_id: this.sessionId,
						rating: parseInt(rating),
					}),
				});
			} catch (error) {
				console.error('Failed to submit feedback:', error);
			}
		}
	}

	// Initialize widget when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => new ChatCommerceAI());
	} else {
		new ChatCommerceAI();
	}
})();
