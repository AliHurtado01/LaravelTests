<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importante añadir esto
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación con las tareas PROPIAS (Uno a muchos)
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Relación con las tareas COMPARTIDAS (Muchos a muchos)
    // IMPORTANTE: El nombre debe ser 'sharedTasks' (Plural)
    public function sharedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')->withPivot('permission');
    }
}