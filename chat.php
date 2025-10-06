<?php
// Fixed chat.php: Robust parsing for empty/malformed responses (handles MAX_TOKENS, missing parts)
// Confirmed model: gemini-2.5-flash (stable Oct 2025)
error_reporting(E_ALL);
ini_set('display_errors', 1);  // TEMP: Remove for production
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');  // Logs to file in same dir

// Include DB connection
try {
    include 'db.php';
} catch (Exception $e) {
    die("DB Include Error: " . $e->getMessage());
}

// Handle AJAX request for new message (if POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json');
    $message = trim($_POST['message']);
    if (!empty($message)) {
        try {
            // Generate AI response using Gemini API
            $apiKey = 'AIzaSyB6XMmzkUAeEPwqRPbvwoy-zeO-yUkciaA';
            $model = 'gemini-2.5-flash';  // Stable Oct 2025 model
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
            
            // Added system instruction for better short-prompt handling
            $systemPrompt = "You are a helpful AI assistant like Copilot. Respond concisely and usefully.";
            $data = json_encode([
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $message]
                        ]
                    ]
                ],
                'systemInstruction' => [  // Helps with consistent responses
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,  // Increased for fuller responses
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ]
            ]);
            
            $ch = curl_init();
            if (!$ch) {
                throw new Exception('cURL init failed');
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Testing; enable prod
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new Exception('cURL Error: ' . $curlError);
            }
            
            error_log("Full API Response: " . $response);  // Log full for debug
            
            $aiResponse = '';
            if ($httpCode === 200) {
                $decoded = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSON Decode Error: ' . json_last_error_msg());
                }
                
                $candidate = $decoded['candidates'][0] ?? null;
                if ($candidate && isset($candidate['content']['parts'][0]['text'])) {
                    $aiResponse = trim($candidate['content']['parts'][0]['text']);
                } elseif ($candidate && isset($candidate['finishReason'])) {
                    // Handle empty/malformed: e.g., MAX_TOKENS with no text
                    $reason = $candidate['finishReason'];
                    $aiResponse = "No content generated. Reason: $reason (possibly short prompt or limits). Try a more detailed query! Raw hint: " . substr($response, 0, 300);
                } elseif (isset($decoded['prompt_feedback']['blockReason'])) {
                    $blockReason = $decoded['prompt_feedback']['blockReason'];
                    $aiResponse = "Response blocked due to safety guidelines ($blockReason). Rephrase your prompt.";
                } else {
                    $aiResponse = 'Sorry, couldn\'t parse AI response. Check logs for details. Raw: ' . substr($response, 0, 300);
                }
            } else {
                if ($httpCode === 404) {
                    $aiResponse = 'Model not found (404). Update to latest Gemini model. Response: ' . substr($response, 0, 200);
                } else {
                    $aiResponse = 'API error (Code: ' . $httpCode . '). Check key/limits. Response: ' . substr($response, 0, 200);
                }
            }
            
            // Save to DB
            $stmt = $pdo->prepare("INSERT INTO conversations (message, response) VALUES (?, ?)");
            $stmt->execute([$message, $aiResponse]);
            
            echo json_encode(['response' => $aiResponse]);
        } catch (Exception $e) {
            $errorMsg = 'Error: ' . $e->getMessage();
            echo json_encode(['response' => $errorMsg]);
            error_log("Chat Error: " . $errorMsg . " | Model: $model | Prompt: $message");
        }
        exit;
    }
    echo json_encode(['response' => 'Empty message. Type something!']);
    exit;
}

