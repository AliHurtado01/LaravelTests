<section>

    <div>
        <!-- This is an example component -->

        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4"
            wire:click="openCreateModal">
            Crear Tarea Nueva
        </button>
        <div
            class='flex min-h-screen items-center justify-center min-h-screen from-white-200 via-white-300 to-white-500 bg-gradient-to-br'>
            <div class="flex items-center justify-center min-h-[450px]">
                <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">

                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Título</th>
                                    <th scope="col" class="py-3 px-6">Descripción</th>
                                    <th scope="col" class="py-3 px-6"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="py-4 px-6">{{ $task->title }}</td>
                                        <td class="py-4 px-6">{{ $task->description }}</td>
                                        <td class="py-4 px-6">
                                            <button
                                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                                                wire:click="openCreateModal({{ $task }})">
                                                Editar
                                            </button>
                                        <td class="py-4 px-6">
                                            <button
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                                wire:click="deleteModal({{ $task }})">
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            @if ($modal)
                <div
                    class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10">
                    <div class="max-h-full w-full max-w-xl overflow-y-auto sm:rounded-2xl bg-white">
                        <div class="w-full">
                            <div class="m-8 my-20 max-w-[400px] mx-auto">
                                <div class="mb-8">
                                    @if ($isEditMode == false)
                                        <h1 class="mb-4 text-3xl font-extrabold">Creación de tareas</h1>
                                    @else
                                        <h1 class="mb-4 text-3xl font-extrabold">Edición de tarea</h1>
                                    @endif
                                </div>
                                <div class="space-y-4">
                                    <form>
                                        <input type="text" placeholder="Título de la tarea"
                                            class="w-full border border-gray-300 rounded-md p-2" wire:model="title" />
                                        <textarea placeholder="Descripción de la tarea" rows="4" class="w-full border border-gray-300 rounded-md p-2"
                                            wire:model="description"></textarea>
                                        @if ($isEditMode == false)
                                            <button type="button"
                                                class="p-3 bg-black rounded-full text-white w-full font-semibold"
                                                wire:click="createTask">Crear Tarea</button> @else
                                            <button type="button"
                                                class="p-3 bg-black rounded-full text-white w-full font-semibold"
                                                wire:click="createTask">Actualizar Tarea</button> 
                                        @endif
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
        </div>
    </div>
</section>
