<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenAI\Client as OpenAIClient;

class ChatbotController extends Controller
{
    protected $openAIClient;

    public function __construct(OpenAIClient $openAIClient)
    {
        $this->openAIClient = $openAIClient;
    }

    public function index()
    {
        return view('chatbot.index');
    }

    public function sendMessage(Request $request)
    {
        $client = OpenAI::client(env('OPENAI_API_KEY'));
    
        try {
            $result = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $request->input('message')],
                ],
            ]);
    
            return response()->json(['response' => $result->choices[0]->message->content]);
        } catch (\Throwable $e) {
            \Log::error('OpenAI Error: ' . $e->getMessage());
            return response()->json(['response' => 'Erreur interne du serveur.'], 500);
        }
    }    
}

