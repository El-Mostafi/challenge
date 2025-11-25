<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExportExpenses;
use App\Models\Export;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    // Lancer un export
    public function exportExpenses(Request $request)
    {
        try {
            // Création de l'enregistrement Export
            $export = Export::create([
                'user_id' => auth()->id(),
                'status' => 'PENDING',
                'meta' => json_encode([
                    'totalRecords' => 0,
                    'filters' => [
                        'status' => $request->input('status'),
                        'period' => $request->input('period'),
                    ],
                ]),
            ]);
            
            // Dispatch du Job (sync pour génération immédiate)
            ExportExpenses::dispatch($export->id, [
                'status' => $request->input('status'),
                'period' => $request->input('period'),
            ]);
            
            // Si JSON, retourner info export
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Export lancé avec succès',
                    'export_id' => $export->id,
                ], 202);
            }
            
            // Attendre que le job soit traité (sync) puis télécharger directement
            $export->refresh();
            
            if ($export->status === 'READY' && $export->file_path) {
                $filePath = storage_path('app/' . $export->file_path);
                
                if (file_exists($filePath)) {
                    return response()->download($filePath, basename($export->file_path));
                }
            }
            
            return back()->with('error', 'Erreur lors de la génération de l\'export.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Erreur lors de l\'export.'], 500);
            }
            return back()->with('error', 'Erreur lors de l\'export.');
        }
    }

    // Télécharger un export
    public function download(Export $export)
    {
        try {
            // Vérifier que l'export appartient à l'utilisateur ou que c'est un manager
            if ($export->user_id !== auth()->id() && !auth()->user()->isManager()) {
                abort(403, 'Non autorisé');
            }

            if ($export->status !== 'READY' || !$export->file_path) {
                return back()->with('error', 'Export non disponible.');
            }

            $filePath = storage_path('app/' . $export->file_path);

            if (!file_exists($filePath)) {
                return back()->with('error', 'Fichier introuvable.');
            }

            return response()->download($filePath, basename($export->file_path));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du téléchargement.');
        }
    }
}
