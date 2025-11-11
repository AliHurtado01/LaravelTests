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
    public $description;

    public $isEditMode;

    public function mount()
    {
        $this->getTasks();
    }

    public function deleteModal()
    {
        Task::whereTitle($this->title)->delete();
    }
    public function openCreateModal(?task $task = null)
    {
        if ($task) {
            $this->isEditMode = true;
            $this->title = $task->title;
            $this->description = $task->description;
            $this->modal=true;
        } else {
            $this->isEditMode = false;
            $this->modal=true;
        }
    }
    public function closeCreateModal()
    {
        $this->modal = false;
    }

    public function createTask()
    {
        Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'user_id' => Auth::user()->id,
        ]);
        $this->closeCreateModal();
        $this->getTasks();
    }
    public function getTasks()
    {
        $user = Auth::User();
        $this->tasks = $user->tasks;
    }

    public function render()
    {
        return view('livewire.task-component');
    }
}
