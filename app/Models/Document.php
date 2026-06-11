<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'nom_fichier',
        'chemin_fichier',
        'url_fichier',
        'type_fichier',
        'taille',
    ];

    public function demandes()
    {
        return $this->hasMany(Demande::class);
    }
}
