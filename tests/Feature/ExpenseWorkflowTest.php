<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test : Un employé peut créer une dépense en statut DRAFT
     */
    public function test_employee_can_create_expense()
    {
        $employee = User::factory()->create(['role' => 'EMPLOYEE']);

        $response = $this->actingAs($employee, 'sanctum')
                         ->postJson('/api/expenses', [
                             'title' => 'Déjeuner client',
                             'amount' => 45.50,
                             'currency' => 'EUR',
                             'spent_at' => '2025-11-20',
                             'category' => 'MEAL',
                         ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => 'DRAFT']);

        $this->assertDatabaseHas('expenses', [
            'title' => 'Déjeuner client',
            'status' => 'DRAFT',
        ]);
    }

    /**
     * Test : Un employé peut soumettre une dépense DRAFT
     */
    public function test_employee_can_submit_expense()
    {
        $employee = User::factory()->create(['role' => 'EMPLOYEE']);
        $expense = Expense::factory()->create([
            'user_id' => $employee->id,
            'status' => 'DRAFT',
        ]);

        $response = $this->actingAs($employee, 'sanctum')
                         ->postJson("/api/expenses/{$expense->id}/submit");

        $response->assertStatus(200)
                 ->assertJson(['status' => 'SUBMITTED']);

        // Vérifier que le log a été créé
        $this->assertDatabaseHas('expense_logs', [
            'expense_id' => $expense->id,
            'from_status' => 'DRAFT',
            'to_status' => 'SUBMITTED',
        ]);
    }

    /**
     * Test : Un manager peut approuver une dépense SUBMITTED
     */
    public function test_manager_can_approve_expense()
    {
        $manager = User::factory()->create(['role' => 'MANAGER']);
        $expense = Expense::factory()->create(['status' => 'SUBMITTED']);

        $response = $this->actingAs($manager, 'sanctum')
                         ->postJson("/api/expenses/{$expense->id}/approve");

        $response->assertStatus(200)
                 ->assertJson(['status' => 'APPROVED']);
    }

    /**
     * Test : Un manager peut rejeter une dépense avec un motif
     */
    public function test_manager_can_reject_expense_with_reason()
    {
        $manager = User::factory()->create(['role' => 'MANAGER']);
        $expense = Expense::factory()->create(['status' => 'SUBMITTED']);

        $response = $this->actingAs($manager, 'sanctum')
                         ->postJson("/api/expenses/{$expense->id}/reject", [
                             'reason' => 'Justificatif manquant ou invalide',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'REJECTED']);

        // Vérifier que le commentaire a été créé
        $this->assertDatabaseHas('comments', [
            'expense_id' => $expense->id,
            'content' => 'Justificatif manquant ou invalide',
        ]);
    }

    /**
     * Test : Un employé ne peut pas approuver une dépense
     */
    public function test_employee_cannot_approve_expense()
    {
        $employee = User::factory()->create(['role' => 'EMPLOYEE']);
        $expense = Expense::factory()->create(['status' => 'SUBMITTED']);

        $response = $this->actingAs($employee, 'sanctum')
                         ->postJson("/api/expenses/{$expense->id}/approve");

        $response->assertStatus(403);
    }
}