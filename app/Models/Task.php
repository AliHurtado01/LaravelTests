<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <--- IMPORTANTE: Añadir esto

class Task extends Model
{
    use HasFactory;

    // Permite rellenar estos campos masivamente
    protected $fillable = ['title','description', 'is_completed', 'user_id']; 

    // Relación con el DUEÑO de la tarea (quien la creó)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- AÑADIR ESTO ---
    // Relación con los USUARIOS COMPARTIDOS (Muchos a muchos)
    public function users(): BelongsToMany
    {
        // Definimos que una tarea pertenece a muchos usuarios a través de la tabla 'task_user'
        // y que queremos acceder al campo extra 'permission' de la tabla pivote.
        return $this->belongsToMany(User::class, 'task_user')->withPivot('permission');
    }
}