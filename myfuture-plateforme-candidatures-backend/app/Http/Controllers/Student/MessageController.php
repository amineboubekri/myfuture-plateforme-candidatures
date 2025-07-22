<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $messages = \App\Models\Message::where('receiver_id', $user->id)->orderBy('created_at', 'desc')->get();
        return view('student.messages', [
            'messages' => $messages
        ]);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        $admin = \App\Models\User::where('role', 'admin')->first();
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        \App\Models\Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $admin->id,
            'application_id' => $user->applications()->latest()->first()->id ?? null,
            'subject' => $data['subject'],
            'content' => $data['content'],
        ]);
        // Générer une notification à l'admin
        \App\Models\Notification::create([
            'user_id' => $admin->id,
            'type' => 'message',
            'title' => 'Nouveau message étudiant',
            'message' => 'Vous avez reçu un nouveau message de ' . $user->name . '.',
        ]);
        return redirect()->back()->with('success', 'Message envoyé !');
    }
}
