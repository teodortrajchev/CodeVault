@php
    $fallback = __("You don't have permission to do that. If you think this is a mistake, ask a project owner or manager to update your role.");
@endphp

<div x-data="{
        open: @js(session()->has('forbidden')),
        message: @js(is_string(session('forbidden')) ? session('forbidden') : null),
        fallback: @js($fallback),
     }"
     x-on:forbidden.window="message = null; open = true"
     x-on:keydown.escape.window="open = false"
     x-effect="if (open) $nextTick(() => $refs.ok?.focus())"
     x-show="open"
     x-transition.opacity
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center px-4"
     role="alertdialog" aria-modal="true"
     aria-labelledby="forbidden-title" aria-describedby="forbidden-message">

    <div class="fixed inset-0 bg-gray-500/75" @click="open = false"></div>

    <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl p-6">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>

            <div>
                <h3 id="forbidden-title" class="text-lg font-medium text-gray-900">{{ __('Permission denied') }}</h3>
                <p id="forbidden-message" class="mt-2 text-sm text-gray-600" x-text="message || fallback"></p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button x-ref="ok" type="button" @click="open = false"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Got it') }}
            </button>
        </div>
    </div>
</div>