<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseLog;
use Illuminate\Http\Request;

class ExpenseLogController extends Controller
{
    static function logStatusChange(Expense $expense, String $oldStatus, int $changedByUserId)
    {
        ExpenseLog::create([
            'expense_id' => $expense->id,
            'user_id'=> $changedByUserId,
            'from_status' => $oldStatus,
            'to_status' => $expense->status,
        ]);
    }
}
