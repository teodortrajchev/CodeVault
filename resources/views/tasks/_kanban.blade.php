
@php
    // Current week
    $weekStart = request('week')
        ? \Carbon\Carbon::parse(request('week'))->startOfWeek()
        : now()->startOfWeek();

    $weekEnd = $weekStart->copy()->endOfWeek();

    // Week navigation
    $previousWeek = $weekStart->copy()->subWeek()->format('Y-m-d');
    $nextWeek = $weekStart->copy()->addWeek()->format('Y-m-d');
    $currentWeek = now()->startOfWeek()->format('Y-m-d');

    // Monday to Sunday
    $days = collect(range(0, 6))->map(function ($offset) use ($weekStart) {
        return $weekStart->copy()->addDays($offset);
    });

    // Permission
    $canContribute = $project->roleFor(auth()->user())?->canContribute();

    // Tasks without a due date
    $unscheduledTasks = $project->tasks->filter(function ($task) {
        return !$task->due_date;
    });
@endphp

<div id="calendar-board">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Tasks
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                {{ $weekStart->format('M j') }}
                –
                {{ $weekEnd->format('M j, Y') }}
            </p>
        </div>

        {{-- Week navigation --}}
        <div class="flex items-center gap-2">

            {{-- Previous week --}}
            <a
                href="{{ request()->fullUrlWithQuery(['week' => $previousWeek]) }}"
                class="calendar-nav-button"
                title="Previous week"
            >
                ←
            </a>

            {{-- Today --}}
            <a
                href="{{ request()->fullUrlWithQuery(['week' => $currentWeek]) }}"
                class="calendar-today-button"
            >
                Today
            </a>

            {{-- Next week --}}
            <a
                href="{{ request()->fullUrlWithQuery(['week' => $nextWeek]) }}"
                class="calendar-nav-button"
                title="Next week"
            >
                →
            </a>

            {{-- New task --}}
            @if ($canContribute)
                <a
                    href="{{ route('projects.tasks.create', $project) }}"
                    class="ml-2 inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition"
                >
                    + New task
                </a>
            @endif

        </div>
    </div>

    {{-- Weekly calendar --}}
    <div class="calendar-wrapper">

        <div class="calendar-grid">

            @foreach ($days as $day)

                @php
                    $dayTasks = $project->tasks->filter(function ($task) use ($day) {
                        return $task->due_date &&
                            $task->due_date->isSameDay($day);
                    });

                    $isToday = $day->isToday();
                @endphp

                {{-- Day --}}
                <div
                    class="calendar-day {{ $isToday ? 'calendar-day-today' : '' }}"
                    data-date="{{ $day->format('Y-m-d') }}"
                >

                    {{-- Day header --}}
                    <div class="calendar-day-header">

                        <div>
                            <div class="calendar-day-name">
                                {{ $day->format('D') }}
                            </div>

                            <div
                                class="calendar-day-number {{ $isToday ? 'calendar-day-number-today' : '' }}"
                            >
                                {{ $day->format('j') }}
                            </div>
                        </div>

                        {{-- Task count --}}
                        <span class="calendar-task-count">
                            {{ $dayTasks->count() }}
                        </span>

                    </div>

                    {{-- Tasks --}}
                    <div class="calendar-day-tasks">

                        @forelse ($dayTasks as $task)

                            <div class="calendar-task">

                                {{-- Task name --}}
                                <a
                                    href="{{ route('projects.tasks.show', [$project, $task]) }}"
                                    class="calendar-task-title"
                                >
                                    {{ $task->name }}
                                </a>

                                {{-- Status --}}
                                <div class="mt-2">
                                    <span @class([
                                        'calendar-badge',
                                        'calendar-status-todo' => $task->status === 'todo',
                                        'calendar-status-progress' => $task->status === 'in_progress',
                                        'calendar-status-completed' => $task->status === 'completed',
                                    ])>
                                        {{ str($task->status)->headline() }}
                                    </span>
                                </div>

                                {{-- Description --}}
                                @if ($task->description)
                                    <p class="calendar-task-description">
                                        {{ $task->description }}
                                    </p>
                                @endif

                                {{-- Priority and assignees --}}
                                <div class="calendar-task-footer">

                                    <span @class([
                                        'calendar-badge',
                                        'calendar-priority-high' => $task->priority === 'high',
                                        'calendar-priority-medium' => $task->priority === 'medium',
                                        'calendar-priority-low' => $task->priority === 'low',
                                    ])>
                                        {{ str($task->priority ?? 'medium')->headline() }}
                                    </span>

                                    @if ($task->assignees->count())
                                        <span
                                            class="calendar-assignees"
                                            title="{{ $task->assignees->pluck('name')->join(', ') }}"
                                        >
                                            👤 {{ $task->assignees->count() }}
                                        </span>
                                    @endif

                                </div>

                                {{-- Edit --}}
                                <div class="calendar-task-actions">
                                    <a
                                        href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                        class="calendar-edit-link"
                                    >
                                        Edit
                                    </a>
                                </div>

                            </div>

                        @empty

                            {{-- Empty day --}}
                            <div class="calendar-empty">
                                No tasks
                            </div>

                        @endforelse

                    </div>

                </div>

            @endforeach

        </div>

    </div>

    {{-- Unscheduled tasks --}}
    @if ($unscheduledTasks->count())

        <div class="mt-6">

            <div class="flex items-center justify-between mb-3">

                <div>
                    <h3 class="font-semibold text-gray-800">
                        Unscheduled
                    </h3>

                    <p class="text-sm text-gray-500">
                        Tasks without a due date
                    </p>
                </div>

                <span class="calendar-task-count">
                    {{ $unscheduledTasks->count() }}
                </span>

            </div>

            <div class="unscheduled-grid">

                @foreach ($unscheduledTasks as $task)

                    <div class="calendar-task">

                        {{-- Task name --}}
                        <a
                            href="{{ route('projects.tasks.show', [$project, $task]) }}"
                            class="calendar-task-title"
                        >
                            {{ $task->name }}
                        </a>

                        {{-- Status --}}
                        <div class="mt-2">
                            <span @class([
                                'calendar-badge',
                                'calendar-status-todo' => $task->status === 'todo',
                                'calendar-status-progress' => $task->status === 'in_progress',
                                'calendar-status-completed' => $task->status === 'completed',
                            ])>
                                {{ str($task->status)->headline() }}
                            </span>
                        </div>

                        {{-- Description --}}
                        @if ($task->description)
                            <p class="calendar-task-description">
                                {{ $task->description }}
                            </p>
                        @endif

                        {{-- Priority and assignees --}}
                        <div class="calendar-task-footer">

                            <span @class([
                                'calendar-badge',
                                'calendar-priority-high' => $task->priority === 'high',
                                'calendar-priority-medium' => $task->priority === 'medium',
                                'calendar-priority-low' => $task->priority === 'low',
                            ])>
                                {{ str($task->priority ?? 'medium')->headline() }}
                            </span>

                            @if ($task->assignees->count())
                                <span
                                    class="calendar-assignees"
                                    title="{{ $task->assignees->pluck('name')->join(', ') }}"
                                >
                                    👤 {{ $task->assignees->count() }}
                                </span>
                            @endif

                        </div>

                        {{-- Edit --}}
                        <div class="calendar-task-actions">
                            <a
                                href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                class="calendar-edit-link"
                            >
                                Edit
                            </a>
                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    @endif

</div>
