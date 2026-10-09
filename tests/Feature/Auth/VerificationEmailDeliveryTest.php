<?php

use App\Models\User;
use App\Notifications\VerifyEmailAddress;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('registering queues a branded verification notification and leaves the user unverified', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'first_name' => 'Ada',
        'last_name' => 'Customer',
        'phone' => '09171234567',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'ada@example.com')->firstOrFail();

    expect($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmailAddress::class);
    $response->assertRedirect(route('verification.notice', absolute: false));
    $response->assertSessionHas('status', 'verification-link-sent');
});

test('the verification notice shows the users email address', function () {
    $user = User::factory()->unverified()->create(['email' => 'ada@example.com']);

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('ada@example.com')
        ->assertSee('Resend Verification Email');
});

test('the verification email contains a signed url and the rental branding', function () {
    config(['app.name' => 'Brilliant Gem Car Rental']);

    $user = User::factory()->unverified()->create(['first_name' => 'Ada', 'last_name' => 'Customer']);

    $mail = (new VerifyEmailAddress)->toMail($user);

    expect($mail->subject)->toBe('Verify Your Email Address')
        ->and($mail->greeting)->toBe('Hello Ada Customer,')
        ->and($mail->actionText)->toBe('Verify Email')
        ->and($mail->actionUrl)->toContain('signature=')
        ->and($mail->actionUrl)->toContain('expires=')
        ->and($mail->actionUrl)->toContain('/verify-email/'.$user->getKey())
        ->and($mail->introLines)->toContain('Thank you for creating an account with Brilliant Gem Car Rental.')
        ->and($mail->salutation)->toContain('Brilliant Gem Car Rental');
});

test('an authenticated unverified user can resend the verification email', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentTo($user, VerifyEmailAddress::class);
});

test('a guest cannot request a verification email', function () {
    Notification::fake();

    $this->post(route('verification.send'))->assertRedirect(route('login'));

    Notification::assertNothingSent();
});

test('an already verified user does not receive another verification email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHas('status', 'already-verified');

    Notification::assertNothingSent();
});

test('resending is rate limited after six attempts', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    foreach (range(1, 6) as $ignored) {
        $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
    }

    $this->actingAs($user)->post(route('verification.send'))->assertStatus(429);
});

test('a mail transport failure is logged and surfaced as a friendly message', function () {
    $user = User::factory()->unverified()->create();

    Mail::shouldReceive('mailer')->andThrow(new RuntimeException('smtp is down'));

    $this->actingAs($user)
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('status', 'verification-link-failed');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a tampered verification link is rejected with an invalid message', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->getKey(),
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)
        ->get($url.'-tampered')
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('status', 'verification-link-invalid');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('an expired verification link is rejected with an expired message', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
        'id' => $user->getKey(),
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)
        ->get($url)
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('status', 'verification-link-expired');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a user cannot verify another users account', function () {
    $user = User::factory()->unverified()->create();
    $victim = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $victim->getKey(),
        'hash' => sha1($victim->email),
    ]);

    $this->actingAs($user)->get($url);

    expect($victim->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('an unverified user is blocked from the dashboard and shown the notice', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

test('a verified user reaches the dashboard and sees the success banner', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard').'?verified=1')
        ->assertOk()
        ->assertSee('Your email has been successfully verified.');
});
