<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'title', 'amount', 'currency', 'spent_at',
        'category', 'receipt_path', 'status'
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function logs() {
        return $this->hasMany(ExpenseLog::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
