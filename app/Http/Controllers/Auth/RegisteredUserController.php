<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $delivered = true;

        try {
            event(new Registered($user));
        } catch (Throwable $e) {
            // The account is already persisted, so a mail outage must not fail
            // registration. The user can retry from the verification notice.
            $delivered = false;

            Log::error('Verification email could not be sent during registration.', [
                'user_id' => $user->getKey(),
                'exception' => $e,
            ]);
        }

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false))
            ->with('status', $delivered ? 'verification-link-sent' : 'verification-link-failed');
    }
}
