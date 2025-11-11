<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    // Permite rellenar estos campos masivamente
    protected $fillable = ['title','description', 'is_completed', 'user_id']; 

    // Define la relación inversa
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}