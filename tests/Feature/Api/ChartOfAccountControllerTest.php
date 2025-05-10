<?php

namespace Tests\Feature\Api;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChartOfAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function createChartOfAccount(array $attributes = []): ChartOfAccount
    {
        return ChartOfAccount::factory()->for($this->user)->create($attributes);
    }

    public function test_guest_cannot_access_chart_of_accounts_index()
    {
        $response = $this->getJson('/api/v1/chart-of-accounts');
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_view_their_chart_of_accounts_index()
    {
        Sanctum::actingAs($this->user);
        $this->createChartOfAccount(['name' => 'Account A', 'account_code' => '1000']);
        $this->createChartOfAccount(['name' => 'Account B', 'account_code' => '2000']);

        // Create an account for another user that should not be visible
        $otherUser = User::factory()->create();
        ChartOfAccount::factory()->for($otherUser)->create(['name' => 'Other User Account']);

        $response = $this->getJson('/api/v1/chart-of-accounts');

        $response->assertOk()
            ->assertJsonCount(2, 'data') // Only user's accounts
            ->assertJsonPath('data.0.name', 'Account A')
            ->assertJsonPath('data.1.name', 'Account B')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'account_code',
                        'name',
                        'type',
                        'description',
                        'parent_id',
                        'is_active',
                        'allow_direct_posting',
                        'system_account_tag',
                        'created_at',
                        'updated_at',
                        // 'parent', // These are conditionally loaded, so might not be present by default
                        // 'children',
                    ],
                ],
                'links' => [
                    'first', 'last', 'prev', 'next',
                ],
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
                ],
            ]);
    }

    public function test_chart_of_accounts_index_is_paginated()
    {
        Sanctum::actingAs($this->user);
        ChartOfAccount::factory()->count(20)->for($this->user)->create();

        $response = $this->getJson('/api/v1/chart-of-accounts?page=2');

        $response->assertOk()
            ->assertJsonCount(config('app.pagination_size', 15) > 20 - config('app.pagination_size', 15) ? 20 - config('app.pagination_size', 15) : config('app.pagination_size', 15), 'data') // Adjust count for page 2
            ->assertJsonPath('meta.current_page', 2);
    }

    // Store Tests
    public function test_guest_cannot_store_chart_of_account()
    {
        $accountData = [
            'account_code' => '3000',
            'name' => 'New Revenue Account',
            'type' => 'revenue',
        ];
        $response = $this->postJson('/api/v1/chart-of-accounts', $accountData);
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_store_valid_chart_of_account_with_defaults()
    {
        Sanctum::actingAs($this->user);
        $accountData = [
            'account_code' => '3001',
            'name' => 'Sales Revenue',
            'type' => 'revenue',
            'description' => 'Revenue from sales',
        ];

        $response = $this->postJson('/api/v1/chart-of-accounts', $accountData);

        $response->assertStatus(201)
            ->assertJsonPath('data.account_code', '3001')
            ->assertJsonPath('data.name', 'Sales Revenue')
            ->assertJsonPath('data.type', 'revenue')
            ->assertJsonPath('data.description', 'Revenue from sales')
            ->assertJsonPath('data.is_active', true) // Default
            ->assertJsonPath('data.allow_direct_posting', true); // Default

        $this->assertDatabaseHas('chart_of_accounts', [
            'user_id' => $this->user->id,
            'account_code' => '3001',
            'name' => 'Sales Revenue',
            'is_active' => true,
            'allow_direct_posting' => true,
        ]);
    }

    public function test_authenticated_user_can_store_valid_chart_of_account_with_specific_booleans()
    {
        Sanctum::actingAs($this->user);
        $accountData = [
            'account_code' => '3002',
            'name' => 'Consulting Revenue',
            'type' => 'revenue',
            'is_active' => false,
            'allow_direct_posting' => false,
        ];

        $response = $this->postJson('/api/v1/chart-of-accounts', $accountData);

        $response->assertStatus(201)
            ->assertJsonPath('data.account_code', '3002')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.allow_direct_posting', false);

        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => '3002',
            'is_active' => false,
            'allow_direct_posting' => false,
        ]);
    }

    public function test_store_chart_of_account_fails_with_missing_required_fields()
    {
        Sanctum::actingAs($this->user);
        $response = $this->postJson('/api/v1/chart-of-accounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_code', 'name', 'type']);
    }

    public function test_store_chart_of_account_fails_with_non_unique_account_code_for_same_user()
    {
        Sanctum::actingAs($this->user);
        $this->createChartOfAccount(['account_code' => '3003']);

        $accountData = [
            'account_code' => '3003', // Duplicate for this user
            'name' => 'Another Account',
            'type' => 'expense',
        ];
        $response = $this->postJson('/api/v1/chart-of-accounts', $accountData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_code']);
    }

    public function test_store_chart_of_account_succeeds_with_same_account_code_for_different_user()
    {
        Sanctum::actingAs($this->user);
        $otherUser = User::factory()->create();
        ChartOfAccount::factory()->for($otherUser)->create(['account_code' => '3004']);

        $accountData = [
            'account_code' => '3004', // Same code, but for $this->user
            'name' => 'My Expense Account',
            'type' => 'expense',
        ];
        $response = $this->postJson('/api/v1/chart-of-accounts', $accountData);
        $response->assertStatus(201);
        $this->assertDatabaseHas('chart_of_accounts', ['user_id' => $this->user->id, 'account_code' => '3004']);
    }

    public function test_store_chart_of_account_with_parent_id()
    {
        Sanctum::actingAs($this->user);
        $parentAccount = $this->createChartOfAccount(['account_code' => '4000', 'name' => 'Parent Asset']);

        $childAccountData = [
            'account_code' => '4001',
            'name' => 'Child Asset',
            'type' => $parentAccount->type, // Typically child has same type or a compatible one
            'parent_id' => $parentAccount->id,
        ];

        $response = $this->postJson('/api/v1/chart-of-accounts', $childAccountData);

        $response->assertStatus(201)
            ->assertJsonPath('data.parent_id', $parentAccount->id);

        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => '4001',
            'parent_id' => $parentAccount->id,
        ]);
    }

    public function test_store_chart_of_account_fails_with_non_existent_parent_id()
    {
        Sanctum::actingAs($this->user);
        $childAccountData = [
            'account_code' => '4002',
            'name' => 'Child Asset Non Existent Parent',
            'type' => 'asset',
            'parent_id' => 999, // Non-existent parent
        ];

        $response = $this->postJson('/api/v1/chart-of-accounts', $childAccountData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_store_chart_of_account_fails_with_parent_id_belonging_to_another_user()
    {
        Sanctum::actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherUserParentAccount = ChartOfAccount::factory()->for($otherUser)->create();

        $childAccountData = [
            'account_code' => '4003',
            'name' => 'Child Asset Other User Parent',
            'type' => 'asset',
            'parent_id' => $otherUserParentAccount->id,
        ];

        $response = $this->postJson('/api/v1/chart-of-accounts', $childAccountData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    // Show Tests
    public function test_guest_cannot_access_chart_of_account_show()
    {
        $account = ChartOfAccount::factory()->create(); // Create a dummy account
        $response = $this->getJson("/api/v1/chart-of-accounts/{$account->id}");
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_view_their_own_chart_of_account()
    {
        Sanctum::actingAs($this->user);
        $account = $this->createChartOfAccount(['name' => 'My Test Account']);

        $response = $this->getJson("/api/v1/chart-of-accounts/{$account->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $account->id)
            ->assertJsonPath('data.name', 'My Test Account')
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'account_code',
                    'name',
                    'type',
                    'description',
                    'parent_id',
                    'is_active',
                    'allow_direct_posting',
                    'system_account_tag',
                    'created_at',
                    'updated_at',
                    // 'parent', // Not explicitly loaded in controller's show method
                    // 'children',
                ],
            ]);
    }

    public function test_authenticated_user_cannot_view_others_chart_of_account()
    {
        Sanctum::actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherUserAccount = ChartOfAccount::factory()->for($otherUser)->create();

        $response = $this->getJson("/api/v1/chart-of-accounts/{$otherUserAccount->id}");

        $response->assertStatus(403); // Controller has explicit check
    }

    public function test_view_non_existent_chart_of_account_returns_404()
    {
        Sanctum::actingAs($this->user);
        $response = $this->getJson('/api/v1/chart-of-accounts/99999'); // Non-existent ID
        $response->assertNotFound();
    }

    // Update Tests
    public function test_guest_cannot_update_chart_of_account()
    {
        $account = ChartOfAccount::factory()->create(); // Dummy account
        $updateData = ['name' => 'Updated Name'];
        $response = $this->putJson("/api/v1/chart-of-accounts/{$account->id}", $updateData);
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_update_their_own_chart_of_account()
    {
        Sanctum::actingAs($this->user);
        $account = $this->createChartOfAccount(['account_code' => '5000', 'name' => 'Original Name']);
        $updateData = [
            'account_code' => '5001',
            'name' => 'Updated Account Name',
            'type' => 'liability',
            'description' => 'Updated description',
            'is_active' => false,
            'allow_direct_posting' => false,
        ];

        $response = $this->putJson("/api/v1/chart-of-accounts/{$account->id}", $updateData);

        $response->assertOk()
            ->assertJsonPath('data.account_code', '5001')
            ->assertJsonPath('data.name', 'Updated Account Name')
            ->assertJsonPath('data.type', 'liability')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.allow_direct_posting', false);

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'user_id' => $this->user->id,
            'account_code' => '5001',
            'name' => 'Updated Account Name',
            'type' => 'liability',
            'is_active' => false,
            'allow_direct_posting' => false,
        ]);
    }

    public function test_authenticated_user_cannot_update_others_chart_of_account()
    {
        Sanctum::actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherUserAccount = ChartOfAccount::factory()->for($otherUser)->create();
        $updateData = ['name' => 'Attempted Update'];

        $response = $this->putJson("/api/v1/chart-of-accounts/{$otherUserAccount->id}", $updateData);
        $response->assertStatus(403);
    }

    public function test_update_non_existent_chart_of_account_returns_404()
    {
        Sanctum::actingAs($this->user);
        $updateData = ['name' => 'Trying to update ghost'];
        $response = $this->putJson('/api/v1/chart-of-accounts/99999', $updateData);
        $response->assertNotFound();
    }

    public function test_update_chart_of_account_fails_with_missing_required_fields()
    {
        Sanctum::actingAs($this->user);
        $account = $this->createChartOfAccount();
        $response = $this->putJson("/api/v1/chart-of-accounts/{$account->id}", [
            'account_code' => '',
            'name' => '',
            'type' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_code', 'name', 'type']);
    }

    public function test_update_chart_of_account_fails_with_non_unique_account_code_for_same_user()
    {
        Sanctum::actingAs($this->user);
        $accountToUpdate = $this->createChartOfAccount(['account_code' => '5002']);
        $existingAccount = $this->createChartOfAccount(['account_code' => '5003']); // Another account with a different code

        $updateData = ['account_code' => '5003']; // Try to change to existingAccount's code
        $response = $this->putJson("/api/v1/chart-of-accounts/{$accountToUpdate->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_code']);
    }

    public function test_update_chart_of_account_succeeds_with_its_own_account_code()
    {
        Sanctum::actingAs($this->user);
        $account = $this->createChartOfAccount(['account_code' => '5004', 'name' => 'Original Name']);
        $updateData = [
            'account_code' => '5004', // Same account code
            'name' => 'Name Updated Slightly',
            'type' => $account->type,
        ];

        $response = $this->putJson("/api/v1/chart-of-accounts/{$account->id}", $updateData);
        $response->assertOk()
            ->assertJsonPath('data.name', 'Name Updated Slightly');
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id, 'name' => 'Name Updated Slightly']);
    }

    public function test_update_chart_of_account_succeeds_with_same_account_code_as_different_user()
    {
        Sanctum::actingAs($this->user);
        $accountToUpdate = $this->createChartOfAccount(['account_code' => '5005']);

        $otherUser = User::factory()->create();
        ChartOfAccount::factory()->for($otherUser)->create(['account_code' => '5006']); // This other user has '5006'

        $updateData = [
            'account_code' => '5006', // Trying to update current user's account to '5006'
            'name' => $accountToUpdate->name,
            'type' => $accountToUpdate->type,
        ];

        $response = $this->putJson("/api/v1/chart-of-accounts/{$accountToUpdate->id}", $updateData);
        $response->assertOk(); // This should be fine as uniqueness is per user
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $accountToUpdate->id, 'account_code' => '5006']);
    }

    public function test_update_chart_of_account_with_parent_id()
    {
        Sanctum::actingAs($this->user);
        $parentAccount = $this->createChartOfAccount(['account_code' => '6000']);
        $accountToUpdate = $this->createChartOfAccount(['account_code' => '6001']);

        $updateData = ['parent_id' => $parentAccount->id];
        $response = $this->putJson("/api/v1/chart-of-accounts/{$accountToUpdate->id}", $updateData);

        $response->assertOk()
            ->assertJsonPath('data.parent_id', $parentAccount->id);
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $accountToUpdate->id, 'parent_id' => $parentAccount->id]);
    }

    public function test_update_chart_of_account_fails_with_non_existent_parent_id()
    {
        Sanctum::actingAs($this->user);
        $accountToUpdate = $this->createChartOfAccount();
        $updateData = ['parent_id' => 9999]; // Non-existent parent

        $response = $this->putJson("/api/v1/chart-of-accounts/{$accountToUpdate->id}", $updateData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_update_chart_of_account_fails_with_parent_id_belonging_to_another_user()
    {
        Sanctum::actingAs($this->user);
        $accountToUpdate = $this->createChartOfAccount();
        $otherUser = User::factory()->create();
        $otherUserParent = ChartOfAccount::factory()->for($otherUser)->create();

        $updateData = ['parent_id' => $otherUserParent->id];
        $response = $this->putJson("/api/v1/chart-of-accounts/{$accountToUpdate->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_update_chart_of_account_fails_if_parent_id_is_itself()
    {
        Sanctum::actingAs($this->user);
        $account = $this->createChartOfAccount();
        $updateData = ['parent_id' => $account->id];

        $response = $this->putJson("/api/v1/chart-of-accounts/{$account->id}", $updateData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']); // parent_id cannot be self
    }

    // Destroy Tests
    public function test_guest_cannot_destroy_chart_of_account()
    {
        $account = ChartOfAccount::factory()->create(); // Dummy account
        $response = $this->deleteJson("/api/v1/chart-of-accounts/{$account->id}");
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_destroy_their_own_chart_of_account_without_children()
    {
        Sanctum::actingAs($this->user);
        $account = $this->createChartOfAccount();

        $response = $this->deleteJson("/api/v1/chart-of-accounts/{$account->id}");

        $response->assertStatus(204); // No content
        // Assuming ChartOfAccount uses SoftDeletes, otherwise use assertDatabaseMissing
        $this->assertSoftDeleted('chart_of_accounts', ['id' => $account->id]);
    }

    public function test_authenticated_user_cannot_destroy_others_chart_of_account()
    {
        Sanctum::actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherUserAccount = ChartOfAccount::factory()->for($otherUser)->create();

        $response = $this->deleteJson("/api/v1/chart-of-accounts/{$otherUserAccount->id}");
        $response->assertStatus(403);
    }

    public function test_destroy_non_existent_chart_of_account_returns_404()
    {
        Sanctum::actingAs($this->user);
        $response = $this->deleteJson('/api/v1/chart-of-accounts/99999');
        $response->assertNotFound();
    }

    public function test_authenticated_user_cannot_destroy_account_with_child_accounts()
    {
        Sanctum::actingAs($this->user);
        $parentAccount = $this->createChartOfAccount(['account_code' => '7000', 'name' => 'Parent With Child']);
        $childAccount = $this->createChartOfAccount(['account_code' => '7001', 'name' => 'Child Account', 'parent_id' => $parentAccount->id]);

        $response = $this->deleteJson("/api/v1/chart-of-accounts/{$parentAccount->id}");

        $response->assertStatus(422) // Or 409 Conflict, depending on controller implementation
            ->assertJsonPath('error', 'Cannot delete account with child accounts.');

        $this->assertDatabaseHas('chart_of_accounts', ['id' => $parentAccount->id]); // Still exists
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $childAccount->id]); // Child also still exists
    }
}
