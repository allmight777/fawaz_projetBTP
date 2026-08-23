{{-- resources/views/emails/document-assigned.blade.php --}}
<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #047857;">Un document vous a été assigné</h2>
        <p>Bonjour,</p>
        <p>Le Chef de mission <strong>{{ $affectePar->full_name }}</strong> vous a assigné un nouveau document pour contrôle et observation :</p>
        <div style="background: #f0fdf4; border-radius: 10px; padding: 16px; margin: 16px 0;">
            <p><strong>Titre :</strong> {{ $dossier->titre }}</p>
            <p><strong>Fichier :</strong> {{ $version->nom_affiche }}</p>
            <p><strong>Identifiant :</strong> {{ $dossier->identifiant ?? '#'.$dossier->id }}</p>
        </div>

        @if(isset($affectation) && $affectation && $affectation->date_limite)
        <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 16px; margin: 16px 0;">
            <p style="margin: 0; color: #92400e;">
                <strong>⏱ Délai de traitement :</strong> vous devez rendre votre analyse avant le
                <strong>{{ $affectation->date_limite->translatedFormat('d/m/Y à H:i') }}</strong>.
            </p>
        </div>
        @endif

        <p>Connectez-vous à votre espace pour consulter et traiter ce document.</p>
    </div>
</body>
</html>
