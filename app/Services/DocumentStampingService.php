<?php

namespace App\Services;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class DocumentStampingService
{
    private const EXTENSIONS_IMAGE = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /**
     * Validation finale : tampon « Validé » sur chaque page + cachets et signatures posés sur les pages (début, 1/4, milieu, 3/4, fin ; cachets et
     * signatures de tous les chefs ayant visé, dont celui du Bureau de Contrôle).
     * Écrit dans fichier_valide_chemin et scelle (SHA-256) ; ne touche jamais à l'original.
     * Ne fait rien pour les formats non tamponnables (Word, Excel, zip...).
     */
    public function tamponner(DocumentVersion $version): bool
    {
        $chemin = $this->produire($version, true);
        if (!$chemin) {
            return false;
        }

        $version->update([
            'fichier_valide_chemin' => $chemin,
            // Scellement : empreinte du fichier final, invalidée par toute modification a posteriori.
            'fichier_valide_hash' => hash_file('sha256', Storage::disk('public')->path($chemin)),
            'fichier_valide_scelle_le' => now(),
        ]);

        return true;
    }

    /**
     * Avant l'envoi à une autre structure : copie du document avec les cachets et signatures sur les pages
     * (sans tampon « Validé »). Régénérée à chaque nouveau visa depuis l'original.
     */
    public function viser(DocumentVersion $version): bool
    {
        $chemin = $this->produire($version, false);
        if (!$chemin) {
            return false;
        }

        $version->update(['fichier_vise_chemin' => $chemin]);

        return true;
    }

    private function produire(DocumentVersion $version, bool $avecTampon): ?string
    {
        if (!$version->fichier_chemin || !Storage::disk('public')->exists($version->fichier_chemin)) {
            return null;
        }

        $visas = $version->visas()->with(['user', 'structure'])->get();
        if (!$avecTampon && $visas->isEmpty()) {
            return null;
        }

        $extension = strtolower(pathinfo($version->fichier_chemin, PATHINFO_EXTENSION));
        $sourcePath = Storage::disk('public')->path($version->fichier_chemin);

        try {
            if ($extension === 'pdf') {
                return $this->tamponnerPdf($version, $sourcePath, $avecTampon, $visas);
            }
            if (in_array($extension, self::EXTENSIONS_IMAGE, true)) {
                return $this->tamponnerImage($version, $sourcePath, $extension, $avecTampon, $visas);
            }
        } catch (\Throwable $e) {
            Log::error('Échec de la production du document visé/validé : ' . $e->getMessage(), [
                'document_version_id' => $version->id,
            ]);
        }

        return null;
    }

