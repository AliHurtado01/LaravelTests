<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Container\Attributes\Tag;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use function Livewire\Volt\title;

class TaskComponent extends Component
{
    public $tasks = [];
    public $modal = false;
    public $title;
    public $deleteModal = false; // <-- Añade esta línea para eliminar
    public $taskToDeleteId = null; // <-- Añade esta línea para eliminar
    public $description;
    public $taskIdToEdit = null;
    public $isEditMode;

    public function mount()
    {
        $this->getTasks();
    }


// Método para ABRIR el modal de confirmación
    public function openDeleteModal($taskId)
    {
        $this->taskToDeleteId = $taskId;
        $this->deleteModal = true;
    }

    // Método para CERRAR el modal de confirmación
    public function closeDeleteModal()
    {
        $this->deleteModal = false;
        $this->taskToDeleteId = null;
    }

    // Método para CONFIRMAR la eliminación
    public function confirmDelete()
    {
        // Busca la tarea y asegúrate de que pertenece al usuario
        $task = Task::where('id', $this->taskToDeleteId)
                    ->where('user_id', Auth::id())
                    ->first();

        if ($task) {
            $task->delete();
            $this->getTasks(); // Refresca la lista
        }

        $this->closeDeleteModal(); // Cierra el modal
    }
    public function openCreateModal(?task $task = null)
    {
        if ($task) {
            $this->isEditMode = true;
            $this->taskIdToEdit = $task->id;
            $this->title = $task->title;
            $this->description = $task->description;
            
        } else {
            $this->isEditMode = false;
            $this->reset(['title', 'description', 'taskIdToEdit']);
        }
        $this->modal = true; // Abre el modal en ambos casos
    }
    public function closeCreateModal()
    {
        $this->modal = false;
        // Resetea todo al cerrar
        $this->reset(['title', 'description', 'isEditMode', 'taskIdToEdit']);
    }

// Usar createTask
    public function createTask() 
    {
        // Buena práctica: añade validación
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($this->isEditMode) {
            // --- Lógica de ACTUALIZACIÓN ---
            $task = Task::find($this->taskIdToEdit);
            if ($task) {
                $task->update([
                    'title' => $this->title,
                    'description' => $this->description,
                ]);
            }
        } else {
            // --- Lógica de CREACIÓN --- (la que ya tenías)
            Task::create([
                'title' => $this->title,
                'description' => $this->description,
                'user_id' => Auth::user()->id,
            ]);
        }

        $this->closeCreateModal(); // Cierra el modal
        $this->getTasks(); // Refresca la lista de tareas
    }
    public function getTasks()
    {
        $user = Auth::User();
        $userTasks = $user->tasks;
        $sharedTasks = $user->sharedTasks;
        $this->tasks = $sharedTasks->merge($userTasks);
    }

    public function render()
    {
        return view('livewire.task-component');
    }
}
