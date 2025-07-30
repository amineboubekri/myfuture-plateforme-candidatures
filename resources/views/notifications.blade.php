@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<h2>Notifications</h2>
<div class="card">
    <div class="card-header">Liste des notifications</div>
    <div class="card-body">
        @if(count($notifications))
            <ul>
                @foreach($notifications as $notif)
                    <li>
                        <strong>{{ $notif->title }}</strong> - {{ $notif->message }}
                        <span class="badge bg-{{ $notif->read_at ? 'secondary' : 'info' }}">{{ $notif->read_at ? 'Lue' : 'Non lue' }}</span>
                        @if(!$notif->read_at)
                            <form method="POST" action="/notifications/{{ $notif->id }}/read" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Marquer comme lue</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p>Aucune notification.</p>
        @endif
    </div>
</div>
@endsection 