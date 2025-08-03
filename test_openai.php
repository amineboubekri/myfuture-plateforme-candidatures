<?php

require_once 'vendor/autoload.php';

use OpenAI;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'] ?? null;

if (empty($apiKey)) {
    echo "❌ OpenAI API Key not found in .env file\n";
    exit(1);
}

echo "✅ OpenAI API Key found\n";
echo "Key starts with: " . substr($apiKey, 0, 10) . "...\n\n";

try {
    $client = OpenAI::client($apiKey);
    
    echo "📡 Testing OpenAI API connection...\n";
    
    $result = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, just testing the connection. Please respond with "Connection successful!"']
        ],
        'max_tokens' => 50
    ]);
    
    echo "✅ API Connection successful!\n";
    echo "Response: " . $result->choices[0]->message->content . "\n";
    
} catch (\OpenAI\Exceptions\ErrorException $e) {
    echo "❌ OpenAI API Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
    if (strpos($e->getMessage(), 'quota') !== false) {
        echo "\n💡 This is a quota/billing issue. Please check:\n";
        echo "   1. Go to https://platform.openai.com/account/billing\n";
        echo "   2. Make sure you have a valid payment method\n";
        echo "   3. Check your usage limits\n";
        echo "   4. For new accounts, you might need to add credit first\n";
    } elseif (strpos($e->getMessage(), 'invalid') !== false) {
        echo "\n💡 The API key appears to be invalid. Please check:\n";
        echo "   1. Go to https://platform.openai.com/api-keys\n";
        echo "   2. Generate a new API key\n";
        echo "   3. Update your .env file\n";
    }
    
} catch (\Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
