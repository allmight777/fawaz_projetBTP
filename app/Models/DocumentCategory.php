<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $fillable = ['cle', 'nom', 'ordre'];

    public function documentTypes()
    {
        return $this->hasMany(DocumentType::class, 'categorie', 'cle');
    }
}
