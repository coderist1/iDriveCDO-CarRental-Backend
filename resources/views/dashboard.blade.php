<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (request()->query('verified'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    {{ __('Your email has been successfully verified.') }}
                </div>
            @elseif (session('status') === 'already-verified')
                <div class="mb-4 rounded-lg bg-blue-50 p-4 text-sm font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ __('Your email address is already verified.') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
