@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')

@section('content')
    <h1>Nouveau mot de passe</h1>
    <p class="sub">Choisissez un mot de passe pour votre compte.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')<div class="error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-login">Réinitialiser le mot de passe</button>
    </form>
@endsection
