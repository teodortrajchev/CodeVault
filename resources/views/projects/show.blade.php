<x-app-layout>
    <x-slot name="header">
                <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>

            <div class="flex items-center gap-4">
                <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Back to Projects') }}
                </a>

                    @if ($project->roleFor(auth()->user())?->canManage())
                    <form method="POST" action="{{ route('projects.status.update', $project) }}">
                        @csrf
                        @method('PATCH')

                        @if ($project->status === 'completed')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">
                                {{ __('Reopen project') }}
                            </button>
                        @else
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="text-sm text-green-600 hover:text-green-800">
                                {{ __('Mark as finished') }}
                            </button>
                        @endif
                    </form>
                @endif
                    @if ($project->roleFor(auth()->user())?->canDeleteProject())
                    <x-confirm-delete
                        :action="route('projects.destroy', $project)"
                        title="Delete this project?"
                        :message="'“' . $project->name . '” and all of its tasks, messages and members will be permanently deleted. This cannot be undone.'"
                        confirm="Delete project">
                        {{ __('Delete project') }}
                    </x-confirm-delete>
                @endif
            </div>
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
                <div class="p-6 text-gray-900">
                    @include('tasks._tasks', ['project' => $project])
                </div>
            </div>

            @include('messages._board', ['project' => $project])
            @php
    $actorRole = $project->roleFor(auth()->user());
    $canManage = $actorRole?->canManage() ?? false;
    $isOwner = $actorRole === \App\Enums\ProjectRole::Owner;
    $canChangeRoles = $actorRole?->canChangeRoles() ?? false;
    $roleOptions = ['owner' => 'Owner', 'manager' => 'Manager', 'member' => 'Member', 'viewer' => 'Viewer'];
    $invitations = $invitations ?? collect();
    $inviteHasErrors = $errors->has('email') || $errors->has('role');
@endphp

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6" x-data="{ open: {{ $inviteHasErrors ? 'true' : 'false' }} }">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-900">{{ __('Members') }}</h3>

            @if ($canManage)
                <button type="button"
                        @click="open = !open"
                        class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                    <span x-show="!open">+ {{ __('Invite collaborator') }}</span>
                    <span x-show="open" x-cloak>{{ __('Cancel') }}</span>
                </button>
            @endif
        </div>

        @if ($canManage)
            <form x-show="open"
                  x-cloak
                  x-transition
                  method="POST"
                  action="{{ route('projects.invitations.store', $project) }}"
                  class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                @csrf

                <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1">
                        <label for="invite-email" class="block text-sm font-medium text-gray-700">
                            {{ __('Email address') }}
                        </label>
                        <input type="email"
                               id="invite-email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="name@example.com"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="invite-role" class="block text-sm font-medium text-gray-700">
                            {{ __('Role') }}
                        </label>
                        <select id="invite-role"
                                name="role"
                                class="mt-1 block border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($roleOptions as $value => $label)
                                @if ($value !== 'owner' || $isOwner)
                                    <option value="{{ $value }}" @selected(old('role', 'member') === $value)>
                                        {{ $label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-primary-button>{{ __('Send invite') }}</x-primary-button>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    {{ __('They will get an email with a link that expires in 7 days.') }}
                </p>
            </form>
        @endif

        @if ($errors->roleUpdate->any())
        <div class="mb-4 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-700">
            {{ $errors->roleUpdate->first() }}
        </div>
        @endif

        <ul role="list" class="divide-y divide-gray-200">
            @foreach ($project->members as $member)
                <li class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                    </div>

                    @if ($canChangeRoles && $member->id !== auth()->id())
                        <form method="POST" action="{{ route('projects.members.update', [$project, $member]) }}">
                        @csrf
                        @method('PUT')
                        <label for="role-{{ $member->id }}" class="sr-only">{{ __('Role for :name', ['name' => $member->name]) }}</label>
                        <select id="role-{{ $member->id }}" name="role"  onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($roleOptions as $value => $label)
                                <option value="{{ $value }}" @selected($member->pivot->role === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="text-sm text-indigo-600">{{ __('Save') }}</button></noscript>
                        </form>
                   @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize">
                            {{ $member->pivot->role }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($canManage && $invitations->isNotEmpty())
            <h4 class="text-sm font-medium text-gray-900 mt-8 mb-2">{{ __('Pending invitations') }}</h4>

            <ul role="list" class="divide-y divide-gray-200">
                @foreach ($invitations as $invitation)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $invitation->email }}</p>
                            <p class="text-sm text-gray-500">
                                {{ __('Expires') }} {{ $invitation->expires_at->format('M j, Y') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 capitalize">
                                {{ $invitation->role }}
                            </span>

                            <form method="POST"
                                  action="{{ route('projects.invitations.destroy', [$project, $invitation]) }}"
                                  onsubmit="return confirm('Revoke this invitation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                    {{ __('Revoke') }}
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
        </div>
    </div>
</x-app-layout>