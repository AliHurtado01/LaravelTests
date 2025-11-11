<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Crea un título de tarea falso (ej: "Hacer la cosa del proyecto")
            'title' => fake()->catchPhrase(), 
            'is_completed' => fake()->boolean(20) // 20% de probabilidad de estar completada
        ];
    }
}
