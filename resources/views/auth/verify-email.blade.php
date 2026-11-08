@extends('layouts.auth')

@section('title', 'Vérification de l\'e-mail')

@section('content')
    <h1>Vérifiez votre e-mail</h1>
    <p class="sub">Merci pour votre inscription ! Cliquez sur le lien que nous venons de vous envoyer par e-mail. Si vous ne l'avez pas reçu, nous pouvons vous en renvoyer un.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="session-status">Un nouveau lien de vérification vient d'être envoyé à l'adresse indiquée lors de l'inscription.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn-login">Renvoyer l'e-mail de vérification</button>
    </form>

    <div class="auth-foot">
        <span></span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:0;cursor:pointer;color:var(--accent-d);font-weight:800;font-size: var(--fs-m)">Se déconnecter</button>
        </form>
    </div>
@endsection
