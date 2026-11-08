{{-- $titre, $texte, $ton (ok|warn|bad|vide) --}}
@if(filled($texte))
    <div class="comment-box {{ !empty($ton) ? 'tone-'.$ton : '' }}">
        <div class="comment-title">{{ $titre }}</div>
        <div class="comment-text">{{ $texte }}</div>
    </div>
@endif
