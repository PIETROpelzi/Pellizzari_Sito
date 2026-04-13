<?php

test('guest users are redirected to login from dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
