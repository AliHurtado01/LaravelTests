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
                                    <th scope="col" class="py-3 px-6"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    {{-- LÓGICA DE PERMISOS --}}
                                    @php
                                        $esDueno = $task->user_id === auth()->id();
                                        // Obtenemos el permiso de la tabla pivote (si existe)
                                        $permiso = optional($task->pivot)->permission; 
                                        
                                        // Reglas de visualización
                                        $puedeEditar = $esDueno || $permiso === 'edit';
                                        $puedeEliminar = $esDueno;
                                        // Si no es dueño, puede ocultar (si es compartida)
                                        $puedeOcultar = !$esDueno; 
                                    @endphp

                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="py-4 px-6">{{ $task->title }}</td>
                                        <td class="py-4 px-6">{{ $task->description }}</td>
                                        
                                        {{-- Columna Editar --}}
                                        <td class="py-4 px-6">
                                            @if($puedeEditar)
                                                <button
                                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                                                    wire:click="openCreateModal({{ $task }})">
                                                    Editar
                                                </button>
                                            @endif
                                        </td> 

                                        {{-- Columna Eliminar / Ocultar --}}
                                        <td class="py-4 px-6"> 
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
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($modal)
                <div class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10">
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

            @if ($confirmModal)
                <div class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10">
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

        </div>
    </div>
</section>