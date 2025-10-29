# ChatCommerce AI Widget - Testing & Troubleshooting Guide

**Version**: 1.0.0
**Last Updated**: 2025-10-30

---

## ✅ Pre-Flight Checklist

Before testing the chat widget, ensure:

1. **Plugin is Activated**
   - Go to `wp-admin` → Plugins
   - Verify "ChatCommerce AI" shows "Active"

2. **Database Tables Created**
   - Run: `wp db query "SHOW TABLES LIKE 'wp_chatcommerce_%'"`
   - Should see 6 tables:
     - `wp_chatcommerce_sessions`
     - `wp_chatcommerce_messages`
     - `wp_chatcommerce_feedback`
     - `wp_chatcommerce_leads`
     - `wp_chatcommerce_sync_index`
     - `wp_chatcommerce_logs`

3. **OpenAI API Key Configured**
   - Go to: `wp-admin` → ChatCommerce AI → Settings → AI Settings
   - Enter your OpenAI API key
   - **IMPORTANT**: Without this, the chat will not work!

4. **Widget Enabled**
   - Go to: `wp-admin` → ChatCommerce AI → Settings → General
   - Check "Enable Chatbot"
   - Click "Save Settings"

---

## 🧪 Testing Procedure

### Step 1: Verify Widget Appears

1. **Visit Frontend**
   - Open your site's homepage (not wp-admin)
   - Look for chat bubble in bottom-right corner
   - Should see animated bubble with gradient

2. **Check Widget Position**
   - Settings → General → Widget Position
   - Try both `bottom-right` and `bottom-left`
   - Refresh frontend to verify

### Step 2: Test Basic Chat Flow

1. **Click Chat Bubble**
   - Widget should slide up
   - Header shows site name
   - Welcome message appears

2. **Send Test Message**
   - Type: "Hello"
   - Press Enter or click Send button
   - Should see:
     - User message (blue bubble, right side)
     - "Typing..." indicator
     - AI response (white bubble, left side)

3. **Verify Streaming**
   - AI response should "stream" in character-by-character
   - Three dots animate while streaming

### Step 3: Test Feedback System

1. **Hover over AI message**
   - Thumbs up/down buttons should appear
   - Click thumbs up → should turn blue
   - Click thumbs down → should turn red

### Step 4: Test Session Persistence

1. **Send multiple messages**
   - Chat should maintain conversation context
   - Check wp-admin → Conversations
   - Your session should appear in table

---

## 🔍 Verification Commands

### Check Plugin Status
```bash
wp plugin list --format=table | grep chatcommerce
```

### Check Settings
```bash
wp option get chatcommerce_ai_settings --format=json
```

### Check Sessions
```bash
wp db query "SELECT COUNT(*) as total FROM wp_chatcommerce_sessions;"
```

### Check Messages
```bash
wp db query "SELECT COUNT(*) as total FROM wp_chatcommerce_messages;"
```

### Check Recent Conversations
```bash
wp db query "SELECT s.session_id, s.started_at, s.message_count, COUNT(m.id) as actual_messages
FROM wp_chatcommerce_sessions s
LEFT JOIN wp_chatcommerce_messages m ON s.session_id = m.session_id
GROUP BY s.session_id
ORDER BY s.started_at DESC
LIMIT 10;"
```

---

## 🚨 Troubleshooting

### Widget Not Appearing

**Problem**: Chat bubble doesn't show on frontend

**Solutions**:

1. **Check if enabled**
   ```bash
   wp option get chatcommerce_ai_settings --format=json | grep enabled
   ```
   Should show: `"enabled":true` or `"enabled":"1"`

2. **Check browser console for errors**
   - Open DevTools (F12)
   - Look for JavaScript errors
   - Check if Alpine.js loaded

3. **Verify assets are loading**
   - View page source (Ctrl+U)
   - Search for: `chatcommerce-ai-widget`
   - Should find CSS and JS files

4. **Clear cache**
   ```bash
   wp cache flush
   ```

5. **Check if Alpine.js CDN is accessible**
   - Open: https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js
   - Should download JavaScript file

### "No Active Session" Error

**Problem**: Message says "No active session. Please refresh."

**Solutions**:

