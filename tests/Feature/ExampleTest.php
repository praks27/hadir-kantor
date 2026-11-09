<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_visiting_the_root_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
        $this->get(route('login'))->assertOk();
    }

    public function test_login_notification_response_does_not_expose_request_ids(): void
    {
        $this->getJson(route('login.leave-notifications'))
            ->assertOk()
            ->assertExactJson(['has_pending' => false]);
    }
}
