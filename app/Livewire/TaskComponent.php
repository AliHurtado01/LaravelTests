<?php

namespace App\Livewire;

use App\Mail\SharedTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
    public $confirmModal = false;
    public $taskToActId = null;
    public $actionType = '';

    // --- ESTO ES PARA COMPARTIR ---
    public $shareModal = false;
    public $taskToShare = null;
    public $users = [];
    public $permissions = [];
    // ------------------------------

    public function mount()
    {
        $this->getTasks();
    }

    // --- CARGA DE TAREAS ---
    public function getTasks()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $userTasks = $user->tasks;
        $sharedTasks = $user->sharedTasks;

        $this->tasks = $sharedTasks->merge($userTasks);
    }

    // --- MODAL CREAR / EDITAR ---

    public function openCreateModal(?Task $task = null)
    {
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
            $task = Task::find($this->taskIdToEdit);

            if ($task) {
                $esDueno = $task->user_id === Auth::id();
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $esEditor = $user->sharedTasks()
                    ->where('task_id', $task->id)
                    ->wherePivot('permission', 'edit')
                    ->exists();

                if ($esDueno || $esEditor) {
                    $task->update([
                        'title' => $this->title,
                        'description' => $this->description,
                    ]);
                }
            }
        } else {
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

    public function openDeleteModal($taskId)
    {
        $this->taskToActId = $taskId;
        $this->actionType = 'delete';
        $this->confirmModal = true;
    }

    public function openHideModal($taskId)
    {
        $this->taskToActId = $taskId;
        $this->actionType = 'hide';
        $this->confirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->confirmModal = false;
        $this->reset(['taskToActId', 'actionType']);
    }

    public function executeAction()
    {
        if ($this->actionType === 'delete') {
            $task = Task::where('id', $this->taskToActId)
                ->where('user_id', Auth::id())
                ->first();
            if ($task) {
                $task->delete();
            }
        } elseif ($this->actionType === 'hide') {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->sharedTasks()->detach($this->taskToActId);
        }

        $this->getTasks();
        $this->closeConfirmModal();
    }

    // --- ESTO ES PARA COMPARTIR (Lógica Nueva) ---

    public function openShareModal($taskId)
    {
        $this->taskToShare = Task::with('users')->find($taskId);

        if ($this->taskToShare->user_id !== Auth::id()) {
            return;
        }

        $this->users = User::where('id', '!=', Auth::id())->get();

        foreach ($this->users as $user) {
            $existingUser = $this->taskToShare->users->find($user->id);

            if ($existingUser) {
                $this->permissions[$user->id] = $existingUser->pivot->permission;
            } else {
                $this->permissions[$user->id] = 'view';
            }
        }

        $this->shareModal = true;
    }

    public function closeShareModal()
    {
        $this->shareModal = false;
        $this->reset(['taskToShare', 'users', 'permissions']);
    }

    // --- AQUÍ ESTABA EL ERROR ---
    public function shareTaskWithUser($userId)
    {
        if (!in_array($this->permissions[$userId], ['view', 'edit'])) {
            return;
        }
        
        // 1. Buscamos al usuario real para tener sus datos (email, nombre)
        $userToShare = User::find($userId);

        if (!$userToShare) {
            return; // Seguridad por si el usuario no existe
        }

        $this->taskToShare->users()->attach($userId, [
            'permission' => $this->permissions[$userId]
        ]);

        $this->taskToShare->refresh();

        // 2. Enviamos el correo usando el objeto $userToShare y la tarea correcta
        Mail::to($userToShare->email)->send(
            new SharedTask($this->taskToShare, Auth()->user()));
    }

    public function unshareTaskWithUser($userId)
    {
        $this->taskToShare->users()->detach($userId);
        $this->taskToShare->refresh();
    }
    // --------------------------------------------

    public function render()
    {
        return view('livewire.task-component');
    }
}