// Fetch recent conversations for display (last 20)
try {
    $stmt = $pdo->query("SELECT * FROM conversations ORDER BY created_at DESC LIMIT 20");
    $conversations = $stmt->fetchAll();
} catch (PDOException $e) {
    $conversations = [];
    error_log("DB Query Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Copilot Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Premium CSS: Unchanged, for that real Copilot vibe */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); height: 100vh; display: flex; flex-direction: column; }
        .header { background: linear-gradient(135deg, #1a73e8, #4285f4); color: white; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .header h1 { font-size: 1.8em; margin-bottom: 5px; }
        .chat-container { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; }
        .message { max-width: 70%; padding: 12px 18px; border-radius: 20px; animation: slideIn 0.3s ease; position: relative; word-wrap: break-word; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        .user-message { align-self: flex-end; background: linear-gradient(135deg, #1a73e8, #4285f4); color: white; border-bottom-right-radius: 5px; }
        .ai-message { align-self: flex-start; background: white; color: #333; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border-bottom-left-radius: 5px; }
        .message-time { font-size: 0.8em; color: #999; margin-top: 5px; }
        .input-area { background: white; padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        #messageInput { flex: 1; padding: 12px 18px; border: 1px solid #ddd; border-radius: 25px; font-size: 1em; outline: none; transition: border 0.3s ease; }
        #messageInput:focus { border-color: #1a73e8; box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.1); }
        #sendBtn { background: linear-gradient(135deg, #1a73e8, #4285f4); color: white; border: none; padding: 12px 20px; border-radius: 25px; cursor: pointer; transition: transform 0.3s ease; }
        #sendBtn:hover { transform: scale(1.05); }
        #sendBtn:disabled { opacity: 0.6; cursor: not-allowed; }
        .save-btn { background: #34a853; color: white; border: none; padding: 5px 10px; border-radius: 15px; cursor: pointer; margin-left: 10px; font-size: 0.8em; transition: background 0.3s ease; }
        .save-btn:hover { background: #2e7d32; }
        .save-btn.saved { background: #ff9800; }
        @media (max-width: 768px) { .message { max-width: 85%; } .input-area { padding: 15px; } }
        .error-display { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin: 10px; border-left: 4px solid #f44336; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🤖 Chat with Copilot</h1>
        <p>Ask anything – from code to creative writing!</p>
    </div>
    
    <div class="chat-container" id="chatContainer">
        <?php if (empty($conversations)): ?>
            <div style="text-align: center; color: #999; margin-top: 50px;">
                <p>Welcome! Start a conversation below.</p>
            </div>
        <?php else: ?>
            <?php foreach (array_reverse($conversations) as $conv): ?>
                <div class="message user-message"><?= htmlspecialchars($conv['message']) ?></div>
                <div class="message ai-message">
                    <?= nl2br(htmlspecialchars($conv['response'])) ?>
                    <div class="message-time"><?= date('M j, Y g:i A', strtotime($conv['created_at'])) ?></div>
                    <button class="save-btn <?= $conv['is_saved'] ? 'saved' : '' ?>" onclick="toggleSave(<?= $conv['id'] ?>)">
                        <?= $conv['is_saved'] ? 'Saved' : 'Save' ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="input-area">
        <input type="text" id="messageInput" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
        <button id="sendBtn" onclick="sendMessage()">Send</button>
    </div>

    <script>
        // JS unchanged, but added better formatting
        let isSaved = {};
        
        function handleKeyPress(event) {
            if (event.key === 'Enter') sendMessage();
        }
        
        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const btn = document.getElementById('sendBtn');
            const message = input.value.trim();
            if (!message) return;
            
            input.disabled = true;
            btn.disabled = true;
            btn.textContent = 'Sending...';
            
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.innerHTML += `<div class="message user-message">${message}</div>`;
            input.value = '';
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `message=${encodeURIComponent(message)}`
                });
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                const data = await response.json();
                
                const formattedResponse = data.response.replace(/\n/g, '<br>');
                chatContainer.innerHTML += `
                    <div class="message ai-message ${data.response.includes('Error') || data.response.includes('blocked') ? 'error-display' : ''}">
                        ${formattedResponse}
                        <div class="message-time">${new Date().toLocaleString()}</div>
                        <button class="save-btn" onclick="toggleSave(-1)">Save</button>
                    </div>
                `;
            } catch (error) {
                chatContainer.innerHTML += `<div class="message ai-message error-display">Error: ${error.message}. Check console/logs.</div>`;
                console.error('Send Error:', error);
            } finally {
                input.disabled = false;
                btn.disabled = false;
                btn.textContent = 'Send';
                input.focus();
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }
        
        function toggleSave(id) {
            const btn = event.target;
            btn.classList.toggle('saved');
            btn.textContent = btn.classList.contains('saved') ? 'Saved' : 'Save';
        }
    </script>
</body>
</html>
