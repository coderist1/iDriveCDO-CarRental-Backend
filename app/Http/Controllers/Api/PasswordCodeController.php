<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PasswordCodeController extends Controller
{
    private const TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    /** Email a one-time code for a Google account that is setting a password. */
    public function store(Request $request): JsonResponse
    {
        $email = $this->email($request);

        $user = User::whereRaw('lower(email) = ?', [$email])->first();

        if (! $user) {
            return response()->json(['message' => 'No account uses that email.'], 404);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Your account has been disabled. Please contact the admin.'], 403);
        }

        if (! $user->google_id) {
            return response()->json(['message' => 'This account uses a password. Reset it with your old password.'], 422);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($email), [
            'hash' => $this->hashCode($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->getTimestamp(),
        ], now()->addMinutes(self::TTL_MINUTES));

        $user->notify(new PasswordResetCode($code));

        AuditLog::create([
            'action' => 'password_code_sent',
            'user_id' => $user->user_id,
            'detail' => 'Password reset code emailed.',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Code sent. Check your email.']);
    }

    /** Accept a code that was emailed for this address. The code is single use. */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $email = strtolower($data['email']);
        $key = $this->cacheKey($email);
        $stored = Cache::get($key);

        if (! is_array($stored) || ! isset($stored['hash'], $stored['expires_at'])) {
            return response()->json(['message' => 'That code is invalid or has expired. Request a new one.'], 422);
        }

        $expiresAt = Carbon::createFromTimestamp((int) $stored['expires_at']);
        if ($expiresAt->isPast()) {
            Cache::forget($key);

            return response()->json(['message' => 'That code is invalid or has expired. Request a new one.'], 422);
        }

        if (! hash_equals($stored['hash'], $this->hashCode($data['code']))) {
            $stored['attempts'] = (int) ($stored['attempts'] ?? 0) + 1;

            if ($stored['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($key);

                return response()->json(['message' => 'Too many incorrect codes. Request a new one.'], 422);
            }

            Cache::put($key, $stored, $expiresAt);

            return response()->json(['message' => 'That code is incorrect.'], 422);
        }

        Cache::forget($key);

        $user = User::whereRaw('lower(email) = ?', [$email])->first();

        AuditLog::create([
            'action' => 'password_code_ok',
            'user_id' => $user?->user_id,
            'actor_label' => $user ? null : 'guest',
            'detail' => 'Password reset code accepted.',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Code accepted.']);
    }

    private function email(Request $request): string
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        return strtolower($data['email']);
    }

    private function cacheKey(string $email): string
    {
        return 'password-code:'.hash('sha256', $email);
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }
}
