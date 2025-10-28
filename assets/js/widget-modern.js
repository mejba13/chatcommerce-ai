/**
 * ChatCommerce AI - Modern Widget (Alpine.js)
 *
 * @package ChatCommerceAI
 */

document.addEventListener('alpine:init', () => {
	Alpine.data('chatWidget', () => ({
		// State
		isOpen: false,
		isLoading: false,
		sessionId: null,
		messages: [],
		inputMessage: '',
		isTyping: false,
		showScrollButton: false,

		// Settings from WordPress
		settings: window.chatcommerceAI?.settings || {},
		i18n: window.chatcommerceAI?.i18n || {},

		// Initialize
		async init() {
			await this.startSession();
			this.$watch('isOpen', (value) => {
				if (value && this.messages.length === 0) {
					this.addWelcomeMessage();
				}
			});
		},

		// Toggle widget
		toggle() {
			this.isOpen = !this.isOpen;
			if (this.isOpen) {
				this.$nextTick(() => {
					this.$refs.input?.focus();
				});
			}
		},

		// Close widget
		close() {
			this.isOpen = false;
		},

		// Start session
		async startSession() {
			try {
				console.log('[ChatCommerce] Starting session with API URL:', window.chatcommerceAI.apiUrl);

				const response = await fetch(`${window.chatcommerceAI.apiUrl}/session/start`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					}
				});

				console.log('[ChatCommerce] Session response status:', response.status);
				const data = await response.json();
				console.log('[ChatCommerce] Session data:', data);

				if (data.success) {
					this.sessionId = data.session_id;
					console.log('[ChatCommerce] Session ID:', this.sessionId);
				} else {
					console.error('[ChatCommerce] Session creation failed:', data);
					alert('Failed to start chat session. Please refresh the page.');
				}
			} catch (error) {
				console.error('[ChatCommerce] Failed to start session:', error);
				alert('Could not connect to chat service. Please check your internet connection.');
			}
		},

		// Add welcome message
		addWelcomeMessage() {
			this.messages.push({
				id: Date.now(),
				role: 'assistant',
				content: this.settings.welcomeMessage || 'Hi! How can I help you today?',
				timestamp: new Date(),
			});
		},

		// Send message
		async sendMessage() {
			if (!this.inputMessage.trim() || this.isLoading) return;

			const message = this.inputMessage.trim();
			console.log('[ChatCommerce] Sending message:', message);
			console.log('[ChatCommerce] Session ID:', this.sessionId);

			if (!this.sessionId) {
				alert('No active session. Please refresh the page.');
				console.error('[ChatCommerce] No session ID! Cannot send message.');
				return;
			}

			this.inputMessage = '';

			// Add user message
			this.messages.push({
				id: Date.now(),
				role: 'user',
				content: message,
				timestamp: new Date(),
			});

			this.scrollToBottom();
			this.isLoading = true;
			this.isTyping = true;

			try {
				await this.streamResponse(message);
			} catch (error) {
				console.error('[ChatCommerce] Chat error:', error);
				this.messages.push({
					id: Date.now(),
					role: 'assistant',
					content: 'Sorry, something went wrong. Please try again.',
					timestamp: new Date(),
					isError: true,
				});
			} finally {
				this.isLoading = false;
				this.isTyping = false;
			}
		},

		// Stream response
		async streamResponse(message) {
			console.log('[ChatCommerce] Calling API:', `${window.chatcommerceAI.apiUrl}/chat/stream`);
			console.log('[ChatCommerce] Request payload:', {
				session_id: this.sessionId,
				message: message,
			});

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

			console.log('[ChatCommerce] Response status:', response.status);
			console.log('[ChatCommerce] Response content-type:', response.headers.get('content-type'));

			if (!response.ok) {
				const errorText = await response.text();
				console.error('[ChatCommerce] API Error:', response.status, errorText);
				throw new Error(`API returned ${response.status}: ${errorText}`);
			}

			if (response.headers.get('content-type')?.includes('text/event-stream')) {
				console.log('[ChatCommerce] Using SSE stream');
				await this.handleSSEStream(response);
			} else {
				console.log('[ChatCommerce] Using regular JSON response');
				const data = await response.json();
				console.log('[ChatCommerce] Response data:', data);

				if (data.success) {
					this.messages.push({
						id: Date.now(),
						role: 'assistant',
						content: data.message,
						timestamp: new Date(),
					});
					this.scrollToBottom();
				} else {
					console.error('[ChatCommerce] Response not successful:', data);
				}
			}
		},

		// Handle SSE stream
		async handleSSEStream(response) {
			const reader = response.body.getReader();
			const decoder = new TextDecoder();

			let assistantMessage = {
				id: Date.now(),
				role: 'assistant',
				content: '',
				timestamp: new Date(),
				isStreaming: true,
			};

			this.messages.push(assistantMessage);
			const messageIndex = this.messages.length - 1;

			try {
				while (true) {
					const { done, value } = await reader.read();
					if (done) break;

					const chunk = decoder.decode(value);
					const lines = chunk.split('\n');

					for (const line of lines) {
						if (line.startsWith('event: error')) {
							// Next line will have error data
							continue;
						}

						if (line.startsWith('data: ')) {
							try {
								const data = JSON.parse(line.substring(6));

								// Handle errors from server
								if (data.error) {
									this.messages[messageIndex].content = '⚠️ Error: ' + data.error + '\n\nPlease check:\n• OpenAI API key is configured\n• You have API credits\n• Try refreshing the page';
									this.messages[messageIndex].isStreaming = false;
									this.messages[messageIndex].isError = true;
									console.error('Chat API Error:', data.error);
									return;
								}

								if (data.chunk) {
									this.messages[messageIndex].content += data.chunk;
									this.scrollToBottom();
								}
							} catch (e) {
								// Ignore parsing errors for [DONE] and other markers
							}
						}
					}
				}

				this.messages[messageIndex].isStreaming = false;
			} catch (error) {
				this.messages[messageIndex].content = '⚠️ Connection error. Please check your internet connection and try again.';
				this.messages[messageIndex].isStreaming = false;
				this.messages[messageIndex].isError = true;
				console.error('Stream error:', error);
			}
		},

		// Submit feedback
		async submitFeedback(messageId, rating) {
			try {
				await fetch(`${window.chatcommerceAI.apiUrl}/feedback`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						session_id: this.sessionId,
						message_id: messageId,
						rating: rating,
					}),
				});

				// Update UI
				const message = this.messages.find(m => m.id === messageId);
				if (message) {
					message.feedback = rating;
				}
			} catch (error) {
				console.error('Failed to submit feedback:', error);
			}
		},

		// Scroll to bottom
		scrollToBottom() {
			this.$nextTick(() => {
				const container = this.$refs.messages;
				if (container) {
					container.scrollTop = container.scrollHeight;
				}
			});
		},

		// Check scroll position
		onScroll() {
			const container = this.$refs.messages;
			if (container) {
				const isNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;
				this.showScrollButton = !isNearBottom;
			}
		},

		// Format time
		formatTime(timestamp) {
			return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		},

		// Handle input keypress
		onKeyPress(event) {
			if (event.key === 'Enter' && !event.shiftKey) {
				event.preventDefault();
				this.sendMessage();
			}
		},
	}));
});
