<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Copilot Clone - Your AI Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Amazing CSS: Modern, Copilot-inspired design with gradients, shadows, and smooth animations */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 90%;
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            color: #1a73e8;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        p {
            font-size: 1.2em;
            margin-bottom: 30px;
            color: #666;
            line-height: 1.6;
        }
        .features {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 40px;
        }
        .feature {
            background: linear-gradient(90deg, #1a73e8, #34a853);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .feature:hover {
            transform: translateY(-2px);
        }
        .btn {
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26, 115, 232, 0.3);
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.4);
        }
        .examples {
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .example-prompt {
            color: #1a73e8;
            margin: 5px 0;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .container { padding: 20px; }
            h1 { font-size: 2em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Copilot Clone</h1>
        <p>Your intelligent AI assistant for text generation, task help, and smart suggestions.</p>
        
        <div class="features">
            <div class="feature">✨ Generate creative text & stories</div>
            <div class="feature">📧 Draft emails & summaries</div>
            <div class="feature">💻 Code assistance & debugging</div>
            <div class="feature">📚 Learn & explore topics</div>
        </div>
        
        <div class="examples">
            <h3 style="color: #333; margin-bottom: 10px;">Try these prompts:</h3>
            <div class="example-prompt">"Write a professional email to my boss about a project update."</div>
            <div class="example-prompt">"Explain quantum computing in simple terms."</div>
            <div class="example-prompt">"Generate a Python script for a to-do list app."</div>
        </div>
        
        <a href="chat.php" class="btn" onclick="redirectToChat()">Start Chatting</a>
        <br><br>
        <a href="history.php" class="btn" style="background: linear-gradient(135deg, #34a853, #7cb342);" onclick="redirectToHistory()">View History</a>
    </div>

    <script>
        // JS for redirection (no PHP redirects)
        function redirectToChat() {
            window.location.href = 'chat.php';
        }
        function redirectToHistory() {
            window.location.href = 'history.php';
        }
    </script>
</body>
</html>
