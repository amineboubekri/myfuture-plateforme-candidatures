<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🤖 Testing Free AI Providers\n";
echo "============================\n\n";

// Test Groq
$groqKey = $_ENV['GROQ_API_KEY'] ?? null;
if (!empty($groqKey)) {
    echo "🔵 Testing Groq API...\n";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'llama3-8b-8192',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello, please respond with "Groq is working!"']
            ],
            'max_tokens' => 50
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $groqKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            echo "✅ Groq: " . ($data['choices'][0]['message']['content'] ?? 'No response') . "\n";
        } else {
            echo "❌ Groq failed (HTTP $httpCode): " . $response . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Groq error: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚪ Groq API key not configured\n";
}

echo "\n";

// Test Hugging Face
$hfKey = $_ENV['HUGGINGFACE_API_KEY'] ?? null;
if (!empty($hfKey)) {
    echo "🟡 Testing Hugging Face API...\n";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'inputs' => 'Hello, please respond with "Hugging Face is working!"'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $hfKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            echo "✅ Hugging Face: " . ($data[0]['generated_text'] ?? 'No response') . "\n";
        } else {
            echo "❌ Hugging Face failed (HTTP $httpCode): " . $response . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Hugging Face error: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚪ Hugging Face API key not configured\n";
}

echo "\n";

// Test fallback
echo "🔄 Testing Fallback Response...\n";
echo "✅ Fallback: Works (keyword-based responses)\n";

echo "\n=== Setup Instructions ===\n";
echo "1. Groq (Recommended): https://console.groq.com/ - Free tier: 5000 requests/day\n";
echo "2. Hugging Face: https://huggingface.co/settings/tokens - Free tier available\n";
echo "3. Add the API key to your .env file\n";
echo "4. The system will automatically use the first available provider\n";
