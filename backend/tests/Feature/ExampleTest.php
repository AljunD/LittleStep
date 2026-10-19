<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_site_root_sends_guests_to_the_sign_in_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
