<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\User; // <--- IMPORTANTE: Necesario para listar usuarios
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
    public $confirmModal = false;
    public $taskToActId = null;
    public $actionType = '';

    // --- ESTO ES PARA COMPARTIR ---
    // Propiedades para el modal de compartir
    public $shareModal = false;
    public $taskToShare = null;   // La tarea que se está compartiendo actualmente
    public $users = [];           // Lista de todos los usuarios disponibles
    public $permissions = [];     // Array para guardar el permiso seleccionado (view/edit) por usuario
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
        // Cargamos la tarea y sus usuarios relacionados (para saber quién la tiene ya)
        $this->taskToShare = Task::with('users')->find($taskId);

        // Seguridad: Solo el dueño puede compartir
        if ($this->taskToShare->user_id !== Auth::id()) {
            return;
        }

        // Obtenemos todos los usuarios MENOS el actual (no tiene sentido compartírtela a ti mismo)
        $this->users = User::where('id', '!=', Auth::id())->get();

        // Preparamos el array de permisos
        foreach ($this->users as $user) {
            // Verificamos si este usuario ya tiene la tarea compartida
            $existingUser = $this->taskToShare->users->find($user->id);

            if ($existingUser) {
                // Si ya la tiene, cargamos su permiso actual
                $this->permissions[$user->id] = $existingUser->pivot->permission;
            } else {
                // Si no, permiso por defecto 'view'
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

    public function shareTaskWithUser($userId)
    {
        // Validamos que el permiso sea válido
        if (!in_array($this->permissions[$userId], ['view', 'edit'])) {
            return;
        }

        // --- USO DE ATTACH ---
        // Crea la relación en la tabla 'task_user' asignando el permiso seleccionado
        $this->taskToShare->users()->attach($userId, [
            'permission' => $this->permissions[$userId]
        ]);

        // Refrescamos la tarea para que la vista se actualice (el botón cambie a 'Dejar de compartir')
        $this->taskToShare->refresh();
    }

    public function unshareTaskWithUser($userId)
    {
        // --- USO DE DETACH ---
        // Elimina la relación de la tabla 'task_user'
        $this->taskToShare->users()->detach($userId);

        $this->taskToShare->refresh();
    }
    // --------------------------------------------

    public function render()
    {
        return view('livewire.task-component');
    }
}