    private function tamponnerPdf(DocumentVersion $version, string $sourcePath, bool $avecTampon, $visas): ?string
    {
        $tamponPng = $avecTampon ? $this->genererTamponTransparent() : null;

        try {
            // Le lecteur PDF gratuit de FPDI refuse les PDF récents (flux de références compressés) :
            // dans ce cas on les ré-écrit au format PDF 1.4 avec Ghostscript, puis on réessaie.
            $converti = null;
            try {
                $pdf = new Fpdi();
                $pageCount = $pdf->setSourceFile($sourcePath);
            } catch (\Throwable $e) {
                $converti = $this->convertirPdfCompatible($sourcePath);
                if (!$converti) {
                    throw $e;
                }
                $pdf = new Fpdi();
                $pageCount = $pdf->setSourceFile($converti);
            }
            // Pas de saut de page automatique : on écrit des légendes dans la marge basse.
            $pdf->SetAutoPageBreak(false);
            [$imgW, $imgH] = $tamponPng ? getimagesize($tamponPng) : [1, 1];

            // Cachets + signatures posés sur les pages : début, 1/4, milieu, 3/4 et fin
            // (toutes les pages si le document en compte moins de 5).
            $cartes = $this->preparerCartesVisas($visas);
            $pagesVisa = $this->pagesVisa($pageCount);

            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $pdf->importPage($page);
                $taille = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($taille['orientation'], [$taille['width'], $taille['height']]);
                $pdf->useTemplate($templateId);

                // Tampon posé sur chaque page (plus seulement la première)
                if ($tamponPng) {
                    $largeur = min(60, $taille['width'] * 0.4);
                    $hauteur = $largeur * $imgH / $imgW;
                    // À droite, à mi-hauteur : les coins sont réservés aux cachets des structures.
                    $x = $taille['width'] - $largeur - 15;
                    $y = ($taille['height'] - $hauteur) / 2;
                    $pdf->Image($tamponPng, max(5, $x), max(5, $y), $largeur, $hauteur, 'PNG');
                }

                if ($cartes && in_array($page, $pagesVisa, true)) {
                    $this->poserVisasPdf($pdf, $cartes, $taille['width'], $taille['height'], $avecTampon);
                }
            }

            $relatif = ($avecTampon ? 'dossiers/valides/' : 'dossiers/vises/') . date('Y/m') . '/' . pathinfo($version->fichier_chemin, PATHINFO_FILENAME) . ($avecTampon ? '-valide' : '-vise') . '.pdf';
            Storage::disk('public')->makeDirectory(dirname($relatif));
            $pdf->Output(Storage::disk('public')->path($relatif), 'F');

            return $relatif;
        } finally {
            if ($tamponPng) {
                @unlink($tamponPng);
            }
            if (!empty($converti)) {
                @unlink($converti);
            }
            foreach ($cartes ?? [] as $carte) {
                @unlink($carte['cachet']);
            }
        }
    }

    /** Ré-écrit un PDF en version 1.4 (lisible par FPDI) via Ghostscript ; null si indisponible ou en échec. */
    private function convertirPdfCompatible(string $source): ?string
    {
        $gs = null;
        foreach (['/usr/bin/gs', '/usr/local/bin/gs', '/opt/homebrew/bin/gs'] as $candidat) {
            if (is_executable($candidat)) {
                $gs = $candidat;
                break;
            }
        }
        if (!$gs || !function_exists('proc_open')) {
            Log::warning('Ghostscript introuvable : impossible d\'inscrire les visas sur ce PDF.');

            return null;
        }

        $sortie = tempnam(sys_get_temp_dir(), 'pdf14_') . '.pdf';
        $commande = [$gs, '-q', '-dNOPAUSE', '-dBATCH', '-dSAFER', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', '-sOutputFile=' . $sortie, $source];
        $process = @proc_open($commande, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            return null;
        }
        stream_get_contents($pipes[1]);
        $erreur = stream_get_contents($pipes[2]);
        $code = proc_close($process);

        if ($code !== 0 || !is_file($sortie) || filesize($sortie) === 0) {
            Log::warning('Conversion Ghostscript du PDF en échec : ' . trim((string) $erreur));
            @unlink($sortie);

            return null;
        }

        return $sortie;
    }

    /** Texte compatible polices FPDF (Windows-1252). */
    private function t(?string $texte): string
    {
        return (string) iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $texte);
    }

    /** Pages portant les visas : début, 25 %, milieu, 75 % et fin ; toutes si moins de 5 pages. */
    private function pagesVisa(int $nombre): array
    {
        if ($nombre <= 5) {
            return range(1, max(1, $nombre));
        }

        return array_values(array_unique([1, (int) ceil($nombre * 0.25), (int) ceil($nombre * 0.5), (int) ceil($nombre * 0.75), $nombre]));
    }

    /**
     * Prépare chaque visa : cachet en PNG au fond transparent (le blanc du JPG ne masque plus le texte
     * du document), chemin absolu de la signature, et deux lignes de légende.
     *
     * @return array<int, array{cachet: string, signature: ?string, l1: string, l2: string}>
     */
    private function preparerCartesVisas($visas): array
    {
        $cartes = [];
        foreach ($visas as $visa) {
            $cachet = $this->cachetTransparent(Storage::disk('public')->path($visa->cachet_chemin));
            if (!$cachet) {
                continue;
            }
            $signature = Storage::disk('public')->path($visa->signature_chemin);
            $cartes[] = [
                'type' => (string) $visa->structure?->type,
                'cachet' => $cachet,
                'signature' => is_file($signature) ? $signature : null,
                'l1' => (string) $visa->user?->full_name,
                'l2' => trim(($visa->type === 'validation' ? 'Validation' : 'Envoi') . ' · ' . ($visa->structure?->nom ?? '') . ' · ' . $visa->signe_le->format('d/m/Y'), ' ·'),
            ];
        }

        return $cartes;
    }

    /** Copie du cachet en PNG où le fond blanc devient transparent. Fichier temporaire à supprimer. */
    private function cachetTransparent(string $chemin): ?string
    {
        $contenu = is_file($chemin) ? @file_get_contents($chemin) : false;
        $source = $contenu ? @imagecreatefromstring($contenu) : false;
        if (!$source) {
            return null;
        }

        $w = imagesx($source);
        $h = imagesy($source);
        $cible = min(1.0, 360 / max($w, $h));
        $nw = max(1, (int) round($w * $cible));
        $nh = max(1, (int) round($h * $cible));

        $png = imagecreatetruecolor($nw, $nh);
        imagealphablending($png, false);
        imagesavealpha($png, true);
        imagefill($png, 0, 0, imagecolorallocatealpha($png, 255, 255, 255, 127));
        imagealphablending($png, true);
        imagecopyresampled($png, $source, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($source);
        imagealphablending($png, false);

        for ($y = 0; $y < $nh; $y++) {
            for ($x = 0; $x < $nw; $x++) {
                $rgba = imagecolorat($png, $x, $y);
                $alphaExistant = ($rgba >> 24) & 0x7F;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $clair = min($r, $g, $b);
                if ($clair >= 245) {
                    $alpha = 127; // fond blanc : transparent
                } elseif ($clair >= 205) {
                    $alpha = max($alphaExistant, (int) round(127 * ($clair - 205) / 40)); // bord adouci
                } else {
                    continue;
                }
                imagesetpixel($png, $x, $y, imagecolorallocatealpha($png, $r, $g, $b, $alpha));
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'cachet_') . '.png';
        imagepng($png, $tmp);
        imagedestroy($png);

        return $tmp;
    }

    /**
     * Emplacement du cachet de chaque structure : BC en bas à droite, BE en bas à gauche, MOE en bas au centre,
     * MO en haut à droite. Plusieurs visas d'une même structure se superposent vers l'intérieur de la page.
     *
     * @return array{0: float, 1: float} coin supérieur gauche du cachet
     */
    private function emplacement(string $type, int $rang, float $w, float $h, float $bw, float $bh, float $marge, float $gap): array
    {
        $decalage = $rang * ($bh + $gap);

        return match ($type) {
            'bureau_controle' => [$w - $marge - $bw, $h - $marge - $bh - $decalage],
            'maitre_oeuvre' => [($w - $bw) / 2, $h - $marge - $bh - $decalage],
            'maitre_ouvrage' => [$w - $marge - $bw, $marge + $decalage],
            // Bureau d'Études et visas sans structure connue : en bas à gauche.
            default => [$marge, $h - $marge - $bh - $decalage],
        };
    }

    /** Pose les visas aux emplacements propres à chaque structure (voir emplacement()). */
    private function poserVisasPdf(Fpdi $pdf, array $cartes, float $w, float $h, bool $avecTampon): void
    {
        $bw = 46;
        $bh = 29;
        $gap = 3;
        $marge = 8;
        $cs = 20; // côté du cachet
        $rangs = [];

        foreach ($cartes as $carte) {
            $rang = $rangs[$carte['type']] = ($rangs[$carte['type']] ?? -1) + 1;
            [$x, $y] = $this->emplacement($carte['type'], $rang, $w, $h, $bw, $bh, $marge, $gap);

            $pdf->Image($carte['cachet'], $x, $y, $cs, $cs, 'PNG');

            if ($carte['signature'] && ($dim = @getimagesize($carte['signature']))) {
                $hs = min(15, 26 * $dim[1] / $dim[0]);
                $ws = $hs * $dim[0] / $dim[1];
                $pdf->Image($carte['signature'], $x + $cs * 0.6, $y + ($cs - $hs) / 2 + 2, $ws, $hs, 'PNG');
            }

            $pdf->SetTextColor(15, 23, 42);
            $pdf->SetFont('Helvetica', 'B', 6.5);
            $pdf->SetXY($x, $y + $cs + 0.6);
            $pdf->Cell($bw, 3, $this->t($this->tenir($pdf, $carte['l1'], $bw)), 0, 0);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->SetFont('Helvetica', '', 5.5);
            $pdf->SetXY($x, $y + $cs + 3.6);
            $pdf->Cell($bw, 3, $this->t($this->tenir($pdf, $carte['l2'], $bw)), 0, 0);
        }
    }

    /** Raccourcit un texte jusqu'à ce qu'il tienne dans la largeur donnée (police courante). */
    private function tenir(Fpdi $pdf, string $texte, float $largeur): string
    {
        $encode = fn (string $t) => $this->t($t);
        while ($texte !== '' && $pdf->GetStringWidth($encode($texte)) > $largeur) {
            $texte = rtrim(mb_substr($texte, 0, -2)) . '…';
            if (mb_strlen($texte) <= 4) {
                break;
            }
        }

        return $texte;
    }

    private function tamponnerImage(DocumentVersion $version, string $sourcePath, string $extension, bool $avecTampon, $visas): ?string
    {
        $document = match ($extension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($sourcePath),
            'png' => imagecreatefrompng($sourcePath),
            'gif' => imagecreatefromgif($sourcePath),
            'webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : false,
            'bmp' => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($sourcePath) : false,
            default => false,
        };

        if (!$document) {
            return null;
        }

        imagealphablending($document, true);
        imagesavealpha($document, true);

        $tampon = null;
        if ($avecTampon) {
            $tamponPng = $this->genererTamponTransparent();
            $tampon = imagecreatefrompng($tamponPng);
            @unlink($tamponPng);

            if (!$tampon) {
                imagedestroy($document);

                return null;
            }
        }

        $docW = imagesx($document);
        $docH = imagesy($document);
        if ($tampon) {
        $tamponW = imagesx($tampon);
        $tamponH = imagesy($tampon);

        // Redimensionne le tampon si le document est plus petit que lui
        $largeurCible = min($tamponW, (int) ($docW * 0.45));
        if ($largeurCible < $tamponW) {
            $hauteurCible = (int) ($largeurCible * $tamponH / $tamponW);
            $redimensionne = imagecreatetruecolor($largeurCible, $hauteurCible);
            imagealphablending($redimensionne, false);
            imagesavealpha($redimensionne, true);
            imagecopyresampled($redimensionne, $tampon, 0, 0, 0, 0, $largeurCible, $hauteurCible, $tamponW, $tamponH);
            imagedestroy($tampon);
            $tampon = $redimensionne;
            $tamponW = $largeurCible;
            $tamponH = $hauteurCible;
        }

        $destX = max(10, $docW - $tamponW - 25);
        $destY = max(10, (int) (($docH - $tamponH) / 2));
        imagecopy($document, $tampon, $destX, $destY, 0, 0, $tamponW, $tamponH);
        imagedestroy($tampon);
        }

        if ($visas->isNotEmpty()) {
            $this->incrusterVisasImage($document, $visas);
        }

        $relatif = ($avecTampon ? 'dossiers/valides/' : 'dossiers/vises/') . date('Y/m') . '/' . pathinfo($version->fichier_chemin, PATHINFO_FILENAME) . ($avecTampon ? '-valide.' : '-vise.') . $extension;
        Storage::disk('public')->makeDirectory(dirname($relatif));
        $chemin = Storage::disk('public')->path($relatif);

        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($document, $chemin, 92),
            'png' => imagepng($document, $chemin),
            'gif' => imagegif($document, $chemin),
            'webp' => imagewebp($document, $chemin),
            'bmp' => imagebmp($document, $chemin),
            default => imagejpeg($document, $chemin, 92),
        };

        imagedestroy($document);

        return $relatif;
    }

    /** Incruste les visas en bas à gauche de l'image (cachet, signature, légende). */
    private function incrusterVisasImage(\GdImage $document, $visas): void
    {
        $police = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $cartes = $this->preparerCartesVisas($visas);
        $docW = imagesx($document);
        $docH = imagesy($document);

        $bw = max(150, (int) ($docW * 0.24));
        $cs = (int) ($bw * 0.44);
        $taille = max(8, $bw * 0.062);
        $bh = $cs + (int) ($taille * 3.4);
        $marge = (int) max(10, $docW * 0.015);
        imagealphablending($document, true);
        $rangs = [];

        foreach ($cartes as $carte) {
            $rang = $rangs[$carte['type']] = ($rangs[$carte['type']] ?? -1) + 1;
            [$x, $y] = array_map('intval', $this->emplacement($carte['type'], $rang, $docW, $docH, $bw, $bh, $marge, $marge));

            $cachet = @imagecreatefrompng($carte['cachet']);
            if ($cachet) {
                imagecopyresampled($document, $cachet, $x, $y, 0, 0, $cs, $cs, imagesx($cachet), imagesy($cachet));
                imagedestroy($cachet);
            }
            if ($carte['signature'] && ($sig = @imagecreatefrompng($carte['signature']))) {
                $ws = (int) ($bw * 0.52);
                $hs = (int) min($cs * 0.8, $ws * imagesy($sig) / imagesx($sig));
                $ws = (int) ($hs * imagesx($sig) / imagesy($sig));
                imagecopyresampled($document, $sig, $x + (int) ($cs * 0.6), $y + (int) (($cs - $hs) / 2), 0, 0, $ws, $hs, imagesx($sig), imagesy($sig));
                imagedestroy($sig);
            }

            $encre = imagecolorallocate($document, 15, 23, 42);
            $gris = imagecolorallocate($document, 71, 85, 105);
            imagettftext($document, $taille, 0, $x, $y + $cs + (int) ($taille * 1.5), $encre, $police, $carte['l1']);
            imagettftext($document, $taille * 0.82, 0, $x, $y + $cs + (int) ($taille * 2.9), $gris, $police, $carte['l2']);
            @unlink($carte['cachet']);
        }
    }

    /**
     * Construit, à partir de public/images/validate.jpg, un PNG semi-transparent
     * légèrement incliné (canal alpha réel, coins transparents après rotation),
     * réutilisable aussi bien pour l'incrustation sur image que sur PDF (FPDF gère
     * nativement la transparence PNG).
     */
    private function genererTamponTransparent(): string
    {
        $sourcePath = public_path('images/validate.jpg');
        $source = @imagecreatefromjpeg($sourcePath);

        if (!$source) {
            throw new \RuntimeException('Image de tampon introuvable ou invalide : ' . $sourcePath);
        }

        // Réduit à une taille de travail raisonnable avant les traitements pixel par pixel
        $maxLargeur = 400;
        $largeurSource = imagesx($source);
        $hauteurSource = imagesy($source);

        if ($largeurSource > $maxLargeur) {
            $hauteurCible = (int) ($maxLargeur * $hauteurSource / $largeurSource);
            $redimensionne = imagecreatetruecolor($maxLargeur, $hauteurCible);
            imagecopyresampled($redimensionne, $source, 0, 0, 0, 0, $maxLargeur, $hauteurCible, $largeurSource, $hauteurSource);
            imagedestroy($source);
            $source = $redimensionne;
        }

        $w = imagesx($source);
        $h = imagesy($source);

        // Canevas avec canal alpha : copie opaque du tampon
        $canevas = imagecreatetruecolor($w, $h);
        imagealphablending($canevas, false);
        imagesavealpha($canevas, true);
        imagefill($canevas, 0, 0, imagecolorallocatealpha($canevas, 0, 0, 0, 127));
        imagealphablending($canevas, true);
        imagecopy($canevas, $source, 0, 0, 0, 0, $w, $h);
        imagedestroy($source);

        // Légère rotation, coins nouvellement créés transparents
        imagealphablending($canevas, false);
        imagesavealpha($canevas, true);
        $incline = imagerotate($canevas, 12, imagecolorallocatealpha($canevas, 0, 0, 0, 127));
        imagedestroy($canevas);
        imagealphablending($incline, false);
        imagesavealpha($incline, true);

        // Rend le tampon semi-transparent (sans toucher aux coins déjà transparents)
        $rw = imagesx($incline);
        $rh = imagesy($incline);
        for ($y = 0; $y < $rh; $y++) {
            for ($x = 0; $x < $rw; $x++) {
                $rgba = imagecolorat($incline, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha < 100) {
                    $couleur = imagecolorsforindex($incline, $rgba);
                    $nouvelAlpha = min(85, $alpha + 55);
                    imagesetpixel($incline, $x, $y, imagecolorallocatealpha(
                        $incline,
                        $couleur['red'],
                        $couleur['green'],
                        $couleur['blue'],
                        $nouvelAlpha
                    ));
                }
            }
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'stamp_') . '.png';
        imagepng($incline, $tmpPath);
        imagedestroy($incline);

        return $tmpPath;
    }
}