1. **Check REST API is working**
   - Open: `https://yoursite.com/wp-json/chatcommerce/v1/session/start`
   - Should return: `{"error":"Rest route expects POST request method."}`
   - (This is expected - shows endpoint exists)

2. **Test session creation**
   ```bash
   curl -X POST https://yoursite.com/wp-json/chatcommerce/v1/session/start
   ```
   Should return JSON with `session_id`

3. **Check .htaccess / nginx config**
   - Ensure REST API routes are not blocked
   - WordPress permalinks must be enabled

4. **Check browser console**
   - Look for `[ChatCommerce] Starting session`
   - Check if API URL is correct

### "API Key Not Configured" Error

**Problem**: AI doesn't respond or shows error message

**Solutions**:

1. **Verify API key is set**
   ```bash
   wp option get chatcommerce_ai_settings --format=json | grep openai_api_key
   ```

2. **Test OpenAI connection manually**
   - Go to: wp-admin → ChatCommerce AI → Settings → AI Settings
   - Click "Test Connection" (if available)

3. **Check OpenAI API key validity**
   - Visit: https://platform.openai.com/api-keys
   - Ensure key has sufficient credits
   - Check key hasn't expired

4. **Verify API key encryption**
   - API keys are encrypted in database
   - If you migrated database, re-enter the key

### Streaming Not Working

**Problem**: Response appears all at once instead of streaming

**Solutions**:

1. **Check if SSE is supported**
   - Most modern browsers support Server-Sent Events
   - Check browser console for SSE connection messages

2. **Check server buffering**
   - Some servers (nginx, Apache) buffer output
   - Widget will fallback to regular (non-streaming) mode

3. **Verify response headers**
   - Open DevTools → Network tab
   - Send message
   - Check `/chat/stream` request
   - Response should have `Content-Type: text/event-stream`

### Messages Not Saving to Database

**Problem**: Dashboard shows 0 sessions/messages but chat works

**Solutions**:

1. **Check database connection**
   ```bash
   wp db check
   ```

2. **Verify table structure**
   ```bash
   wp db query "DESCRIBE wp_chatcommerce_sessions;"
   wp db query "DESCRIBE wp_chatcommerce_messages;"
   ```

3. **Check WordPress error logs**
   - Enable debug: `define('WP_DEBUG', true);` in wp-config.php
   - Check: `wp-content/debug.log`

4. **Test direct insert**
   ```bash
   wp db query "INSERT INTO wp_chatcommerce_messages (session_id, role, content, created_at) VALUES ('test-123', 'user', 'Test message', NOW());"
   ```

### Widget Styling Issues

**Problem**: Widget looks broken or CSS not loading

**Solutions**:

1. **Verify CSS file exists**
   ```bash
   ls -la wp-content/plugins/chatcommerce-ai/assets/css/widget.css
   ```

2. **Check if CSS is enqueued**
   - View page source
   - Search for: `chatcommerce-ai-widget.css`

3. **Clear browser cache**
   - Hard refresh: Ctrl+Shift+R (Windows) / Cmd+Shift+R (Mac)

4. **Rebuild Tailwind CSS** (if modified)
   ```bash
   cd wp-content/plugins/chatcommerce-ai
   npm run build
   ```

5. **Check for CSS conflicts**
   - Theme might override styles
   - Use browser DevTools to inspect

---

## 🧰 Developer Tools

### Enable Debug Mode

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### JavaScript Console Logging

The widget logs all actions to console with `[ChatCommerce]` prefix.

**Example logs to look for**:
```
[ChatCommerce] Starting session with API URL: ...
[ChatCommerce] Session ID: abc-123-def
[ChatCommerce] Sending message: Hello
[ChatCommerce] Using SSE stream
```

### Network Tab Debugging

1. Open DevTools → Network tab
2. Filter: "chatcommerce"
3. Send message
4. Should see:
   - `/session/start` (POST) → Status 200
   - `/chat/stream` (POST) → Status 200

### Test REST API Endpoints

**Start Session**:
```bash
curl -X POST https://yoursite.com/wp-json/chatcommerce/v1/session/start \
  -H "Content-Type: application/json"
```

**Send Chat Message**:
```bash
curl -X POST https://yoursite.com/wp-json/chatcommerce/v1/chat/stream \
  -H "Content-Type: application/json" \
  -H "Accept: text/event-stream" \
  -d '{"session_id":"YOUR_SESSION_ID","message":"Hello"}'
```

