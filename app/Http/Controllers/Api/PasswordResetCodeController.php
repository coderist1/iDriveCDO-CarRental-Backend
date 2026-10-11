<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PasswordResetCodeController extends Controller
{
    private const TTL_SECONDS = 600;

    private const RESEND_SECONDS = 60;

    private const MAX_TRIES = 5;

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:120']]);
        $email = strtolower(trim($data['email']));
        $key = $this->key($email);

        if (! Cache::add($key.':cooldown', 1, self::RESEND_SECONDS)) {
            return response()->json(['message' => 'A code was just sent. Wait a minute before asking for another.'], 429);
        }

        $code = (string) random_int(100000, 999999);

        try {
            Mail::raw(
                "Your ".config('app.name')." password reset code is {$code}.\n\n".
                "It expires in 10 minutes. If you did not ask to reset your password, ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)->subject('Your password reset code');
                }
            );
        } catch (Throwable $e) {
            Cache::forget($key.':cooldown');
            Log::warning('Password reset mail failed: '.$e->getMessage());

            return response()->json(['message' => 'The code could not be emailed right now. Please try again later or contact the desk.'], 503);
        }

        Cache::put($key, ['hash' => Hash::make($code), 'tries' => 0], self::TTL_SECONDS);

        AuditLog::create([
            'action' => 'password_code_sent',
            'actor_label' => 'guest',
            'detail' => 'Password reset code emailed to '.$email.'.',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Code sent. Check your email.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:120'],
            'code' => ['required', 'digits:6'],
        ]);
        $email = strtolower(trim($data['email']));
        $key = $this->key($email);
        $entry = Cache::get($key);

        if (! $entry) {
            return response()->json(['message' => 'That code has expired. Ask for a new one.'], 422);
        }

        if (! Hash::check($data['code'], $entry['hash'])) {
            $entry['tries']++;
            if ($entry['tries'] >= self::MAX_TRIES) {
                Cache::forget($key);

                return response()->json(['message' => 'Too many wrong codes. Ask for a new one.'], 422);
            }
            Cache::put($key, $entry, self::TTL_SECONDS);

            return response()->json(['message' => 'That code is not correct.'], 422);
        }

        Cache::forget($key);

        AuditLog::create([
            'action' => 'password_code_verified',
            'actor_label' => 'guest',
            'detail' => 'Password reset code accepted for '.$email.'.',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['verified' => true]);
    }

    private function key(string $email): string
    {
        return 'pwreset:'.hash('sha256', $email);
    }
}
