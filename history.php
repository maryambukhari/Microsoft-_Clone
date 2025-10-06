<?php
// Include DB connection
include 'db.php';

// Fetch all conversations, prioritize saved ones
$stmt = $pdo->query("
    SELECT * FROM conversations 
    ORDER BY is_saved DESC, created_at DESC
");
$conversations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat History - Copilot Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Elegant CSS: Clean history list with cards, hover effects, and Copilot vibes */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2em; }
        .history-list {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }
        .history-item {
            background: #f8f9fa;
            margin-bottom: 15px;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #1a73e8;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .history-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .history-item.saved {
            border-left-color: #ff9800;
            background: #fff3e0;
        }
        .message-preview {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .response-preview {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8em;
            color: #999;
        }
        .btn {
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        .btn:hover { transform: scale(1.05); }
        .delete-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 0.8em;
        }
        .delete-btn:hover { background: #d32f2f; }
        @media (max-width: 768px) {
            .history-list { padding: 10px; }
            .history-item { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📜 Chat History</h1>
            <p>Review your past conversations and saved responses.</p>
        </div>
        
        <div class="history-list">
            <?php if (empty($conversations)): ?>
                <div style="text-align: center; color: #999; padding: 40px;">
                    <p>No conversations yet. Start chatting!</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="history-item <?= $conv['is_saved'] ? 'saved' : '' ?>">
                        <div class="message-preview">You: <?= htmlspecialchars(substr($conv['message'], 0, 50)) ?>...</div>
                        <div class="response-preview">AI: <?= htmlspecialchars(substr($conv['response'], 0, 100)) ?>...</div>
                        <div class="item-footer">
                            <span><?= date('M j, Y g:i A', strtotime($conv['created_at'])) ?></span>
                            <div>
                                <button class="save-btn <?= $conv['is_saved'] ? 'saved' : '' ?>" onclick="toggleSave(<?= $conv['id'] ?>)">
                                    <?= $conv['is_saved'] ? 'Saved' : 'Save' ?>
                                </button>
                                <button class="delete-btn" onclick="deleteConv(<?= $conv['id'] ?>)">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="padding: 20px; text-align: center;">
            <button class="btn" onclick="goToChat()">New Chat</button>
            <button class="btn" style="background: linear-gradient(135deg, #34a853, #7cb342); margin-left: 10px;" onclick="goHome()">Home</button>
        </div>
    </div>

    <script>
        // JS for history interactions and redirections
        function toggleSave(id) {
            // Simulate toggle (real: AJAX to update DB)
            location.reload(); // Refresh to show change (pro: use fetch)
        }
        
        function deleteConv(id) {
            if (confirm('Delete this conversation?')) {
                // Real: AJAX delete
                location.reload();
            }
        }
        
        function goToChat() {
            window.location.href = 'chat.php';
        }
        
        function goHome() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
