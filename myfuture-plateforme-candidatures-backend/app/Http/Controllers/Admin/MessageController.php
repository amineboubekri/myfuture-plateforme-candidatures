<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $messages = \App\Models\Message::where('receiver_id', $admin->id)->orderBy('created_at', 'desc')->get();
        $students = \App\Models\User::where('role', 'student')->get();
        return view('admin.messages', [
            'messages' => $messages,
            'students' => $students
        ]);
    }

    public function send(Request $request)
    {
        $admin = $request->user();
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        \App\Models\Message::create([
            'sender_id' => $admin->id,
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'],
            'content' => $data['content'],
        ]);
        // Générer une notification à l'étudiant
        $student = \App\Models\User::find($data['receiver_id']);
        \App\Models\Notification::create([
            'user_id' => $student->id,
            'type' => 'message',
            'title' => 'Nouveau message admin',
            'message' => 'Vous avez reçu un nouveau message de l\'administrateur.',
        ]);
        return redirect()->back()->with('success', 'Message envoyé !');
    }
}
