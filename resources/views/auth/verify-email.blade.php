<x-guest-layout>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        {{ __('Verify Your Email') }}
    </h2>

    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
        {{ __("We've sent a verification link to:") }}

        <p class="mt-1 font-medium break-all text-gray-900 dark:text-gray-100">
            {{ $user->email }}
        </p>

        <p class="mt-3">
            {{ __('Please check your inbox and click the verification button.') }}
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @elseif (session('status') === 'verification-link-expired')
        <div class="mt-4 rounded-md bg-amber-50 p-3 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
            {{ __('This verification link has expired. Please request a new verification email.') }}
        </div>
    @elseif (session('status') === 'verification-link-invalid')
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
            {{ __('This verification link is invalid. Please request a new verification email.') }}
        </div>
    @elseif (session('status') === 'verification-link-failed')
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
            {{ __("We couldn't send the verification email right now. Please try again later.") }}
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}" x-data="{ sending: false }" @submit="sending = true">
            @csrf

            <x-primary-button x-bind:disabled="sending" class="w-full justify-center sm:w-auto">
                <svg x-show="sending" style="display: none;" class="-ms-1 me-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>

                <span x-show="! sending">{{ __('Resend Verification Email') }}</span>
                <span x-show="sending" style="display: none;">{{ __('Sending...') }}</span>
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

    <p class="mt-6 border-t border-gray-200 pt-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
        {{ __("Didn't receive the email? Check your spam/junk folder.") }}
    </p>
</x-guest-layout>
