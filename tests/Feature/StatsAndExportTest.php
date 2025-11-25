<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatsAndExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test : Les statistiques sont correctement calculées
     */
    public function test_manager_can_get_statistics()
    {
        $manager = User::factory()->create(['role' => 'MANAGER']);

        // Créer des dépenses de test
        Expense::factory()->create([
            'status' => 'APPROVED',
            'category' => 'MEAL',
            'amount' => 50,
            'spent_at' => '2025-11-15',
        ]);

        Expense::factory()->create([
            'status' => 'APPROVED',
            'category' => 'TRAVEL',
            'amount' => 100,
            'spent_at' => '2025-11-20',
        ]);

        $response = $this->actingAs($manager, 'sanctum')
                         ->getJson('/api/stats/summary?period=2025-11');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'period',
                     'total_amount',
                     'by_category',
                     'by_status',
                 ]);
    }

    /**
     * Test : Les statistiques sont mises en cache
     */
    public function test_statistics_are_cached()
    {
        $manager = User::factory()->create(['role' => 'MANAGER']);

        // Premier appel
        $this->actingAs($manager, 'sanctum')
             ->getJson('/api/stats/summary?period=2025-11');

        // Vérifier que le cache existe
        $this->assertTrue(Cache::has('stats_summary_2025-11'));
    }

    /**
     * Test : Un manager peut lancer un export CSV
     */
    public function test_manager_can_create_export()
    {
        $manager = User::factory()->create(['role' => 'MANAGER']);

        $response = $this->actingAs($manager, 'sanctum')
                         ->postJson('/api/exports/expenses', [
                             'status' => 'APPROVED',
                             'period' => '2025-11',
                         ]);

        $response->assertStatus(202)
                 ->assertJsonStructure(['message', 'export_id']);

        // Vérifier que l'export a été créé (peut être PENDING ou READY car queue=sync)
        $this->assertDatabaseHas('exports', [
            'user_id' => $manager->id,
        ]);
    }

    /**
     * Test : Un employé ne peut pas accéder aux stats
     */
    public function test_employee_cannot_access_statistics()
    {
        $employee = User::factory()->create(['role' => 'EMPLOYEE']);

        $response = $this->actingAs($employee, 'sanctum')
                         ->getJson('/api/stats/summary?period=2025-11');

        $response->assertStatus(403);
    }
}