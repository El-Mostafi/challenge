<?php

use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Routes publiques
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Routes API publiques (pour login)
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Routes authentifiées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard redirection basée sur le rôle
    Route::get('/dashboard', function () {
        if (auth()->user()->isManager()) {
            return redirect('/manager/dashboard');
        }
        return redirect('/employee/dashboard');
    });
    
    // Routes Employé
    Route::middleware('role:EMPLOYEE')->prefix('employee')->group(function () {
        Route::get('/dashboard', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
        Route::post('/expenses/{expense}/submit', [ExpenseController::class, 'submit']);
    });
    
    // Routes Manager
    Route::middleware('role:MANAGER')->prefix('manager')->group(function () {
        Route::get('/dashboard', [ExpenseController::class, 'index']);
        Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
        Route::post('/expenses/{expense}/pay', [ExpenseController::class, 'pay']);
        Route::get('/stats/summary', [StatsController::class, 'summary']);
        Route::post('/exports/expenses', [ExportController::class, 'exportExpenses']);
        Route::get('/exports/{export}', [ExportController::class, 'download']);
    });
    
    // Routes API authentifiées (pour tests et compatibilité)
    Route::prefix('api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/expenses', [ExpenseController::class, 'index']);
        
        // Routes Employee API
        Route::middleware('role:EMPLOYEE')->group(function () {
            Route::post('/expenses', [ExpenseController::class, 'store']);
            Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
            Route::post('/expenses/{expense}/submit', [ExpenseController::class, 'submit']);
        });
        
        // Routes Manager API
        Route::middleware('role:MANAGER')->group(function () {
            Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
            Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
            Route::post('/expenses/{expense}/pay', [ExpenseController::class, 'pay']);
            Route::get('/stats/summary', [StatsController::class, 'summary']);
            Route::post('/exports/expenses', [ExportController::class, 'exportExpenses']);
            Route::get('/exports/{export}', [ExportController::class, 'download']);
        });
    });
});
