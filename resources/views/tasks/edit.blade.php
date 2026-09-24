<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Task — {{ $project->name }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-8">
        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $task->name) }}"
                       class="mt-1 w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="mt-1 w-full border rounded px-3 py-2">{{ old('description', $task->description) }}</textarea>
            </div>

            <div>
                <label for="due_date" class="block text-sm font-medium">Due date</label>
                <input type="date" id="due_date" name="due_date"
                       value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}"
                       class="mt-1 border rounded px-3 py-2">
            </div>

            <div>
                <label for="assigned_to" class="block text-sm font-medium">Assign to</label>
                <select id="assigned_to" name="assigned_to" class="mt-1 border rounded px-3 py-2">
                    <option value="">Unassigned</option>
                    @foreach ($project->members as $member)
                        <option value="{{ $member->id }}" @selected(old('assigned_to', $task->assigned_to) == $member->id)>
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium">Status</label>
                <select id="status" name="status" class="mt-1 border rounded px-3 py-2">
                    @foreach (['todo' => 'To do', 'in_progress' => 'In progress', 'done' => 'Done'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                    Save changes
                </button>
                <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 border rounded">
                    Cancel
                </a>
            </div>
        </form>

        <form method="POST" action="{{ route('projects.tasks.destroy', [$project, $task]) }}"
              onsubmit="return confirm('Delete this task?')" class="mt-6">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600">Delete task</button>
        </form>
    </div>
</x-app-layout>