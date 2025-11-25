<?php

namespace App\Jobs;

use App\Models\Export;
use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportExpenses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exportId;
    protected $filters;

    public function __construct($exportId, $filters)
    {
        $this->exportId = $exportId;
        $this->filters = $filters;
    }

    public function handle(): void
    {
        try {
            $export = Export::find($this->exportId);
            
            // Récupération des dépenses selon les filtres
            $query = Expense::with('user');
            
            if (filled($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }
            
            if (filled($this->filters['period'])) {
                [$year, $month] = explode('-', $this->filters['period']);
                $query->whereYear('spent_at', $year)
                      ->whereMonth('spent_at', $month);
            }
            
            $expenses = $query->get();
            
            // Génération du fichier CSV
            $filename = 'expenses_' . now()->format('Y-m-d_His') . '.csv';
            $path = 'exports/' . $filename;
            
            // Créer le répertoire si nécessaire
            if (!file_exists(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0755, true);
            }
            
            $csv = fopen(storage_path('app/' . $path), 'w');
            
            // En-têtes
            fputcsv($csv, ['ID', 'Employee', 'Title', 'Amount', 'Currency', 'Category', 'Date', 'Status']);
            
            // Données
            foreach ($expenses as $expense) {
                fputcsv($csv, [
                    $expense->id,
                    $expense->user->name,
                    $expense->title,
                    $expense->amount,
                    $expense->currency,
                    $expense->category,
                    $expense->spent_at,
                    $expense->status,
                ]);
            }
            
            fclose($csv);
            
            // Mise à jour de l'export
            $export->update([
                'status' => 'READY',
                'file_path' => $path,
                'meta' => json_encode([
                    'total_records' => $expenses->count(),
                    'filters' => [
                        'status' => $this->filters['status'] ?? null,
                        'period' => $this->filters['period'] ?? null,
                    ],
                ]),
            ]);
            
        } catch (\Exception $e) {
            Export::find($this->exportId)->update([
                'status' => 'FAILED',
                'meta' => json_encode(['error' => $e->getMessage()]),
            ]);
        }
    }
}