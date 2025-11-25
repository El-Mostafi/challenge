<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    /**
     * L'utilisateur peut voir une dépense :
     * - Manager → toutes
     * - Employé → uniquement les siennes
     */
    public function viewAny(User $user)
    {
        if ($user->isManager()) {
            return true; 
        }

        return $user->isEmployee(); 
    }


    /**
     * L'utilisateur(Employee) peut modifier uniquement les DRAFT ou REJECTED
     */
    public function update(User $user, Expense $expense)
    {
        return $expense->user_id === $user->id
            && in_array($expense->status, ['DRAFT', 'REJECTED']);
    }

    /**
     * Soumission d’une dépense (employee)
     */
    public function submit(User $user, Expense $expense)
    {
        return $expense->user_id === $user->id
            && $expense->status === 'DRAFT';
    }

    /**
     * Approbation (manager)
     */
    public function approve(User $user, Expense $expense)
    {
        return $user->isManager() && $expense->status === 'SUBMITTED';
    }

    /**
     * Rejet (manager)
     */
    public function reject(User $user, Expense $expense)
    {
        return $user->isManager() && $expense->status === 'SUBMITTED';
    }

    /**
     * Paiement (manager)
     */
    public function pay(User $user, Expense $expense)
    {
        return $user->isManager() && $expense->status === 'APPROVED';
    }
}
