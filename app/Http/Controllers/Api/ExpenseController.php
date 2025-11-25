<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Comment;
use App\Models\Expense;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Dashboard - Gère l'affichage pour Employé et Manager selon le rôle
     */
    public function index(Request $request){
        try{
            $this->authorize('viewAny', Expense::class);
            // Vérifier si c'est une vue web ou API
            $isWeb = !$request->wantsJson();
            
            // Initialiser la requête
            $query = Expense::query();

            // Si employé, filtrer par user_id
            if ($request->user()->isEmployee()) {
                $query->where('user_id', $request->user()->id);
            } else {
                // Si manager, inclure les relations user
                $query->with('user');
            }

            // Appliquer les filtres
            if (filled($request->input('status'))) {
                $query->where('status', $request->input('status'));
            }

            if (filled($request->input('category'))) {
                $query->where('category', $request->input('category'));
            }

            // Filtre de période
            $period = $request->input('period', now()->format('Y-m'));
            if (filled($period)) {
                [$year, $month] = explode('-', $period);
                $query->whereYear('spent_at', $year)->whereMonth('spent_at', $month);
            }

            $expenses = $query->orderBy('created_at', 'desc')->get();

            // Si c'est une vue web
            if ($isWeb) {
                if ($request->user()->isEmployee()) {
                    return view('employee.dashboard', compact('expenses', 'period'));
                } else {
                    // Pour manager, récupérer les stats depuis StatsController
                    $statsController = app(StatsController::class);
                    $statsResponse = $statsController->summary($request);
                    $stats = $statsResponse->getData(true);
                    
                    return view('manager.dashboard', compact('expenses', 'stats', 'period'));
                }
            }

            // Si c'est une requête API, retourner JSON
            return response()->json($expenses);
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Non autorisé à voir les dépenses.'], 403);
            }
            return back()->with('error', 'Non autorisé à voir les dépenses.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors de la récupération des dépenses.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue.');
        }
    }


    public function store(StoreExpenseRequest $request){
        try{
            $data = $request->validated();
        
            $data['user_id'] = $request->user()->id;
            $data['status'] = 'DRAFT';

            $expense = Expense::create($data);

            // Si c'est une requête API JSON, retourner JSON
            if ($request->wantsJson()) {
                return response()->json($expense, 201);
            }

            return back()->with('success', 'Dépense créée avec succès.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors de la création de la dépense.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors de la création de la dépense.');
        }
    }

    public function update(UpdateExpenseRequest $request, Expense $expense){
        try{
            $this->authorize('update', $expense);

            $data = $request->validated();

            $expense->update($data);

            if ($request->wantsJson()) {
                return response()->json($expense);
            }
            return back()->with('success', 'Dépense modifiée avec succès.');
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Non autorisé à modifier cette dépense.'], 403);
            }
            return back()->with('error', 'Non autorisé à modifier cette dépense.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour de la dépense.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour de la dépense.');
        }
    }

    public function submit(Request $request, Expense $expense){
        try{
            $this->authorize('submit', $expense);
            $oldStatus = $expense->status;
            $expense->status = 'SUBMITTED';
            $expense->save();
            ExpenseLogController::logStatusChange($expense, $oldStatus, auth()->user()->id);

            if ($request->wantsJson()) {
                return response()->json($expense);
            }
            return back()->with('success', 'Dépense soumise pour approbation.');
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Non autorisé à soumettre cette dépense.'], 403);
            }
            return back()->with('error', 'Non autorisé à soumettre cette dépense.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors de la soumission de la dépense.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors de la soumission de la dépense.');
        }
    }

    public function approve(Request $request, Expense $expense){
        try{
            $this->authorize('approve', $expense);

            $oldStatus = $expense->status;
            $expense->status = 'APPROVED';
            $expense->save();

            // Log de changement de statut
            ExpenseLogController::logStatusChange($expense, $oldStatus, auth()->user()->id);
            
            // Invalider le cache des stats pour la période de la dépense
            $period = \Carbon\Carbon::parse($expense->spent_at)->format('Y-m');
            StatsController::clearCache($period);

            if ($request->wantsJson()) {
                return response()->json($expense);
            }
            return back()->with('success', 'Dépense approuvée avec succès.');
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Non autorisé à approuver cette dépense.'], 403);
            }
            return back()->with('error', 'Non autorisé à approuver cette dépense.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors de l\'approbation de la dépense.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors de l\'approbation de la dépense.');
        }
    }

    public function reject(RejectExpenseRequest $request, Expense $expense){
        try{
            $this->authorize('reject', $expense);
            
            $oldStatus = $expense->status;
            $expense->status = 'REJECTED';
            $expense->save();
            
            // Log du changement de statut
            ExpenseLogController::logStatusChange($expense, $oldStatus, auth()->user()->id);
            
            // Invalider le cache des stats pour la période de la dépense
            $period = \Carbon\Carbon::parse($expense->spent_at)->format('Y-m');
            StatsController::clearCache($period);

            // Enregistrement du motif de rejet dans les commentaires
            Comment::create([
                'expense_id' => $expense->id,
                'user_id' => auth()->id(),
                'content' => $request->input('reason'),
            ]);

            if ($request->wantsJson()) {
                return response()->json($expense->load('comments'));
            }
            return back()->with('success', 'Dépense rejetée.');
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Non autorisé à rejeter cette dépense.'], 403);
            }
            return back()->with('error', 'Non autorisé à rejeter cette dépense.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors du rejet de la dépense.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors du rejet de la dépense.');
        }
    }

    public function pay(Request $request, Expense $expense){
        try{
            $this->authorize('pay', $expense);
            $oldStatus = $expense->status;
            $expense->status = 'PAID';
            $expense->save();
            ExpenseLogController::logStatusChange($expense, $oldStatus, auth()->user()->id);
            
            // Invalider le cache des stats pour la période de la dépense
            $period = \Carbon\Carbon::parse($expense->spent_at)->format('Y-m');
            StatsController::clearCache($period);

            if ($request->wantsJson()) {
                return response()->json($expense);
            }
            return back()->with('success', 'Dépense marquée comme payée.');
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Non autorisé à marquer cette dépense comme payée.'], 403);
            }
            return back()->with('error', 'Non autorisé à marquer cette dépense comme payée.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Une erreur est survenue lors du paiement de la dépense.'], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors du paiement de la dépense.');
        }
    }
}
