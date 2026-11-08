@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')
    <h1>Mot de passe oublié ?</h1>
    <p class="sub">Indiquez votre adresse e-mail : nous vous enverrons un lien pour en choisir un nouveau.</p>

    @if (session('status'))
        <div class="session-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="exemple@entreprise-btp.fr" required autofocus>
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-login">Envoyer le lien de réinitialisation</button>

        <div class="auth-foot">
            <a href="{{ route('login') }}">← Retour à la connexion</a>
        </div>
    </form>
@endsection
