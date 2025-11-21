<section>
    <div>
        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4"
            wire:click="openCreateModal">
            Crear Tarea Nueva
        </button>
        
        <div class='flex min-h-screen items-center justify-center min-h-screen from-white-200 via-white-300 to-white-500 bg-gradient-to-br'>
            <div class="flex items-center justify-center min-h-[450px]">
                <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">

                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Título</th>
                                    <th scope="col" class="py-3 px-6">Descripción</th>
                                    <th scope="col" class="py-3 px-6">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    @php
                                        $esDueno = $task->user_id === auth()->id();
                                        $permiso = optional($task->pivot)->permission; 
                                        
                                        $puedeEditar = $esDueno || $permiso === 'edit';
                                        $puedeEliminar = $esDueno;
                                        $puedeOcultar = !$esDueno; 
                                    @endphp

                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="py-4 px-6">{{ $task->title }}</td>
                                        <td class="py-4 px-6">{{ $task->description }}</td>
                                        
                                        {{-- Columna Acciones --}}
                                        <td class="py-4 px-6 flex space-x-2">
                                            
                                            {{-- Editar --}}
                                            @if($puedeEditar)
                                                <button
                                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                                                    wire:click="openCreateModal({{ $task }})">
                                                    Editar
                                                </button>
                                            @endif

                                            {{-- Eliminar / Ocultar --}}
                                            @if($puedeEliminar)
                                                <button
                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                                    wire:click="openDeleteModal({{ $task->id }})"> 
                                                    Eliminar
                                                </button>
                                            @elseif($puedeOcultar)
                                                <button
                                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                                    wire:click="openHideModal({{ $task->id }})"> 
                                                    Ocultar
                                                </button>
                                            @endif

                                            {{-- ESTO ES PARA COMPARTIR (Botón en la tabla) --}}
                                            @if($esDueno)
                                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                                    wire:click="openShareModal({{ $task->id }})">
                                                    Compartir
                                                </button>
                                            @endif
                                            {{-- ----------------------------------------- --}}

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- MODAL CREAR/EDITAR --}}
            @if ($modal)
                <div class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10 z-40">
                    <div class="max-h-full w-full max-w-xl overflow-y-auto sm:rounded-2xl bg-white">
                        <div class="w-full">
                            <div class="m-8 my-20 max-w-[400px] mx-auto">
                                <div class="mb-8">
                                    <h1 class="mb-4 text-3xl font-extrabold">
                                        {{ $isEditMode ? 'Edición de tarea' : 'Creación de tareas' }}
                                    </h1>
                                </div>
                                <div class="space-y-4">
                                    <form>
                                        <input type="text" placeholder="Título de la tarea"
                                            class="w-full border border-gray-300 rounded-md p-2 text-gray-900" wire:model="title" />
                                        <textarea placeholder="Descripción de la tarea" rows="4" 
                                            class="w-full border border-gray-300 rounded-md p-2 text-gray-900"
                                            wire:model="description"></textarea>
                                        
                                        <button type="button"
                                            class="p-3 bg-black rounded-full text-white w-full font-semibold"
                                            wire:click="saveTask">
                                            {{ $isEditMode ? 'Actualizar Tarea' : 'Crear Tarea' }}
                                        </button>
                                        
                                        <button type="button"
                                            class="p-3 bg-gray-500 rounded-full text-white w-full font-semibold mt-2"
                                            wire:click="closeCreateModal">Cancelar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- MODAL CONFIRMAR (ELIMINAR/OCULTAR) --}}
            @if ($confirmModal)
                <div class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10 z-50">
                    <div class="max-h-full w-full max-w-xl overflow-y-auto sm:rounded-2xl bg-white">
                        <div class="w-full">
                            <div class="m-8 my-20 max-w-[400px] mx-auto">
                                <div class="mb-8">
                                    <h1 class="mb-4 text-3xl font-extrabold">
                                        {{ $actionType == 'delete' ? 'Confirmar Eliminación' : 'Ocultar Tarea' }}
                                    </h1>
                                    <p class="text-gray-600">
                                        @if($actionType == 'delete')
                                            ¿Estás seguro de que quieres eliminar esta tarea? Esta acción no se puede deshacer.
                                        @else
                                            ¿Quieres dejar de ver esta tarea? Se quitará de tu lista pero no se borrará para el propietario.
                                        @endif
                                    </p>
                                </div>
                                <div class="space-y-4">
                                    <button type="button"
                                        class="p-3 {{ $actionType == 'delete' ? 'bg-red-600' : 'bg-blue-600' }} rounded-full text-white w-full font-semibold"
                                        wire:click="executeAction">
                                        {{ $actionType == 'delete' ? 'Sí, Eliminar' : 'Sí, Ocultar' }}
                                    </button>
                                    <button type="button"
                                        class="p-3 bg-gray-500 rounded-full text-white w-full font-semibold mt-2"
                                        wire:click="closeConfirmModal">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ESTO ES PARA COMPARTIR (Modal Nuevo) --}}
            @if ($shareModal)
            <div class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10 z-50">
                <div class="max-h-full w-full max-w-2xl overflow-y-auto sm:rounded-2xl bg-white p-6">
                    
                    <div class="mb-6 border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Compartir Tarea</h2>
                        <p class="text-gray-600">Gestiona quién puede ver o editar: <strong>{{ $taskToShare->title }}</strong></p>
                    </div>

                    <div class="space-y-4 max-h-[60vh] overflow-y-auto">
                        {{-- Iteramos sobre todos los usuarios excepto el dueño --}}
                        @foreach($users as $user)
                            @php
                                // Verificamos si el usuario YA tiene la tarea compartida
                                $isShared = $taskToShare->users->contains($user->id);
                            @endphp

                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
                                
                                {{-- Nombre del usuario --}}
                                <div class="flex items-center space-x-3">
                                    <div class="bg-indigo-100 text-indigo-700 font-bold rounded-full w-10 h-10 flex items-center justify-center">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $user->name }}</span>
                                </div>

                                {{-- Controles --}}
                                <div class="flex items-center space-x-2">
                                    
                                    {{-- Select de Permisos --}}
                                    <select wire:model="permissions.{{ $user->id }}" 
                                            class="border-gray-300 rounded-md text-sm shadow-sm p-2"
                                            @if($isShared) disabled @endif>
                                        <option value="view">Solo ver</option>
                                        <option value="edit">Editar</option>
                                    </select>

                                    @if($isShared)
                                        {{-- Botón DETACH (Dejar de compartir) --}}
                                        <button wire:click="unshareTaskWithUser({{ $user->id }})" 
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-2 rounded text-sm font-semibold transition">
                                            Quitar
                                        </button>
                                    @else
                                        {{-- Botón ATTACH (Compartir) --}}
                                        <button wire:click="shareTaskWithUser({{ $user->id }})" 
                                                class="bg-green-500 text-white hover:bg-green-600 px-3 py-2 rounded text-sm font-semibold transition">
                                            Compartir
                                        </button>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end border-t pt-4">
                        <button wire:click="closeShareModal" 
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full">
                            Cerrar
                        </button>
                    </div>

                </div>
            </div>
            @endif
            {{-- ------------------------------------ --}}

        </div>
    </div>
</section>