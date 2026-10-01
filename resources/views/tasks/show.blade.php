<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $task->name }}
            </h2>

            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Back to') }} {{ $project->name }}
            </a>
        </div>
    </x-slot>

    @php
        $canContribute = $project->roleFor(auth()->user())?->canContribute() ?? false;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 rounded-md bg-green-50 border border-green-200 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">
                            {{ str($task->status)->headline() }}
                        </span>

                        <span @class([
                            'px-2 py-0.5 rounded text-xs',
                            'bg-red-100 text-red-700' => $task->priority === 'high',
                            'bg-yellow-100 text-yellow-700' => ($task->priority ?? 'medium') === 'medium',
                            'bg-gray-100 text-gray-700' => $task->priority === 'low',
                        ])>
                            {{ str($task->priority ?? 'medium')->headline() }} {{ __('priority') }}
                        </span>

                        @if ($task->due_date)
                            <span class="text-sm text-gray-500">
                                {{ __('Due') }} {{ $task->due_date->format('M j, Y') }}
                            </span>
                        @endif

                        <span class="text-sm text-gray-500">
                            {{ __('Assigned to') }}: {{ $task->assignedUser->name ?? '—' }}
                        </span>

                        @if ($canContribute)
                            <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                               class="ml-auto text-sm text-blue-600">
                                {{ __('Edit') }}
                            </a>
                        @endif
                    </div>

                    @if ($task->description)
                        <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $task->description }}</p>
                    @else
                        <p class="mt-4 text-sm text-gray-400 italic">{{ __('No description provided.') }}</p>
                    @endif
                </div>
            </div>

            @include('messages._board', ['project' => $project, 'task' => $task])
        </div>
    </div>
</x-app-layout>