<?php

namespace Database\Seeders;

use App\Models\ChecklistItem;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Documents d'étude d'exécution (Entreprise)
            ['code' => 'PLN', 'nom' => "Plans d'exécution", 'categorie' => 'etude_execution', 'mode_traitement' => 'validation'],
            ['code' => 'DEX', 'nom' => "Dossiers d'exécution", 'categorie' => 'etude_execution', 'mode_traitement' => 'validation'],
            ['code' => 'NCA', 'nom' => 'Notes de calcul', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation'],
            ['code' => 'NJU', 'nom' => 'Notes justificatives', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation'],
            ['code' => 'AVM', 'nom' => 'Avant-métrés', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation'],
            ['code' => 'NAC', 'nom' => "Nomenclatures d'aciers", 'categorie' => 'etude_execution', 'mode_traitement' => 'validation'],

            // Documents de gestion de projet (Entreprise)
            ['code' => 'GNRL', 'nom' => 'Planning général', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],
            ['code' => 'GFIN', 'nom' => 'Planning financier', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],
            ['code' => 'LPO', 'nom' => 'Liste du personnel et organigramme', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],
            ['code' => 'LMAT', 'nom' => 'Liste du matériel', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],
            ['code' => 'PRG', 'nom' => 'Programme de réalisation', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],
            ['code' => 'ATT', 'nom' => 'Attachements', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],
            ['code' => 'DEC', 'nom' => 'Décomptes', 'categorie' => 'gestion_projet', 'mode_traitement' => 'validation'],
            ['code' => 'GEOT', 'nom' => 'DQE', 'categorie' => 'gestion_projet', 'mode_traitement' => 'simple'],

            // Documents d'assurance qualité (Entreprise)
            ['code' => 'PAQ', 'nom' => "Plan d'Assurance Qualité", 'categorie' => 'assurance_qualite', 'mode_traitement' => 'validation'],
            ['code' => 'PROC', 'nom' => 'Procédures', 'categorie' => 'assurance_qualite', 'mode_traitement' => 'validation'],
            ['code' => 'METH', 'nom' => 'Méthodologies', 'categorie' => 'assurance_qualite', 'mode_traitement' => 'validation'],
            ['code' => 'DAM', 'nom' => "Dossier d'agrément de matériaux", 'categorie' => 'assurance_qualite', 'mode_traitement' => 'validation'],
            ['code' => 'ETC', 'nom' => 'Étude de composition', 'categorie' => 'assurance_qualite', 'mode_traitement' => 'validation'],
            ['code' => 'FNC', 'nom' => 'Fiche de non-conformité', 'categorie' => 'assurance_qualite', 'mode_traitement' => 'simple'],
            ['code' => 'FCS', 'nom' => 'Fiche de contrôle et de suivi', 'categorie' => 'assurance_qualite', 'mode_traitement' => 'simple'],
            ['code' => 'DCGN', 'nom' => 'Rapport', 'categorie' => 'assurance_qualite', 'mode_traitement' => 'simple'],

            // Document environnemental et social (Entreprise)
            ['code' => 'PGES', 'nom' => 'Plan de gestion environnemental et social', 'categorie' => 'environnemental_social', 'mode_traitement' => 'validation'],
            ['code' => 'FCSE', 'nom' => 'Fiche de contrôle et de suivi environnementale', 'categorie' => 'environnemental_social', 'mode_traitement' => 'simple'],
            ['code' => 'HSE', 'nom' => 'Fiche de non-conformité HSE', 'categorie' => 'environnemental_social', 'mode_traitement' => 'simple'],
            ['code' => 'RMEN', 'nom' => 'Rapport mensuel', 'categorie' => 'environnemental_social', 'mode_traitement' => 'simple'],
            ['code' => 'PDEV', 'nom' => 'Plan de déviation', 'categorie' => 'environnemental_social', 'mode_traitement' => 'validation'],
            ['code' => 'SGN', 'nom' => 'Plan de signalisation et sécurité', 'categorie' => 'environnemental_social', 'mode_traitement' => 'validation'],

            // Documents produits par le Bureau de Contrôle
            ['code' => 'AVT', 'nom' => 'Avis technique', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'VLD', 'nom' => 'Validation', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'OBS', 'nom' => 'Observations', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'NCR', 'nom' => 'Non-conformité', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'CTR', 'nom' => 'Rapport de contrôle', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'VISA', 'nom' => 'Visa technique', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'CRV', 'nom' => 'Compte rendu de visite', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'FSV', 'nom' => 'Fiche de suivi', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
            ['code' => 'RCV', 'nom' => 'Rapport de conformité', 'categorie' => 'bc_document', 'mode_traitement' => 'simple'],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(['code' => $type['code']], $type + ['actif' => true]);
        }

        // Checklist de base pour les plans d'exécution (exemple donné dans le cahier des charges)
        $plan = DocumentType::where('code', 'PLN')->first();
        $checklistPlan = [
            'Cartouche présent',
            'Échelle renseignée',
            'Références du projet indiquées',
            'Numérotation conforme',
            'Légendes complètes',
        ];
        foreach ($checklistPlan as $ordre => $libelle) {
            ChecklistItem::firstOrCreate(
                ['document_type_id' => $plan->id, 'libelle' => $libelle],
                ['ordre' => $ordre, 'obligatoire' => true]
            );
        }
    }
}
