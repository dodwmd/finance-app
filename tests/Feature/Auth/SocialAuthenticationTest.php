<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Make sure routes are loaded for testing
        Route::get('/dashboard', function () {
            return 'dashboard';
        })->middleware(['auth'])->name('dashboard');
    }

    /**
     * Test that the login page shows social login buttons.
     */
    public function test_login_page_shows_social_login_buttons(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Or continue with');
        $response->assertSee(route('auth.social.redirect', ['provider' => 'github']));
        $response->assertSee(route('auth.social.redirect', ['provider' => 'google']));
    }

    /**
     * Test that the redirect to provider works.
     */
    public function test_redirect_to_provider(): void
    {
        $response = $this->get(route('auth.social.redirect', ['provider' => 'github']));
        $response->assertRedirect();
        
        $response = $this->get(route('auth.social.redirect', ['provider' => 'google']));
        $response->assertRedirect();
    }

    /**
     * Test that an invalid provider returns an error.
     */
    public function test_invalid_provider_returns_error(): void
    {
        $response = $this->get(route('auth.social.redirect', ['provider' => 'invalid']));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Invalid social provider');
    }

    /**
     * Test a new user can be created from social auth.
     */
    public function test_user_can_be_created_from_social_auth(): void
    {
        // Create a mock user object to return from Socialite
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive([
            'getId' => '123456789',
            'getName' => 'Test User',
            'getEmail' => 'test@example.com',
            'getAvatar' => 'https://example.com/avatar.jpg',
        ]);

        // Mock the Socialite facade
        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // Test GitHub provider
        $response = $this->get(route('auth.social.callback', ['provider' => 'github']));
        $response->assertRedirect('/dashboard');
        
        // Assert user was created with correct data
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'provider' => 'github',
            'provider_id' => '123456789',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
        
        // Assert user is logged in
        $this->assertAuthenticated();
    }

    /**
     * Test an existing user can login with social auth.
     */
    public function test_existing_user_can_login_with_social_auth(): void
    {
        // Create a user that already has social auth info
        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'provider' => 'google',
            'provider_id' => '987654321',
            'avatar' => 'https://example.com/existing-avatar.jpg',
        ]);

        // Create a mock user object to return from Socialite
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive([
            'getId' => '987654321',
            'getEmail' => 'existing@example.com',
        ]);

        // Mock the Socialite facade
        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // Test login with existing user
        $response = $this->get(route('auth.social.callback', ['provider' => 'google']));
        $response->assertRedirect('/dashboard');
        
        // Assert user is logged in
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test an existing user without social auth can connect their account.
     */
    public function test_existing_user_can_connect_social_account(): void
    {
        // Create a user without social auth info
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'provider' => null,
            'provider_id' => null,
        ]);

        // Create a mock user object to return from Socialite
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive([
            'getId' => '112233445566',
            'getEmail' => 'regular@example.com',
            'getAvatar' => 'https://example.com/new-avatar.jpg',
        ]);

        // Mock the Socialite facade
        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // Test connecting social account to existing user
        $response = $this->get(route('auth.social.callback', ['provider' => 'github']));
        $response->assertRedirect('/dashboard');
        
        // Assert user information was updated
        $this->assertDatabaseHas('users', [
            'email' => 'regular@example.com',
            'provider' => 'github',
            'provider_id' => '112233445566',
            'avatar' => 'https://example.com/new-avatar.jpg',
        ]);
        
        // Assert user is logged in
        $this->assertAuthenticatedAs($user->fresh());
    }

    /**
     * Test a user with existing social auth can't connect a different provider with same email.
     */
    public function test_user_with_different_provider_gets_error(): void
    {
        // Create a user with social auth info
        User::factory()->create([
            'name' => 'Social User',
            'email' => 'social@example.com',
            'provider' => 'github',
            'provider_id' => '11223344',
        ]);

        // Create a mock user object to return from Socialite (different provider, same email)
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive([
            'getId' => '99887766',
            'getEmail' => 'social@example.com',
        ]);

        // Mock the Socialite facade
        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // Test attempting to login with different provider but same email
        $response = $this->get(route('auth.social.callback', ['provider' => 'google']));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'An account with this email already exists. Please log in using your original method.');
        
        // Assert user is not logged in
        $this->assertGuest();
    }
}
