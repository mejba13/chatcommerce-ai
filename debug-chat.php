<?php
/**
 * ChatCommerce AI Debug Page
 *
 * Visit: /wp-content/plugins/chatcommerce-ai/debug-chat.php
 * This page helps diagnose chat issues
 */

// Load WordPress
require_once __DIR__ . '/../../../../wp-load.php';

// Security check
if (!current_user_can('manage_options')) {
	wp_die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');

$settings = get_option('chatcommerce_ai_settings', array());
?>
<!DOCTYPE html>
<html>
<head>
	<title>ChatCommerce AI Debug</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
			max-width: 1200px;
			margin: 40px auto;
			padding: 20px;
			background: #f5f5f5;
		}
		.card {
			background: white;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 20px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		h1 { color: #333; margin-top: 0; }
		h2 { color: #0284c7; border-bottom: 2px solid #0284c7; padding-bottom: 10px; }
		.status {
			display: inline-block;
			padding: 4px 12px;
			border-radius: 4px;
			font-weight: 600;
			font-size: 14px;
		}
		.status.ok { background: #10b981; color: white; }
		.status.error { background: #ef4444; color: white; }
		.status.warning { background: #f59e0b; color: white; }
		table { width: 100%; border-collapse: collapse; }
		td, th { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
		th { background: #f9fafb; font-weight: 600; }
		code {
			background: #f3f4f6;
			padding: 2px 6px;
			border-radius: 4px;
			font-size: 13px;
		}
		pre {
			background: #1f2937;
			color: #f3f4f6;
			padding: 16px;
			border-radius: 8px;
			overflow-x: auto;
			font-size: 13px;
		}
		.test-btn {
			background: #0284c7;
			color: white;
			border: none;
			padding: 10px 20px;
			border-radius: 6px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 600;
		}
		.test-btn:hover { background: #0369a1; }
		#test-result {
			margin-top: 15px;
			padding: 15px;
			border-radius: 6px;
			display: none;
		}
		#test-result.success { background: #d1fae5; border: 1px solid #10b981; }
		#test-result.error { background: #fee2e2; border: 1px solid #ef4444; }
	</style>
</head>
<body>
	<h1>🔍 ChatCommerce AI Debug</h1>

	<!-- Plugin Status -->
	<div class="card">
		<h2>Plugin Status</h2>
		<table>
			<tr>
				<th>Setting</th>
				<th>Value</th>
				<th>Status</th>
			</tr>
			<tr>
				<td>Plugin Enabled</td>
				<td><?php echo !empty($settings['enabled']) ? 'Yes' : 'No'; ?></td>
				<td><span class="status <?php echo !empty($settings['enabled']) ? 'ok' : 'error'; ?>">
					<?php echo !empty($settings['enabled']) ? 'OK' : 'DISABLED'; ?>
				</span></td>
			</tr>
			<tr>
				<td>OpenAI API Key</td>
				<td><?php echo !empty($settings['openai_api_key']) ? '••••••••••••••••' : 'Not Set'; ?></td>
				<td><span class="status <?php echo !empty($settings['openai_api_key']) ? 'ok' : 'error'; ?>">
					<?php echo !empty($settings['openai_api_key']) ? 'CONFIGURED' : 'MISSING'; ?>
				</span></td>
			</tr>
			<tr>
				<td>AI Model</td>
				<td><code><?php echo $settings['openai_model'] ?? 'gpt-4o-mini'; ?></code></td>
				<td><span class="status ok">OK</span></td>
			</tr>
			<tr>
				<td>Temperature</td>
				<td><?php echo $settings['temperature'] ?? '0.7'; ?></td>
				<td><span class="status ok">OK</span></td>
			</tr>
			<tr>
				<td>Max Tokens</td>
				<td><?php echo $settings['max_tokens'] ?? '500'; ?></td>
				<td><span class="status ok">OK</span></td>
			</tr>
		</table>
	</div>

	<!-- Database Status -->
	<div class="card">
		<h2>Database Status</h2>
		<table>
			<?php
			global $wpdb;
			$tables = [
				'chatcommerce_sessions' => 'Chat Sessions',
				'chatcommerce_messages' => 'Messages',
				'chatcommerce_feedback' => 'Feedback',
				'chatcommerce_leads' => 'Leads',
				'chatcommerce_sync_index' => 'Product Index',
				'chatcommerce_logs' => 'Logs',
			];

			foreach ($tables as $table_suffix => $label) {
				$table_name = $wpdb->prefix . $table_suffix;
				$exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
				$count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;

				echo "<tr>";
				echo "<td>$label</td>";
				echo "<td><code>$table_name</code></td>";
				echo '<td><span class="status ' . ($exists ? 'ok' : 'error') . '">';
				echo $exists ? "OK ($count rows)" : 'NOT FOUND';
				echo '</span></td>';
				echo "</tr>";
			}
			?>
		</table>
	</div>

	<!-- REST API Endpoints -->
	<div class="card">
		<h2>REST API Endpoints</h2>
		<table>
			<tr>
				<th>Endpoint</th>
				<th>URL</th>
				<th>Test</th>
			</tr>
			<tr>
				<td>Session Start</td>
				<td><code><?php echo rest_url('chatcommerce/v1/session/start'); ?></code></td>
				<td><button class="test-btn" onclick="testEndpoint('session/start')">Test</button></td>
			</tr>
			<tr>
				<td>Chat Stream</td>
				<td><code><?php echo rest_url('chatcommerce/v1/chat/stream'); ?></code></td>
				<td><button class="test-btn" onclick="testChat()">Test</button></td>
			</tr>
		</table>
		<div id="test-result"></div>
	</div>

	<!-- JavaScript Console Test -->
	<div class="card">
		<h2>JavaScript Test</h2>
		<p>Open browser console (F12) and run:</p>
		<pre>// Test session creation
fetch('<?php echo rest_url('chatcommerce/v1/session/start'); ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'}
})
.then(r => r.json())
.then(d => console.log('Session:', d))
.catch(e => console.error('Error:', e));

// Test chat (replace SESSION_ID with actual session ID from above)
fetch('<?php echo rest_url('chatcommerce/v1/chat/stream'); ?>', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/event-stream'
    },
    body: JSON.stringify({
        session_id: 'SESSION_ID',
        message: 'Hello, test message'
    })
})
.then(r => console.log('Response:', r))
.catch(e => console.error('Error:', e));</pre>
	</div>

	<!-- Quick Fixes -->
	<div class="card">
		<h2>Quick Fixes</h2>
		<ul>
			<li>
				<strong>API Key Missing:</strong> Go to
				<a href="<?php echo admin_url('admin.php?page=chatcommerce-ai-settings&tab=ai'); ?>" target="_blank">
					Settings → AI Settings
				</a>
				and add your OpenAI API key
			</li>
			<li><strong>Plugin Disabled:</strong> Go to Settings → General and enable the chatbot</li>
			<li><strong>Clear Browser Cache:</strong> Press Cmd/Ctrl + Shift + R</li>
			<li><strong>Check PHP Errors:</strong> Enable WP_DEBUG in wp-config.php</li>
			<li><strong>Check Browser Console:</strong> Press F12 and look for errors</li>
		</ul>
	</div>

	<script>
		async function testEndpoint(endpoint) {
			const resultDiv = document.getElementById('test-result');
			resultDiv.style.display = 'block';
			resultDiv.className = '';
			resultDiv.textContent = 'Testing...';

			try {
				const response = await fetch('<?php echo rest_url('chatcommerce/v1/'); ?>' + endpoint, {
					method: 'POST',
					headers: {'Content-Type': 'application/json'}
				});

				const data = await response.json();
				resultDiv.className = response.ok ? 'success' : 'error';
				resultDiv.textContent = JSON.stringify(data, null, 2);
			} catch (error) {
				resultDiv.className = 'error';
				resultDiv.textContent = 'Error: ' + error.message;
			}
		}

		async function testChat() {
			const resultDiv = document.getElementById('test-result');
			resultDiv.style.display = 'block';
			resultDiv.className = '';
			resultDiv.textContent = 'Creating session...';

			try {
				// First create session
				const sessionResponse = await fetch('<?php echo rest_url('chatcommerce/v1/session/start'); ?>', {
					method: 'POST',
					headers: {'Content-Type': 'application/json'}
				});

				const sessionData = await sessionResponse.json();

				if (!sessionData.success) {
					throw new Error('Failed to create session');
				}

				resultDiv.textContent = 'Session created! Testing chat...';

				// Test chat
				const chatResponse = await fetch('<?php echo rest_url('chatcommerce/v1/chat/stream'); ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'text/event-stream'
					},
					body: JSON.stringify({
						session_id: sessionData.session_id,
						message: 'Test message'
					})
				});

				if (chatResponse.ok) {
					resultDiv.className = 'success';
					resultDiv.textContent = 'Success! Chat API is working. Status: ' + chatResponse.status;
				} else {
					throw new Error('Chat API returned: ' + chatResponse.status);
				}
			} catch (error) {
				resultDiv.className = 'error';
				resultDiv.textContent = 'Error: ' + error.message;
			}
		}
	</script>

	<p style="text-align: center; color: #6b7280; margin-top: 40px;">
		<strong>Delete this file after debugging for security</strong>
	</p>
</body>
</html>
