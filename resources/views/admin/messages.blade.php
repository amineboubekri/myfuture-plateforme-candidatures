@extends('layouts.app')
@section('title', 'Messagerie Admin')
@section('content')
<h2>Messagerie</h2>
<div class="card mb-3">
    <div class="card-header">Envoyer un message à un étudiant</div>
    <div class="card-body">
        <form method="POST" action="/admin/messages/send">
            @csrf
            <div class="mb-3">
                <label for="receiver_id" class="form-label">Étudiant</label>
                <select name="receiver_id" id="receiver_id" class="form-select" required>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Sujet</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Message</label>
                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">Messages reçus</div>
    <div class="card-body">
        @if(count($messages))
            <ul>
                @foreach($messages as $msg)
                    <li><strong>{{ $msg->subject }}</strong> - {{ $msg->content }} <span class="badge bg-{{ $msg->read_at ? 'secondary' : 'info' }}">{{ $msg->read_at ? 'Lu' : 'Non lu' }}</span> <span class="text-muted">(de {{ $msg->sender->name ?? 'N/A' }})</span></li>
                @endforeach
            </ul>
        @else
            <p>Aucun message.</p>
        @endif
    </div>
</div>
@endsection 