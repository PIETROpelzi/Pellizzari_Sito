<?php

use App\Models\User;

test('authenticated user is redirected to dashboard from root', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->get('/');

    $response->assertRedirect('/dashboard');
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@test.local',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});
