<?php

use App\Models\User;

test('phone-available rejects an invalid number', function () {
    $response = $this->postJson('/api/users/phone-available', [
        'phone' => '12345',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('phone');
});

test('phone-available reports a free +63 number', function () {
    $response = $this->postJson('/api/users/phone-available', [
        'phone' => '+639171234567',
    ]);

    $response->assertOk()->assertJson(['taken' => false]);
});

test('phone-available treats 09 and +63 as the same number', function () {
    User::factory()->create(['phone' => '+639171234567']);

    $this->postJson('/api/users/phone-available', [
        'phone' => '09171234567',
    ])->assertOk()->assertJson(['taken' => true]);

    $this->postJson('/api/users/phone-available', [
        'phone' => '+639171234567',
    ])->assertOk()->assertJson(['taken' => true]);
});
