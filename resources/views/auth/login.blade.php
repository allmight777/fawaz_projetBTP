@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    <h1>Bienvenue</h1>
    <p class="sub">Espace de gestion Bâtiment &amp; Travaux Publics</p>

    @if (session('status'))
        <div class="session-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="exemple@entreprise-btp.fr" required autofocus>
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" placeholder="Votre mot de passe" required>
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="options">
            <label><input type="checkbox" name="remember"> Se souvenir de moi</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="btn-login">Se connecter</button>

        <div class="auth-foot">
            <a href="{{ url('/') }}">← Retour à l'accueil</a>
            @if (Route::has('register'))
                <span>Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a></span>
            @endif
        </div>
    </form>
@endsection
