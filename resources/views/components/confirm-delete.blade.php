@props([
    'action',
    'title' => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'confirm' => 'Delete',
    'method' => 'DELETE',
])

@php $uid = 'confirm-' . uniqid(); @endphp

<div x-data="{ open: false, busy: false }" x-on:keydown.escape.window="open = false">
    {{-- Trigger --}}
    <button type="button" @click="open = true" class="text-sm text-red-600 hover:text-red-800">
        {{ $slot }}
    </button>

    {{-- Modal --}}
    <div x-show="open"
         x-transition.opacity
         x-effect="if (open) $nextTick(() => $refs.cancel?.focus())"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         role="alertdialog" aria-modal="true"
         aria-labelledby="{{ $uid }}-title" aria-describedby="{{ $uid }}-message">

        <div class="fixed inset-0 bg-gray-500/75" @click="open = false"></div>

        <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>

                <div>
                    <h3 id="{{ $uid }}-title" class="text-lg font-medium text-gray-900">{{ $title }}</h3>
                    <p id="{{ $uid }}-message" class="mt-2 text-sm text-gray-600">{{ $message }}</p>
                </div>
            </div>

            <form method="POST" action="{{ $action }}" @submit="busy = true"
                  class="mt-6 flex justify-end gap-3">
                @csrf
                @method($method)

                <button type="button" x-ref="cancel" @click="open = false"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" :disabled="busy"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                    {{ $confirm }}
                </button>
            </form>
        </div>
    </div>
</div>