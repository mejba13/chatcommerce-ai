# ChatCommerce AI - Quick Start Guide

Get your AI chatbot up and running in 5 minutes!

---

## Step 1: Activate Plugin

```bash
wp plugin activate chatcommerce-ai
```

Or via WordPress admin:
1. Go to **Plugins** → **Installed Plugins**
2. Find "ChatCommerce AI"
3. Click **Activate**

---

## Step 2: Configure OpenAI API Key

### Get API Key
1. Visit [OpenAI Platform](https://platform.openai.com/api-keys)
2. Create new API key
3. Copy the key (starts with `sk-`)

### Add to Plugin
1. Go to **WordPress Admin** → **ChatCommerce AI** → **Settings**
2. Click **AI Settings** tab
3. Paste your OpenAI API key
4. Click **Save Settings**

---

## Step 3: Enable Chatbot

1. Go to **Settings** → **General** tab
2. Check ✅ **Enable Chatbot**
3. Customize:
   - Widget Position: `bottom-right` or `bottom-left`
   - Welcome Message: Customize greeting
   - Primary Color: Match your brand
4. Click **Save Settings**

---

## Step 4: Test on Frontend

1. **Visit your site** (any page, not wp-admin)
2. **Look for chat bubble** in bottom corner
3. **Click to open** → should see welcome message
4. **Send test message** → AI should respond

---

## ✅ Verification Checklist

- [ ] Plugin activated
- [ ] OpenAI API key configured
- [ ] Widget enabled
- [ ] Chat bubble visible on frontend
- [ ] Welcome message appears
- [ ] AI responds to messages
- [ ] Messages save (check Dashboard)

---

## 🎨 Customization Options

### General Settings
- **Enable/Disable**: Turn chatbot on/off
- **Position**: bottom-right, bottom-left
- **Welcome Message**: First message users see
- **Brand Logo**: Upload your logo for widget header
- **Colors**: Primary, background, text colors

### AI Settings
- **OpenAI Model**: gpt-4-turbo-preview (recommended)
- **Temperature**: 0.7 (creativity level)
- **Max Tokens**: 500 (response length)

### Instructions (Advanced)
- **System Prompt**: Customize AI personality
- **Guidelines**: Set response rules
- **Brand Voice**: Match your company tone

### Lead Capture
- **Enable**: Collect visitor information
- **Fields**: Name, email, phone
- **Trigger**: After X messages or on request

---

## 🔍 Quick Troubleshooting

### Chat bubble not showing?
```bash
# Check if enabled
wp option get chatcommerce_ai_settings --format=json | grep enabled

# Should show: "enabled":true
```

### AI not responding?
1. Verify OpenAI API key is entered
2. Check API key has credits at [OpenAI Usage](https://platform.openai.com/usage)
3. Check browser console for errors (F12)

### Widget looks broken?
1. Hard refresh browser: `Ctrl+Shift+R` (Windows) / `Cmd+Shift+R` (Mac)
2. Clear WordPress cache
3. Check if Tailwind CSS file exists:
   ```bash
   ls wp-content/plugins/chatcommerce-ai/assets/css/widget.css
   ```

---

## 📊 Check Your Stats

Go to **ChatCommerce AI** → **Dashboard** to see:

- 💬 **Total Sessions**: Number of conversations
- 📨 **Messages**: Total messages exchanged
- 👤 **Leads Captured**: Contact information collected
- 😊 **CSAT**: Customer satisfaction percentage

---

## 🚀 Next Steps

1. **Customize Instructions**
   - Go to Settings → Instructions
   - Add product info, policies, FAQs
   - Tailor AI personality to your brand

2. **Sync Content**
   - Go to Content Sync page
   - Click "Sync Now"
   - AI will learn about your products

3. **Enable Lead Capture**
   - Go to Settings → Lead Capture
   - Choose fields to collect
   - Set when to ask (after X messages)

4. **Review Conversations**
   - Go to Conversations page
   - See what customers are asking
   - Improve responses over time

---

## 🆘 Need Help?

- 📖 **Full Documentation**: `/docs/WIDGET-TESTING-GUIDE.md`
- 🎨 **Design System**: `/docs/DESIGN-SYSTEM.md`
- 🐛 **Issues**: [GitHub Issues](https://github.com/yourusername/chatcommerce-ai/issues)
- 💬 **Support**: support@yourcompany.com

---

## 🎯 Best Practices

### 1. Write Clear Instructions
- Tell AI about your products/services
- Include common FAQs
- Set tone and personality

### 2. Monitor Conversations
- Review regularly for insights
- Identify knowledge gaps
- Improve system prompts

### 3. Optimize Performance
- Sync content regularly
- Keep instructions concise
- Use appropriate model (GPT-4 for accuracy)

### 4. Engage Users
- Welcoming greeting
- Quick responses
- Helpful suggestions

---

## 🎉 You're All Set!

Your AI chatbot is now live and ready to help customers 24/7.

**What happens next:**
- Visitors can chat instantly
- AI answers questions using your content
- Leads are captured automatically
- Stats tracked in dashboard

**Monitor & Improve:**
- Check dashboard weekly
- Review conversations monthly
- Update instructions based on feedback
- Sync new products/content

---

**Version**: 1.0.0
**Last Updated**: 2025-10-30
