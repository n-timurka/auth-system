<?php

namespace Tests\Feature;

use Tests\TestCase;

class CsrfTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_csrf_cookie_is_set_on_request(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Laravel sets XSRF-TOKEN cookie by default for JS apps
        $response->assertCookie('XSRF-TOKEN');
    }
}
