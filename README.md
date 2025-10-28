# ChatCommerce AI

> 24/7 AI support and lead capture for WooCommerce with OpenAI-powered chat, product-aware responses, and modern, customizable UI.

**Author:** Engr Mejba Ahmed - [https://www.mejba.me](https://www.mejba.me)
**Version:** 1.0.0
**License:** GPL v2 or later

---

## Overview

ChatCommerce AI is a production-grade WordPress plugin that brings intelligent, AI-powered customer support to your WooCommerce store. Built with OpenAI's GPT models, it provides instant, accurate responses about products, shipping, returns, and general store policies while capturing leads and gathering valuable customer feedback.

## Features

### Core Features

- ✅ **OpenAI Integration** - Powered by GPT-4 Turbo for intelligent conversations
- ✅ **Product-Aware Responses** - Automatic product catalog sync with full-text search
- ✅ **Real-Time Streaming** - Server-Sent Events (SSE) for smooth, live responses
- ✅ **Lead Capture** - GDPR-compliant lead collection with explicit consent
- ✅ **Feedback System** - Thumbs up/down ratings with optional comments
- ✅ **Function Calling** - AI can search products, check stock, and retrieve policies
- ✅ **Analytics Dashboard** - Track sessions, leads, CSAT, and more
- ✅ **Customizable UI** - Brand colors, positioning, welcome messages, and logo
- ✅ **Privacy Controls** - Data retention, IP anonymization, telemetry opt-in

### Technical Highlights

- Modern PHP 8.1+ with namespaced architecture
- WordPress REST API with streaming support
- Rate limiting and security hardening
- Full WooCommerce integration
- Responsive, accessible chat widget (WCAG 2.1 AA)
- Database-backed conversation history
- Automatic content/product synchronization

## Requirements

- **PHP:** 8.1 or higher
- **WordPress:** 6.4 or higher
- **WooCommerce:** 8.0 or higher
- **MySQL:** 5.7+ or MariaDB 10.4+
- **OpenAI API Key:** Required (get at [platform.openai.com](https://platform.openai.com))

## Installation

1. **Upload the plugin:**
   ```bash
   cd wp-content/plugins
   git clone <repository-url> chatcommerce-ai
   ```

2. **Activate the plugin:**
   - Go to WordPress Admin → Plugins
   - Find "ChatCommerce AI" and click "Activate"

3. **Configure OpenAI:**
   - Navigate to ChatCommerce AI → Settings → AI Settings
   - Enter your OpenAI API key
   - Select your preferred model (GPT-4 Turbo recommended)

4. **Customize & Enable:**
   - Configure General Settings (colors, position, welcome message)
   - Edit system instructions to match your brand voice
   - Enable the chatbot
   - Run initial content sync

## Configuration

### Settings Tabs

#### 1. General
- Enable/disable chatbot
- Widget position (bottom-right/left)
- Welcome message
- Brand colors and logo

#### 2. AI Settings
- OpenAI API key (encrypted)
- Model selection (GPT-4 Turbo, GPT-4, GPT-3.5 Turbo)
- Temperature (0.0-2.0)
- Max tokens
- Function calling toggle

#### 3. Knowledge & Sync
- Content types to index (Posts, Pages, Products)
- Sync schedule (hourly, daily, manual)
- Manual sync trigger

#### 4. Instructions
- System prompt editor with Markdown support
- Variables: `{site_name}`, `{store_url}`, `{currency}`
- Define AI personality and guidelines

#### 5. Lead Capture
- Enable lead capture
- Fields to request (name, email, phone)
- Consent text customization
- Email notifications
- Webhook integration

#### 6. Feedback
- Enable thumbs up/down ratings
- Optional comment collection
- NPS survey settings

#### 7. Privacy
- Data retention period (7-365 days)
- IP address storage toggle
- Analytics anonymization
- GDPR/CCPA mode
- Telemetry opt-in

## Architecture

### Directory Structure

```
chatcommerce-ai/
├── chatcommerce-ai.php         # Main plugin file
├── src/
│   ├── Admin/                  # Admin interface
│   │   └── AdminController.php
│   ├── AI/                     # OpenAI integration
│   │   ├── OpenAIClient.php
│   │   └── Tools/              # Function calling tools
│   │       ├── ProductSearchTool.php
│   │       ├── StockCheckTool.php
│   │       └── PolicyTool.php
│   ├── API/                    # REST endpoints
│   │   ├── Router.php
│   │   └── Endpoints/
│   │       ├── SessionEndpoint.php
│   │       ├── ChatEndpoint.php
│   │       ├── FeedbackEndpoint.php
│   │       ├── LeadEndpoint.php
│   │       ├── SuggestionsEndpoint.php
│   │       └── StatusEndpoint.php
│   ├── Core/                   # Bootstrap
│   │   ├── Plugin.php
│   │   ├── Activator.php
│   │   └── Deactivator.php
│   ├── Security/               # Security components
│   │   └── RateLimiter.php
│   ├── Sync/                   # Content sync
│   │   └── SyncManager.php
│   └── Widget/                 # Frontend widget
│       └── WidgetLoader.php
├── assets/
│   ├── js/
│   │   ├── widget.js           # Chat widget (vanilla JS)
│   │   └── admin.js            # Admin interface
│   └── css/
│       ├── widget.css          # Widget styles
│       └── admin.css           # Admin styles
├── templates/
│   └── admin/                  # Admin page templates
├── languages/                  # i18n files
├── docs/                       # Documentation
├── tests/                      # Unit/integration tests
├── package.json
├── composer.json
├── readme.txt                  # WordPress.org readme
└── README.md                   # This file
```

### Database Schema

The plugin creates 6 custom tables:

- `wp_chatcommerce_sessions` - Chat sessions
- `wp_chatcommerce_messages` - Individual messages
- `wp_chatcommerce_feedback` - User ratings/comments
- `wp_chatcommerce_leads` - Captured lead data
- `wp_chatcommerce_sync_index` - Indexed content (full-text search)
- `wp_chatcommerce_logs` - System logs

### REST API Endpoints

All endpoints use the namespace `chatcommerce/v1`:

- `POST /session/start` - Start new chat session
- `POST /chat/stream` - Send message (supports SSE streaming)
- `POST /feedback` - Submit message rating
- `POST /lead` - Capture lead information
- `GET /suggestions` - Get quick reply suggestions
- `GET /status` - System status (admin-only)

## Development

### Local Setup

```bash
# Install dependencies (optional for dev tools)
composer install --dev
npm install

# Run tests (when configured)
composer test
npm test
```

### Coding Standards

This plugin follows:
- WordPress Coding Standards (PHPCS)
- PHPStan Level 6 static analysis
- ESLint for JavaScript

### Hooks & Filters

**Actions:**
- `chatcommerce_ai_trigger_sync` - Trigger full content sync
- `chatcommerce_ai_sync_content` - Scheduled sync (cron)

**Filters:**
- `chatcommerce_ai_system_prompt` - Modify system prompt before sending to AI
- `chatcommerce_ai_tools` - Add custom function calling tools

## Usage

### Shortcode

Display the chat widget anywhere:
```php
[chatcommerce_ai]
```

### Programmatic Access

```php
// Get plugin instance
$plugin = \ChatCommerceAI\Core\Plugin::instance();

// Trigger manual sync
do_action('chatcommerce_ai_trigger_sync');

// Access settings
$settings = get_option('chatcommerce_ai_settings');
```

## Security

- API keys encrypted with OpenSSL (AES-256-CBC)
- Nonce verification on all requests
- Rate limiting (20 requests/minute per session)
- SQL injection prevention (prepared statements)
- XSS protection (sanitization/escaping)
- CSRF protection (WordPress nonces)
- IP anonymization for privacy

## Performance

- Lazy loading (assets only when chatbot enabled)
- Object caching for repeated queries
- Transients for rate limiting
- Full-text search indexing
- SSE streaming (reduced latency)
- Asset payload < 80KB gzipped

## Compliance

- **GDPR:** Explicit consent for lead capture, data export/deletion, privacy controls
- **CCPA:** Data retention policies, opt-out mechanisms
- **WCAG 2.1 AA:** Accessible chat interface with ARIA labels, keyboard navigation

## Troubleshooting

### Chat widget not appearing
- Verify chatbot is enabled in General Settings
- Check that OpenAI API key is configured
- Clear browser cache and try again

### "Invalid API key" error
- Ensure API key starts with "sk-"
- Verify key is active at platform.openai.com
- Check for extra spaces when pasting

### Slow responses
- Switch to GPT-3.5 Turbo for faster (but less accurate) responses
- Reduce max_tokens setting
- Check your internet connection

### Content not syncing
- Go to ChatCommerce AI → Content Sync
- Click "Run Full Sync Now"
- Check that WooCommerce is active

## Support & Documentation

- **Issues:** [GitHub Issues](https://github.com/username/chatcommerce-ai/issues)
- **Docs:** See `/docs` folder
- **Author:** [Engr Mejba Ahmed](https://www.mejba.me)

## License

GPLv2 or later. See [LICENSE](LICENSE) file.

---

## Credits

Built by **Engr Mejba Ahmed** with ❤️ for the WordPress community.

- **Website:** [https://www.mejba.me](https://www.mejba.me)
- **Powered by:** OpenAI GPT-4

## Changelog

### 1.0.0 (2025-01-XX)
- Initial release
- OpenAI GPT-4 Turbo integration
- Product catalog sync with full-text search
- Server-Sent Events (SSE) streaming
- Lead capture with GDPR consent
- Feedback system (thumbs up/down)
- Analytics dashboard
- Function calling (product search, stock check)
- Rate limiting and security
- WCAG 2.1 AA compliance
