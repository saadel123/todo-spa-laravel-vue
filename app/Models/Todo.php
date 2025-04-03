<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'user_id', 'completed', 'reminder_at', 'reminded_at'];

    protected $casts = [
        // 'reminder_at' => 'datetime:Y-m-d H:i:s',
        'reminder_at' => 'datetime',
        'reminded_at' => 'datetime',
        'completed' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
