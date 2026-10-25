<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_directed_to_sign_in(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        foreach (['/dashboard', '/yarns', '/companies', '/knitting-jobs', '/dyeing-jobs', '/settings'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        $this->get('/api/lots/preview')->assertUnauthorized();
    }
}
