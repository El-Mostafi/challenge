@extends('layouts.app')

@section('title', 'Dashboard Employé')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mes Notes de Frais</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                + Nouvelle Dépense
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filtres -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="/employee/dashboard" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="">Tous</option>
                            <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Brouillon</option>
                            <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Soumis</option>
                            <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approuvé</option>
                            <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejeté</option>
                            <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Payé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Catégorie</label>
                        <select name="category" class="form-select">
                            <option value="">Toutes</option>
                            <option value="MEAL" {{ request('category') == 'MEAL' ? 'selected' : '' }}>Repas</option>
                            <option value="TRAVEL" {{ request('category') == 'TRAVEL' ? 'selected' : '' }}>Transport</option>
                            <option value="HOTEL" {{ request('category') == 'HOTEL' ? 'selected' : '' }}>Hébergement</option>
                            <option value="OTHER" {{ request('category') == 'OTHER' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Période</label>
                        <input type="month" name="period" class="form-control" 
                               value="{{ request('period', now()->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des dépenses -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Montant</th>
                                <th>Catégorie</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->title }}</td>
                                <td>{{ number_format($expense->amount, 2) }} €</td>
                                <td>
                                    @if($expense->category == 'MEAL') Repas
                                    @elseif($expense->category == 'TRAVEL') Transport
                                    @elseif($expense->category == 'HOTEL') Hébergement
                                    @else Autre
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($expense->spent_at)->format('d/m/Y') }}</td>
                                <td>
                                    @if($expense->status == 'DRAFT')
                                        <span class="badge bg-secondary">Brouillon</span>
                                    @elseif($expense->status == 'SUBMITTED')
                                        <span class="badge bg-info">Soumis</span>
                                    @elseif($expense->status == 'APPROVED')
                                        <span class="badge bg-success">Approuvé</span>
                                    @elseif($expense->status == 'REJECTED')
                                        <span class="badge bg-danger">Rejeté</span>
                                    @elseif($expense->status == 'PAID')
                                        <span class="badge bg-primary">Payé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->status == 'DRAFT')
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#editModal{{ $expense->id }}">
                                                Modifier
                                            </button>
                                            <form method="POST" action="/employee/expenses/{{ $expense->id }}/submit" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" 
                                                        onclick="return confirm('Soumettre cette dépense ?')">
                                                    Soumettre
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal Édition -->
                            @if($expense->status == 'DRAFT')
                            <div class="modal fade" id="editModal{{ $expense->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="/employee/expenses/{{ $expense->id }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier la Dépense</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="currency" value="EUR">
                                                <div class="mb-3">
                                                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                                                    <input type="text" name="title" class="form-control" 
                                                           value="{{ $expense->title }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Montant (EUR) <span class="text-danger">*</span></label>
                                                    <input type="number" name="amount" step="0.01" min="0.01" 
                                                           class="form-control" value="{{ $expense->amount }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                                    <select name="category" class="form-select" required>
                                                        <option value="MEAL" {{ $expense->category == 'MEAL' ? 'selected' : '' }}>Repas</option>
                                                        <option value="TRAVEL" {{ $expense->category == 'TRAVEL' ? 'selected' : '' }}>Transport</option>
                                                        <option value="HOTEL" {{ $expense->category == 'HOTEL' ? 'selected' : '' }}>Hébergement</option>
                                                        <option value="OTHER" {{ $expense->category == 'OTHER' ? 'selected' : '' }}>Autre</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Date de la dépense <span class="text-danger">*</span></label>
                                                    <input type="date" name="spent_at" class="form-control" 
                                                           value="{{ \Carbon\Carbon::parse($expense->spent_at)->format('Y-m-d') }}" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune dépense trouvée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/employee/expenses">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Dépense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="currency" value="EUR">
                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (EUR) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <option value="MEAL">Repas</option>
                            <option value="TRAVEL">Transport</option>
                            <option value="HOTEL">Hébergement</option>
                            <option value="OTHER">Autre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de la dépense <span class="text-danger">*</span></label>
                        <input type="date" name="spent_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
