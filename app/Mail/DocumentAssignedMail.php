<?php

namespace App\Mail;

use App\Models\Dossier;
use App\Models\DocumentAssignment;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $version;
    public $dossier;
    public $affectePar;
    public $affectation;

    public function __construct(DocumentVersion $version, Dossier $dossier, User $affectePar, ?DocumentAssignment $affectation = null)
    {
        $this->version = $version;
        $this->dossier = $dossier;
        $this->affectePar = $affectePar;
        $this->affectation = $affectation;
    }

    public function build()
    {
        return $this->subject('Un document vous a été assigné pour contrôle')
            ->view('emails.document-assigned')
            ->with([
                'dossier' => $this->dossier,
                'version' => $this->version,
                'affectePar' => $this->affectePar,
                'affectation' => $this->affectation,
            ]);
    }
}
