@extends('layouts.app')

@section('title', 'Dashboard Manager')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Gestion des Notes de Frais</h2>

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

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Statistiques - Période: {{ $stats['period'] ?? $period }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Total général -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Montant Total Approuvé</h6>
                                        <h2 class="text-primary">{{ number_format($stats['total_amount'] ?? 0, 2) }} €</h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Par statut -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">Par Statut</h6>
                                        <ul class="list-unstyled mb-0">
                                            @if(isset($stats['by_status']) && is_array($stats['by_status']))
                                                @foreach($stats['by_status'] as $status)
                                                    <li class="d-flex justify-content-between py-1">
                                                        <span>
                                                            @if($status['status'] == 'DRAFT') Brouillon
                                                            @elseif($status['status'] == 'SUBMITTED') Soumis
                                                            @elseif($status['status'] == 'APPROVED') Approuvé
                                                            @elseif($status['status'] == 'REJECTED') Rejeté
                                                            @elseif($status['status'] == 'PAID') Payé
                                                            @endif
                                                        </span>
                                                        <strong>{{ $status['count'] }}</strong>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Par catégorie -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">Par Catégorie (Approuvé)</h6>
                                        <ul class="list-unstyled mb-0">
                                            @if(isset($stats['by_category']) && is_array($stats['by_category']) && count($stats['by_category']) > 0)
                                                @foreach($stats['by_category'] as $category)
                                                    <li class="d-flex justify-content-between py-1">
                                                        <span>
                                                            @if($category['category'] == 'MEAL') Repas
                                                            @elseif($category['category'] == 'TRAVEL') Transport
                                                            @elseif($category['category'] == 'HOTEL') Hébergement
                                                            @else Autre
                                                            @endif
                                                        </span>
                                                        <strong>{{ number_format($category['total'], 2) }} €</strong>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-muted text-center py-2">Aucune dépense approuvée</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et Export -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="/manager/dashboard" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="">Tous</option>
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
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-secondary flex-fill">Filtrer</button>
                            <button type="button" onclick="document.getElementById('exportForm').submit()" class="btn btn-success">CSV</button>
                        </div>
                    </div>
                </form>
                <form id="exportForm" method="POST" action="/manager/exports/expenses" class="d-none">
                    @csrf
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="period" value="{{ request('period', $period) }}">
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
                                <th>Employé</th>
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
                                <td>{{ $expense->user->name }}</td>
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
                                    @if($expense->status == 'SUBMITTED')
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
                                    @if($expense->status == 'SUBMITTED')
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" action="/manager/expenses/{{ $expense->id }}/approve" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" 
                                                        onclick="return confirm('Approuver cette dépense ?')">
                                                    Approuver
                                                </button>
                                            </form>
                                            <button class="btn btn-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal{{ $expense->id }}">
                                                Rejeter
                                            </button>
                                        </div>
                                    @endif

                                    @if($expense->status == 'APPROVED')
                                        <form method="POST" action="/manager/expenses/{{ $expense->id }}/pay" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" 
                                                    onclick="return confirm('Marquer comme payée ?')">
                                                Marquer Payé
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal Rejet -->
                            @if($expense->status == 'SUBMITTED')
                            <div class="modal fade" id="rejectModal{{ $expense->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="/manager/expenses/{{ $expense->id }}/reject">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Rejeter la dépense</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>{{ $expense->title }}</strong> - {{ number_format($expense->amount, 2) }} €</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                                                    <textarea name="reason" class="form-control" rows="3" 
                                                              placeholder="Expliquez pourquoi cette dépense est rejetée..." 
                                                              required minlength="10"></textarea>
                                                    <small class="text-muted">Minimum 10 caractères</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">Rejeter</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune dépense trouvée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
