# ChatCommerce AI - Quick Start Guide

Welcome to ChatCommerce AI! This guide will help you get your AI-powered chatbot up and running in minutes.

## Installation Complete ✅

Your plugin has been successfully created at:
```
wp-content/plugins/chatcommerce-ai/
```

## Next Steps

### 1. Activate the Plugin

```bash
cd /Users/mejba/Herd/wp
wp plugin activate chatcommerce-ai
```

Or via WordPress Admin:
- Go to **Plugins** → **Installed Plugins**
- Find "ChatCommerce AI"
- Click **Activate**

### 2. Get Your OpenAI API Key

1. Visit [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys)
2. Sign in or create an account
3. Click **"Create new secret key"**
4. Copy the key (starts with `sk-...`)
5. **Important:** Save it securely - you won't see it again!

### 3. Configure the Plugin

1. Go to **ChatCommerce AI** → **Settings** in WordPress Admin
2. Navigate to the **AI Settings** tab
3. Paste your OpenAI API key
4. Select your model:
   - **GPT-4 Turbo** (Recommended) - Best quality, moderate speed
   - **GPT-3.5 Turbo** - Faster, lower cost
5. Click **Save Settings**

### 4. Customize Your Chatbot

#### General Settings
- **Enable Chatbot:** Check the box
- **Widget Position:** Choose bottom-right or bottom-left
- **Welcome Message:** Customize the greeting
- **Colors:** Set your brand colors

#### Instructions (Important!)
1. Go to **Settings** → **Instructions**
2. Edit the system prompt to match your brand voice
3. Add specific information about:
   - Your store policies
   - Product categories
   - Shipping details
   - Return policies
4. Click **Save Instructions**

### 5. Sync Your Content

1. Go to **ChatCommerce AI** → **Content Sync**
2. Click **"Run Full Sync Now"**
3. Wait for the sync to complete (may take 1-2 minutes)

This indexes your:
- Products
- Pages
- Posts

So the AI can answer questions about them!

### 6. Test Your Chatbot

1. Visit your website's homepage
2. Look for the chat bubble in the bottom corner
3. Click to open the chat
4. Try asking:
   - "What products do you sell?"
   - "Tell me about shipping"
   - "Do you have [product name]?"

## Verification Checklist

✅ Plugin activated
✅ OpenAI API key configured
✅ Chatbot enabled in settings
✅ Welcome message customized
✅ System instructions edited
✅ Content synced
✅ Chat widget visible on frontend
✅ Test conversation successful

## Common First-Time Setup Issues

### Widget Not Showing
- Verify "Enable Chatbot" is checked in General Settings
- Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
- Check browser console for JavaScript errors

### "API Key Invalid" Error
- Ensure key starts with `sk-`
- No extra spaces when pasting
- Key is active at [platform.openai.com](https://platform.openai.com)

### AI Doesn't Know About Products
- Run content sync: **ChatCommerce AI** → **Content Sync**
- Verify WooCommerce is active
- Check that products are published (not drafts)

## Recommended Settings for Best Results

### For E-commerce Stores:
```
Model: GPT-4 Turbo
Temperature: 0.7
Max Tokens: 500
Function Calling: Enabled
```

### For Service Businesses:
```
Model: GPT-4 Turbo
Temperature: 0.8
Max Tokens: 600
Function Calling: Enabled
```

### For Budget-Conscious Sites:
```
Model: GPT-3.5 Turbo
Temperature: 0.7
Max Tokens: 400
Function Calling: Enabled
```

## Monitoring Costs

Your OpenAI usage is billed by OpenAI, not through this plugin.

**Typical Costs:**
- GPT-3.5 Turbo: ~$0.002-0.004 per conversation
- GPT-4 Turbo: ~$0.01-0.03 per conversation

**To Monitor:**
1. Visit [https://platform.openai.com/usage](https://platform.openai.com/usage)
2. Set up billing alerts
3. Review usage regularly

## Where to Go From Here

### Customize Further
- **Lead Capture:** Enable in Settings → Lead Capture
- **Feedback:** Configure in Settings → Feedback
- **Privacy:** Adjust data retention in Settings → Privacy

### View Analytics
- **Dashboard:** See sessions, leads, CSAT
- **Conversations:** Review chat transcripts
- **Leads:** Export captured leads

### Integrate
- **Email Notifications:** Configure in Lead Capture settings
- **Webhooks:** Send leads to your CRM
- **Shortcode:** Use `[chatcommerce_ai]` to embed chat anywhere

## Support

- **Documentation:** See `/docs` folder
- **Issues:** Check `README.md` for troubleshooting
- **Author:** [Engr Mejba Ahmed](https://www.mejba.me)

## What's Included

This is a **production-ready MVP** with:

✅ OpenAI GPT-4 integration
✅ Streaming responses (SSE)
✅ Product search with function calling
✅ Lead capture with GDPR compliance
✅ Feedback system
✅ Analytics dashboard
✅ Content synchronization
✅ Rate limiting & security
✅ Responsive, accessible UI
✅ Admin settings with 7 tabs
✅ Database schema with 6 tables
✅ REST API with 6 endpoints

**31 PHP files, 2500+ lines of code!**

---

**Ready to go live? Enable the chatbot and start supporting your customers 24/7! 🚀**

For detailed information, see the full [README.md](README.md).
