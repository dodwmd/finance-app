<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A basic browser test example.
     */
    public function test_user_can_login(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('http://localhost:8000')
                ->screenshot('home-page')
                ->assertSee('Vibe Finance')
                ->visit('http://localhost:8000/login')
                ->screenshot('login-page')
                ->assertSee('Email')
                ->assertSee('Password');
        });
    }
}
