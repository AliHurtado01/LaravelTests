<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;


class TaskComponent extends Component
{
    public $tasks = [];
    
    // Propiedades para Crear/Editar
    public $modal = false;
    public $title;
    public $description;
    public $taskIdToEdit = null;
    public $isEditMode = false;

    // Propiedades para Eliminar/Ocultar
    public $confirmModal = false; // Un solo modal para confirmar acciones
    public $taskToActId = null;   // ID de la tarea sobre la que actuar
    public $actionType = '';      // 'delete' o 'hide'

    public function mount()
    {
        $this->getTasks();
    }

    // --- CARGA DE TAREAS ---
    public function getTasks()
    {
        $user = Auth::user();
        // Tareas propias
        $userTasks = $user->tasks;
        // Tareas compartidas
        $sharedTasks = $user->sharedTasks;
        
        // Fusionamos ambas
        $this->tasks = $sharedTasks->merge($userTasks);
    }

    // --- MODAL CREAR / EDITAR ---
    
    public function openCreateModal(?Task $task = null)
    {
        // Limpiamos errores de validación previos
        $this->resetValidation();

        if ($task && $task->exists) {
            // MODO EDICIÓN
            $this->isEditMode = true;
            $this->taskIdToEdit = $task->id;
            $this->title = $task->title;
            $this->description = $task->description;
        } else {
            // MODO CREACIÓN
            $this->isEditMode = false;
            $this->reset(['title', 'description', 'taskIdToEdit']);
        }
        $this->modal = true;
    }

    public function closeCreateModal()
    {
        $this->modal = false;
        $this->reset(['title', 'description', 'taskIdToEdit', 'isEditMode']);
    }

    public function saveTask() 
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($this->isEditMode) {
            // --- ACTUALIZAR ---
            $task = Task::find($this->taskIdToEdit);
            
            // Comprobamos si existe y si tiene permiso (dueño O editor)
            if ($task) {
                $esDueno = $task->user_id === Auth::id();
                $esEditor = Auth::user()->sharedTasks()
                                ->where('task_id', $task->id)
                                ->wherePivot('permission', 'edit') // Solo si es editor
                                ->exists();

                if ($esDueno || $esEditor) {
                    $task->update([
                        'title' => $this->title,
                        'description' => $this->description,
                    ]);
                }
            }
        } else {
            // --- CREAR ---
            Task::create([
                'title' => $this->title,
                'description' => $this->description,
                'user_id' => Auth::id(),
            ]);
        }

        $this->closeCreateModal();
        $this->getTasks();
    }

    // --- MODAL ELIMINAR / OCULTAR ---

    // Abrir modal para ELIMINAR (Solo dueños)
    public function openDeleteModal($taskId)
    {
        $this->taskToActId = $taskId;
        $this->actionType = 'delete'; // Marcamos que vamos a borrar
        $this->confirmModal = true;
    }

    // Abrir modal para OCULTAR (Usuarios compartidos)
    public function openHideModal($taskId)
    {
        $this->taskToActId = $taskId;
        $this->actionType = 'hide';   // Marcamos que vamos a ocultar
        $this->confirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->confirmModal = false;
        $this->reset(['taskToActId', 'actionType']);
    }

    // Ejecutar la acción confirmada
    public function executeAction()
    {
        if ($this->actionType === 'delete') {
            // Borrado físico (Solo dueño)
            $task = Task::where('id', $this->taskToActId)
                        ->where('user_id', Auth::id())
                        ->first();
            if ($task) {
                $task->delete();
            }

        } elseif ($this->actionType === 'hide') {
            // Ocultar: Quitar relación de la tabla pivote (detach)
            // Esto hace que el usuario deje de ver la tarea, pero no la borra
            Auth::user()->sharedTasks()->detach($this->taskToActId);
        }

        $this->getTasks();
        $this->closeConfirmModal();
    }

    public function render()
    {
        return view('livewire.task-component');
    }
}