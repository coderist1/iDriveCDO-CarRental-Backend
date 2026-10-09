<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleAuthController extends Controller
{
    /** Public client id for the browser button; the secret never leaves the server. */
    public function config(): JsonResponse
    {
        return response()->json(['client_id' => config('services.google.client_id')]);
    }

    public function signIn(Request $request): JsonResponse
    {
        $request->validate(['credential' => ['required', 'string', 'max:4096']]);

        $verifier = new GoogleIdTokenVerifier(config('services.google.client_id'));
        try {
            $google = $verifier->verify($request->string('credential')->toString());
        } catch (RuntimeException $e) {
            AuditLog::create([
                'action' => 'google_login_failed',
                'actor_label' => 'guest',
                'detail' => Str::limit($e->getMessage(), 170),
                'ip_address' => $request->ip(),
            ]);

            return response()->json(['message' => $e->getMessage()], 401);
        }

        [$user, $created] = DB::transaction(function () use ($google) {
            $user = User::where('google_id', $google['sub'])->first()
                ?? User::whereRaw('lower(email) = ?', [$google['email']])->first();

            if ($user) {
                $updates = [];
                if (! $user->google_id) {
                    $updates['google_id'] = $google['sub'];
                }
                if (! $user->avatar && $google['picture']) {
                    $updates['avatar'] = $google['picture'];
                }
                if ($updates) {
                    $user->update($updates);
                }

                return [$user, false];
            }

            [$first, $last] = $this->splitName($google);

            return [User::create([
                'email' => $google['email'],
                'google_id' => $google['sub'],
                'first_name' => $first,
                'last_name' => $last,
                'phone' => null,
                'avatar' => $google['picture'] ?: null,
                'role' => 'customer',
                'status' => 'active',
                'password_hash' => Str::random(40),
            ]), true];
        });

        if ($user->status !== 'active') {
            AuditLog::create([
                'action' => 'google_login_blocked',
                'user_id' => $user->user_id,
                'detail' => 'Disabled account tried Google sign-in.',
                'ip_address' => $request->ip(),
            ]);

            return response()->json(['message' => 'This account is disabled.'], 403);
        }

        AuditLog::create([
            'action' => $created ? 'google_register' : 'google_login',
            'user_id' => $user->user_id,
            'detail' => $created ? 'Account created with Google sign-in.' : 'Signed in with Google.',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['user' => $user->refresh(), 'created' => $created]);
    }

    /** @return array{0: string, 1: string} */
    private function splitName(array $google): array
    {
        $first = trim($google['given_name']);
        $last = trim($google['family_name']);
        if ($first === '' && $google['name'] !== '') {
            $pieces = preg_split('/\s+/', trim($google['name']));
            $last = count($pieces) > 1 ? array_pop($pieces) : $last;
            $first = implode(' ', $pieces);
        }
        if ($first === '') {
            $first = Str::before($google['email'], '@');
        }

        return [Str::limit($first, 40, ''), Str::limit($last, 40, '') ?: '-'];
    }
}
