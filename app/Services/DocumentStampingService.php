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
     * Incruste le tampon "Validé" (public/images/validate.jpg) sur le document et
     * enregistre le résultat dans fichier_valide_chemin, sans jamais toucher au
     * fichier original. Ne fait rien (silencieusement) pour les formats qui ne
     * peuvent pas être tamponnés visuellement (Word, Excel, zip...).
     */
    public function tamponner(DocumentVersion $version): bool
    {
        if (!$version->fichier_chemin || !Storage::disk('public')->exists($version->fichier_chemin)) {
            return false;
        }

        $extension = strtolower(pathinfo($version->fichier_chemin, PATHINFO_EXTENSION));
        $sourcePath = Storage::disk('public')->path($version->fichier_chemin);

        try {
            if ($extension === 'pdf') {
                $cheminRelatif = $this->tamponnerPdf($version, $sourcePath);
            } elseif (in_array($extension, self::EXTENSIONS_IMAGE, true)) {
                $cheminRelatif = $this->tamponnerImage($version, $sourcePath, $extension);
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('Échec du tamponnage du document validé : ' . $e->getMessage(), [
                'document_version_id' => $version->id,
            ]);

            return false;
        }

        if (!$cheminRelatif) {
            return false;
        }

        $version->update(['fichier_valide_chemin' => $cheminRelatif]);

        return true;
    }

    private function tamponnerPdf(DocumentVersion $version, string $sourcePath): ?string
    {
        $tamponPng = $this->genererTamponTransparent();

        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($sourcePath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $pdf->importPage($page);
                $taille = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($taille['orientation'], [$taille['width'], $taille['height']]);
                $pdf->useTemplate($templateId);

                if ($page === 1) {
                    [$imgW, $imgH] = getimagesize($tamponPng);
                    $largeur = min(60, $taille['width'] * 0.4);
                    $hauteur = $largeur * $imgH / $imgW;
                    $x = $taille['width'] - $largeur - 15;
                    $y = $taille['height'] - $hauteur - 15;
                    $pdf->Image($tamponPng, max(5, $x), max(5, $y), $largeur, $hauteur, 'PNG');
                }
            }

            $relatif = 'dossiers/valides/' . date('Y/m') . '/' . pathinfo($version->fichier_chemin, PATHINFO_FILENAME) . '-valide.pdf';
            Storage::disk('public')->makeDirectory(dirname($relatif));
            $pdf->Output(Storage::disk('public')->path($relatif), 'F');

            return $relatif;
        } finally {
            @unlink($tamponPng);
        }
    }

    private function tamponnerImage(DocumentVersion $version, string $sourcePath, string $extension): ?string
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

        $tamponPng = $this->genererTamponTransparent();
        $tampon = imagecreatefrompng($tamponPng);
        @unlink($tamponPng);

        if (!$tampon) {
            imagedestroy($document);

            return null;
        }

        $docW = imagesx($document);
        $docH = imagesy($document);
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
        $destY = max(10, $docH - $tamponH - 25);
        imagecopy($document, $tampon, $destX, $destY, 0, 0, $tamponW, $tamponH);
        imagedestroy($tampon);

        $relatif = 'dossiers/valides/' . date('Y/m') . '/' . pathinfo($version->fichier_chemin, PATHINFO_FILENAME) . '-valide.' . $extension;
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
