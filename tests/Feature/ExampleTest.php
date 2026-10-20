<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root URL redirects to login — assert the redirect instead of 200.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // The root route redirects unauthenticated visitors to /login
        $response->assertRedirect('/login');
    }
}
