<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Vous êtes invité(e) à un événement</h2>
        <p>Bonjour {{ $destinataire->prenom ?? $destinataire->nom }},</p>
        <p><strong>{{ $event->createur->full_name }}</strong> vous a inscrit(e) à l'événement suivant :</p>

        <div style="background: #eff6ff; border-radius: 10px; padding: 16px; margin: 16px 0;">
            <p style="margin:0 0 8px;"><strong>{{ $event->titre }}</strong></p>
            <p style="margin:4px 0;">
                <i>Date :</i> {{ $event->date_debut->translatedFormat('d/m/Y à H:i') }}
                @if($event->date_fin)
                    &rarr; {{ $event->date_fin->translatedFormat('d/m/Y à H:i') }}
                @endif
            </p>
            @if($event->lieu)
                <p style="margin:4px 0;"><i>Lieu :</i> {{ $event->lieu }}</p>
            @endif
            @if($event->description)
                <p style="margin:8px 0 0;">{{ $event->description }}</p>
            @endif
        </div>

        @if($autresParticipants->count() > 0)
        <div style="margin: 16px 0;">
            <p style="margin-bottom:6px;"><strong>Autres personnes invitées :</strong></p>
            <ul style="margin:0; padding-left:20px;">
                @foreach($autresParticipants as $participant)
                    <li>{{ $participant->full_name }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <p>Connectez-vous à votre espace pour confirmer votre présence.</p>
    </div>
</body>
</html>
