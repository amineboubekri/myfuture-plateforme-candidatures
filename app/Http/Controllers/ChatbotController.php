<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use OpenAI;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot.index');
    }

    public function sendMessage(Request $request)
    {
        // Validate the request
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = $request->input('message');
        $systemPrompt = 'Tu es un assistant IA pour une plateforme de candidatures étudiantes appelée MyFuture. Tu aides les étudiants avec leurs questions sur les candidatures, les formations, et les procédures. Réponds de manière utile et professionnelle en français.';

        // Try different AI providers in order of preference
        $providers = [
            'groq' => 'tryGroq',
            'huggingface' => 'tryHuggingFace',
            'openai' => 'tryOpenAI',
            'fallback' => 'fallbackResponse'
        ];

        foreach ($providers as $provider => $method) {
            try {
                $response = $this->$method($message, $systemPrompt);
                if ($response) {
                    return response()->json([
                        'success' => true,
                        'response' => $response,
                        'provider' => $provider
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Provider {$provider} failed: " . $e->getMessage());
                continue;
            }
        }

        return response()->json([
            'error' => true,
            'message' => 'Tous les services IA sont temporairement indisponibles. Veuillez réessayer plus tard.'
        ], 500);
    }

    private function tryGroq($message, $systemPrompt)
    {
        $apiKey = env('GROQ_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama3-8b-8192', // Free model
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? false;
        }

        return false;
    }

    private function tryHuggingFace($message, $systemPrompt)
    {
        $apiKey = env('HUGGINGFACE_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        $fullPrompt = $systemPrompt . "\n\nUtilisateur: " . $message . "\n\nAssistant:";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(30)->post('https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium', [
            'inputs' => $fullPrompt,
            'parameters' => [
                'max_length' => 500,
                'temperature' => 0.7,
                'return_full_text' => false
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data[0]['generated_text'])) {
                return trim($data[0]['generated_text']);
            }
        }

        return false;
    }

    private function tryOpenAI($message, $systemPrompt)
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        try {
            $client = OpenAI::client($apiKey);
            
            $result = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ]);
    
            return $result->choices[0]->message->content;
            
        } catch (\Exception $e) {
            Log::error('OpenAI Error: ' . $e->getMessage());
            return false;
        }
    }

    private function fallbackResponse($message, $systemPrompt)
    {
        // Simple keyword-based responses as fallback
        $message = strtolower($message);
        
        if (strpos($message, 'candidature') !== false || strpos($message, 'application') !== false) {
            return "Pour votre candidature, assurez-vous de préparer tous les documents requis : CV, lettre de motivation, relevés de notes, et pièces d'identité. Vous pouvez suivre l'état de votre candidature dans votre tableau de bord.";
        }
        
        if (strpos($message, 'document') !== false) {
            return "Concernant les documents, vous devez télécharger vos fichiers au format PDF dans la section Documents. Assurez-vous que tous vos documents sont lisibles et à jour.";
        }
        
        if (strpos($message, 'formation') !== false || strpos($message, 'étude') !== false) {
            return "Pour les informations sur les formations, consultez la section dédiée dans votre espace étudiant. Chaque formation a ses propres critères d'admission et prérequis.";
        }
        
        if (strpos($message, 'aide') !== false || strpos($message, 'help') !== false) {
            return "Je suis là pour vous aider ! Vous pouvez me poser des questions sur les candidatures, les documents requis, les formations disponibles, ou les procédures à suivre.";
        }
        
        return "Merci pour votre message. Pour obtenir une aide personnalisée, n'hésitez pas à contacter notre équipe support ou à consulter la documentation disponible dans votre espace étudiant.";
    }
}

