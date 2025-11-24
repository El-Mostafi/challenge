<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'expense_id', 'user_id', 'from_status', 'to_status',
    ];

    public function expense() {
        return $this->belongsTo(Expense::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
