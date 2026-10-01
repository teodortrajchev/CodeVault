@if ($tasks->isEmpty())
    <p class="text-sm text-gray-500">{{ $emptyMessage }}</p>
@else
    <table data-sortable class="w-full text-sm border-collapse">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Name</th>
                @if ($showStatus)
                    <th class="py-2">Status</th>
                @endif
                <th class="py-2">Priority</th>
                <th class="py-2">Assigned to</th>
                <th class="py-2">Due date</th>
                <th class="py-2" data-sort-method="none"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                @php
                    $priorityRank = match ($task->priority ?? 'medium') {
                        'low' => 1,
                        'high' => 3,
                        default => 2,
                    };
                    $statusRank = $task->status === 'in_progress' ? 2 : 1;
                @endphp
                <tr class="border-b">
                    <td class="py-2 {{ $showComplete ? '' : 'text-gray-400 line-through' }}"><a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-blue-600 hover:underline"> {{ $task->name }}</a> </td>
                    @if ($showStatus)
                        <td class="py-2" data-sort="{{ $statusRank }}">{{ str($task->status)->headline() }}</td>
                    @endif
                    <td class="py-2" data-sort="{{ $priorityRank }}">
                        <span @class([
                            'px-2 py-0.5 rounded text-xs',
                            'bg-red-100 text-red-700' => $task->priority === 'high',
                            'bg-yellow-100 text-yellow-700' => $task->priority === 'medium',
                            'bg-gray-100 text-gray-700' => $task->priority === 'low',
                        ])>
                            {{ str($task->priority ?? 'medium')->headline() }}
                        </span>
                    </td>
                    <td class="py-2">{{ $task->assignedUser->name ?? '—' }}</td>
                    <td class="py-2" data-sort="{{ optional($task->due_date)->format('Y-m-d') }}">
                        {{ optional($task->due_date)->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="py-2 text-right whitespace-nowrap">
                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="text-blue-600">
                            Edit
                        </a>

                        @if ($showComplete && $canContribute)
                            <form method="POST"
                                  action="{{ route('projects.tasks.complete', [$project, $task]) }}"
                                  class="inline ms-3">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-2 py-1 text-xs font-medium rounded bg-green-600 text-white hover:bg-green-700">
                                    Mark as finished
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif