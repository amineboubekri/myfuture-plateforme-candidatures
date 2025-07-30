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
        $message = $request->input('message');
        $response = $this->openAIClient->sendMessage($message);

        return response()->json(['response' => $response]);
    }
}

