<?php

namespace App\Http\Controllers;

use App\Services\VisaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CachetController extends Controller
{
    public function edit()
    {
        abort_unless(Auth::user()->peutViser(), 403, 'Réservé aux chefs de structure.');

        return view('cachets.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request, VisaService $visas)
    {
        $user = Auth::user();
        abort_unless($user->peutViser(), 403, 'Réservé aux chefs de structure.');

        $request->validate([
            'cachet' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], VisaService::messages());

        $visas->memoriserCachet($user, $request->file('cachet'));

        return redirect()->route('cachets.edit')->with('success', 'Cachet enregistré.');
    }

    public function destroy()
    {
        $user = Auth::user();
        abort_unless($user->peutViser(), 403);

        if ($user->cachet_chemin) {
            Storage::disk('public')->delete($user->cachet_chemin);
            $user->forceFill(['cachet_chemin' => null])->save();
        }

        return redirect()->route('cachets.edit')->with('success', 'Cachet supprimé. Il vous sera redemandé au prochain envoi.');
    }
}
