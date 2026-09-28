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
                    <div class="flex items-center gap-2 px-4 py-3 sm:px-6 border-b border-gray-200 text-sm text-gray-600">
                        <label for="project-sort">{{ __('Sort by') }}:</label>
                        <select id="project-sort" class="text-sm border-gray-300 rounded-md py-1">
                            <option value="">{{ __('Default') }}</option>
                            <option value="name:asc">{{ __('Name (A–Z)') }}</option>
                            <option value="name:desc">{{ __('Name (Z–A)') }}</option>
                            <option value="due:asc">{{ __('Due date (soonest)') }}</option>
                            <option value="due:desc">{{ __('Due date (latest)') }}</option>
                            <option value="status:asc">{{ __('Status') }}</option>
                            <option value="created:asc">{{ __('Oldest first') }}</option>
                            <option value="created:desc">{{ __('Newest first') }}</option>
                        </select>
                    </div>

                    <ul id="project-list" role="list" class="divide-y divide-gray-200">
                        @foreach ($projects as $project)
                            <li data-name="{{ $project->name }}"
                                data-due="{{ optional($project->due_date)->format('Y-m-d') }}"
                                data-status="{{ $project->status }}"
                                data-created="{{ $project->created_at->format('Y-m-d H:i:s') }}">
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

                    <script>
                        const projectList = document.getElementById('project-list');
                        const originalOrder = Array.from(projectList.children);

                        document.getElementById('project-sort').addEventListener('change', (e) => {
                            if (!e.target.value) {
                                originalOrder.forEach((li) => projectList.appendChild(li));
                                return;
                            }

                            const [key, dir] = e.target.value.split(':');
                            const m = dir === 'asc' ? 1 : -1;

                            Array.from(projectList.children)
                                .sort((a, b) => {
                                    const av = a.dataset[key] || '';
                                    const bv = b.dataset[key] || '';
                                    if (!av || !bv) return !av - !bv; // empty values go last
                                    return av.localeCompare(bv, undefined, { numeric: true, sensitivity: 'base' }) * m;
                                })
                                .forEach((li) => projectList.appendChild(li));
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>