---

## 📱 Mobile Testing

### iOS Safari
- Widget should be responsive
- Touch interactions should work
- Textarea should resize on keyboard

### Android Chrome
- Same as iOS
- Check if virtual keyboard doesn't obscure input

### Responsive Breakpoints

Test at these widths:
- **Mobile**: 375px (iPhone SE)
- **Tablet**: 768px (iPad)
- **Desktop**: 1280px

Widget automatically adapts:
- Mobile: Full width (max 420px)
- Desktop: Fixed 420px width

---

## 🔐 Security Checklist

✅ **API Keys**
- Never logged or exposed to frontend
- Encrypted in database

✅ **User Input**
- Sanitized before database storage
- Escaped on output

✅ **Rate Limiting**
- Prevents spam and abuse
- Configurable limits

✅ **CORS**
- REST API only accepts POST requests
- Nonces required for admin endpoints

✅ **Privacy**
- IP addresses anonymized (last octet removed)
- GDPR-compliant data retention

---

## 📊 Performance Benchmarks

### Target Metrics

| Metric | Target | How to Test |
|--------|--------|-------------|
| Widget Load Time | < 500ms | DevTools → Performance |
| First Message Response | < 2s | Send "Hello", measure to first token |
| Streaming Token Rate | 10-20 tok/s | Watch response stream |
| Widget Bundle Size | < 85KB gzipped | DevTools → Network → JS files |
| CSS Bundle Size | < 30KB gzipped | DevTools → Network → CSS files |

### Lighthouse Audit

Run from DevTools:
1. DevTools → Lighthouse tab
2. Select "Performance" + "Accessibility"
3. Click "Generate report"

**Targets**:
- Performance: ≥ 90
- Accessibility: 100
- Best Practices: ≥ 90

---

## 🆘 Common Error Messages

### "Failed to start chat session"
**Cause**: REST API not accessible or database error
**Fix**: Check REST API endpoints, verify database tables exist

### "Could not connect to chat service"
**Cause**: Network error or API URL incorrect
**Fix**: Check browser console for exact error, verify site URL

### "Too many requests"
**Cause**: Rate limit exceeded
**Fix**: Wait 1 minute, or increase rate limits in settings

### "Invalid session ID"
**Cause**: Session expired or doesn't exist in database
**Fix**: Refresh page to start new session

### "Sorry, something went wrong"
**Cause**: OpenAI API error or internal server error
**Fix**: Check OpenAI API status, verify API key, check error logs

---

## 📞 Support Resources

### Documentation
- [Design System](./DESIGN-SYSTEM.md)
- [Phase 1 Report](./PHASE-1-COMPLETE.md)
- [Changelog](../CHANGELOG-REDESIGN.md)

### WordPress Resources
- [REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Debugging in WordPress](https://wordpress.org/documentation/article/debugging-in-wordpress/)

### OpenAI Resources
- [API Documentation](https://platform.openai.com/docs)
- [API Status](https://status.openai.com/)

### Alpine.js Resources
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Alpine.js GitHub](https://github.com/alpinejs/alpine)

---

## ✅ Success Criteria

Widget is working properly when:

✅ Chat bubble appears on all frontend pages
✅ Widget opens smoothly with animation
✅ Welcome message displays immediately
✅ User messages send successfully
✅ AI responses stream in real-time
✅ Feedback buttons work (thumbs up/down)
✅ Conversations save to database
✅ Dashboard shows accurate metrics
✅ Mobile responsive (< 768px)
✅ Keyboard accessible (Tab navigation)
✅ No JavaScript console errors
✅ No PHP errors in logs

---

## 🎓 Training Checklist

For new team members testing the widget:

- [ ] Read this guide completely
- [ ] Verify plugin is activated
- [ ] Configure OpenAI API key
- [ ] Enable widget in settings
- [ ] Test basic chat flow
- [ ] Send 5+ test messages
- [ ] Try feedback buttons
- [ ] Check conversations in admin
- [ ] Test on mobile device
- [ ] Test keyboard navigation
- [ ] Review browser console logs
- [ ] Review database records

---

**Last Updated**: 2025-10-30
**Maintainer**: ChatCommerce AI Team
**Version**: 1.0.0
