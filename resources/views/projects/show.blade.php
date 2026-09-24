<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>

            <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Back to Projects') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 rounded-md bg-green-50 border border-green-200 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center gap-3">
                        <span @class([
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                            'bg-green-100 text-green-800' => $project->status === 'active',
                            'bg-gray-100 text-gray-800' => $project->status !== 'active',
                        ])>
                            {{ $project->status }}
                        </span>

                        @if ($project->due_date)
                            <span class="text-sm text-gray-500">
                                {{ __('Due') }} {{ $project->due_date->format('M d, Y') }}
                            </span>
                        @endif
                    </div>

                    @if ($project->description)
                        <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">
                            {{ $project->description }}
                        </p>
                    @else
                        <p class="mt-4 text-sm text-gray-400 italic">
                            {{ __('No description provided.') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('Members') }}</h3>

                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach ($project->members as $member)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                </div>

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize">
                                    {{ $member->pivot->role }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>