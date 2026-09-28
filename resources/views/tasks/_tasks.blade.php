@php
    $canContribute = $project->roleFor(auth()->user())?->canContribute();

    // partition() returns [matching, non-matching]
    [$doneTasks, $openTasks] = $project->tasks->partition(
        fn ($t) => in_array($t->status, ['completed', 'done'])
    );
@endphp

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold">Tasks</h2>
        <a href="{{ route('projects.tasks.create', $project) }}" class="text-sm text-blue-600">
            + New task
        </a>
    </div>

    <h3 class="text-sm font-medium text-gray-900 mb-2">
        To do <span class="text-gray-400">({{ $openTasks->count() }})</span>
    </h3>
    @include('tasks._table_tasks', [
        'tasks' => $openTasks,
        'project' => $project,
        'showStatus' => true,
        'showComplete' => true,
        'canContribute' => $canContribute,
        'emptyMessage' => 'Nothing left to do.',
    ])

    <h3 class="text-sm font-medium text-gray-900 mt-8 mb-2">
        Completed <span class="text-gray-400">({{ $doneTasks->count() }})</span>
    </h3>
    @include('tasks._table_tasks', [
        'tasks' => $doneTasks,
        'project' => $project,
        'showStatus' => false,
        'showComplete' => false,
        'canContribute' => $canContribute,
        'emptyMessage' => 'No completed tasks yet.',
    ])
</div>

<style>
    table[data-sortable] th { cursor: pointer; user-select: none; }
    table[data-sortable] th[data-sort-method="none"] { cursor: default; }
    table[data-sortable] th[aria-sort="ascending"]::after { content: " ▲"; }
    table[data-sortable] th[aria-sort="descending"]::after { content: " ▼"; }
</style>
<script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/dist/tablesort.min.js"></script>
<script>
    document.querySelectorAll('table[data-sortable]').forEach((t) => new Tablesort(t));
</script>