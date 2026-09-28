<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Project invitation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">

                @if ($state === 'ready')
                    <p class="text-sm">
                        <strong>{{ $invitation->inviter->name }}</strong> invited you to join
                        <strong>{{ $invitation->project->name }}</strong> as
                        <strong>{{ ucfirst($invitation->role) }}</strong>.
                    </p>

                    <form method="POST" action="{{ route('invitations.accept', $token) }}" class="mt-6 flex items-center gap-4">
                        @csrf
                        <x-primary-button>{{ __('Accept invitation') }}</x-primary-button>
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Not now') }}
                        </a>
                    </form>

                @elseif ($state === 'mismatch')
                    <p class="text-sm">
                        This invitation was sent to <strong>{{ $invitation->email }}</strong>, but you're signed in as
                        <strong>{{ auth()->user()->email }}</strong>. Please sign in with the invited email address.
                    </p>

                @elseif ($state === 'expired')
                    <p class="text-sm">
                        This invitation has expired. Ask {{ $invitation->inviter->name }} to send a new one.
                    </p>

                @else
                    <p class="text-sm">This invitation has already been used.</p>
                @endif

                @if ($state !== 'ready')
                    <a href="{{ route('dashboard') }}" class="mt-6 inline-block text-sm text-blue-600">
                        {{ __('Go to dashboard') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>