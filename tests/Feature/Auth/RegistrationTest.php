<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '09171234567',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    expect(auth()->user())
        ->role->toBe('customer')
        ->name->toBe('Test User')
        ->code->toStartWith('usr_');
    $response->assertRedirect(route('verification.notice', absolute: false));
});
