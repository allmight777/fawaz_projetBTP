@extends('layouts.auth')

@section('title', 'Confirmation du mot de passe')

@section('content')
    <h1>Zone sécurisée</h1>
    <p class="sub">Veuillez confirmer votre mot de passe avant de continuer.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" autofocus>
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-login">Confirmer</button>
    </form>
@endsection
