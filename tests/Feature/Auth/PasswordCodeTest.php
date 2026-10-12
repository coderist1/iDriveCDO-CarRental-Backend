<?php

use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Support\Facades\Notification;

test('a google account can be emailed a password code', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'Ada@Example.com',
        'google_id' => 'google-sub-1',
    ]);

    $this->postJson('/api/auth/password-code', ['email' => 'ada@example.com'])
        ->assertOk()
        ->assertJson(['message' => 'Code sent. Check your email.']);

    Notification::assertSentTo($user, PasswordResetCode::class, function (PasswordResetCode $notification) use ($user) {
        expect($notification->toMail($user)->introLines)->toContain($notification->code);

        return true;
    });
});

test('the emailed code can be verified once', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'ada@example.com',
        'google_id' => 'google-sub-1',
    ]);

    $this->postJson('/api/auth/password-code', ['email' => $user->email])->assertOk();

    $code = null;
    Notification::assertSentTo($user, PasswordResetCode::class, function (PasswordResetCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->postJson('/api/auth/password-code/verify', [
        'email' => $user->email,
        'code' => $code,
    ])->assertOk()->assertJson(['message' => 'Code accepted.']);

    $this->postJson('/api/auth/password-code/verify', [
        'email' => $user->email,
        'code' => $code,
    ])->assertStatus(422);
});

test('an incorrect code is rejected and too many guesses invalidate it', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'ada@example.com',
        'google_id' => 'google-sub-1',
    ]);

    $this->postJson('/api/auth/password-code', ['email' => $user->email])->assertOk();

    $code = null;
    Notification::assertSentTo($user, PasswordResetCode::class, function (PasswordResetCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $wrong = $code === '111111' ? '222222' : '111111';

    foreach (range(1, 4) as $ignored) {
        $this->postJson('/api/auth/password-code/verify', [
            'email' => $user->email,
            'code' => $wrong,
        ])->assertStatus(422)->assertJson(['message' => 'That code is incorrect.']);
    }

    $this->postJson('/api/auth/password-code/verify', [
        'email' => $user->email,
        'code' => $wrong,
    ])->assertStatus(422)->assertJson(['message' => 'Too many incorrect codes. Request a new one.']);
});

test('a password code expires', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'ada@example.com',
        'google_id' => 'google-sub-1',
    ]);

    $this->postJson('/api/auth/password-code', ['email' => $user->email])->assertOk();

    $code = null;
    Notification::assertSentTo($user, PasswordResetCode::class, function (PasswordResetCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->travel(11)->minutes();

    $this->postJson('/api/auth/password-code/verify', [
        'email' => $user->email,
        'code' => $code,
    ])->assertStatus(422)->assertJson(['message' => 'That code is invalid or has expired. Request a new one.']);
});

test('password codes are only sent for active google accounts', function () {
    Notification::fake();

    User::factory()->create([
        'email' => 'password@example.com',
    ]);

    User::factory()->create([
        'email' => 'disabled@example.com',
        'google_id' => 'google-sub-2',
        'status' => 'disabled',
    ]);

    $this->postJson('/api/auth/password-code', ['email' => 'missing@example.com'])
        ->assertNotFound()
        ->assertJson(['message' => 'No account uses that email.']);

    $this->postJson('/api/auth/password-code', ['email' => 'password@example.com'])
        ->assertStatus(422)
        ->assertJson(['message' => 'This account uses a password. Reset it with your old password.']);

    $this->postJson('/api/auth/password-code', ['email' => 'disabled@example.com'])
        ->assertForbidden()
        ->assertJson(['message' => 'Your account has been disabled. Please contact the admin.']);

    Notification::assertNothingSent();
});
