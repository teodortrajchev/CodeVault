<div class="mt-8">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Tasks</h2>
        <a href="{{ route('projects.tasks.create', $project) }}" class="text-sm text-blue-600">
            + New task
        </a>
    </div>

    @if ($project->tasks->isEmpty())
        <p class="text-sm text-gray-500">No tasks yet.</p>
    @else
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2">Name</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Assigned to</th>
                    <th class="py-2">Due date</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($project->tasks as $task)
                    <tr class="border-b">
                        <td class="py-2">{{ $task->name }}</td>
                        <td class="py-2">{{ str($task->status)->headline() }}</td>
                        <td class="py-2">{{ $task->assignedUser->name ?? '—' }}</td>
                        <td class="py-2">{{ optional($task->due_date)->format('M j, Y') ?? '—' }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="text-blue-600">
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>