
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>

            <a href="{{ route('projects.create') }}">
                <x-primary-button>{{ __('New Project') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 px-4 py-3 rounded-md bg-green-50 border border-green-200 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($projects->isEmpty())
                    <div class="p-12 text-center">
                        <h3 class="text-sm font-medium text-gray-900">{{ __('No projects yet') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Get started by creating your first project.') }}</p>
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}">
                                <x-primary-button>{{ __('New Project') }}</x-primary-button>
                            </a>
                        </div>
                    </div>
                @else
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach ($projects as $project)
                            <li>
                                <a href="{{ route('projects.show', $project) }}" class="block hover:bg-gray-50 transition ease-in-out duration-150">
                                    <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-indigo-600 truncate">
                                                {{ $project->name }}
                                            </p>
                                            @if ($project->description)
                                                <p class="mt-1 text-sm text-gray-500 truncate">
                                                    {{ $project->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="ms-4 flex flex-shrink-0 items-center gap-4">
                                            @if ($project->due_date)
                                                <span class="text-sm text-gray-500">
                                                    {{ __('Due') }} {{ $project->due_date->format('M d, Y') }}
                                                </span>
                                            @endif

                                            <span @class([
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                                'bg-green-100 text-green-800' => $project->status === 'active',
                                                'bg-gray-100 text-gray-800' => $project->status !== 'active',
                                            ])>
                                                {{ $project->status }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
