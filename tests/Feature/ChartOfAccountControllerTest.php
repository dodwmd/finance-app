<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChartOfAccountControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_chart_of_accounts_index(): void
    {
        $response = $this->get(route('chart-of-accounts.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_chart_of_accounts_index_with_their_accounts(): void
    {
        $this->actingAs($this->user);

        // Accounts for the authenticated user
        $userAccount1 = ChartOfAccount::factory()->for($this->user)->create(['account_code' => '1000', 'name' => 'User Bank Account']);
        $userAccount2 = ChartOfAccount::factory()->for($this->user)->create(['account_code' => '2000', 'name' => 'User Credit Card']);

        // Account for another user
        $otherUser = User::factory()->create();
        $otherUserAccount = ChartOfAccount::factory()->for($otherUser)->create(['account_code' => '3000', 'name' => 'Other User Account']);

        $response = $this->get(route('chart-of-accounts.index'));

        $response->assertOk(); // Alias for assertStatus(200)
        $response->assertViewIs('chart-of-accounts.index');
        $response->assertViewHas('accounts');

        // Check that user's accounts are visible
        $response->assertSeeText($userAccount1->name);
        $response->assertSeeText($userAccount1->account_code);
        $response->assertSeeText($userAccount2->name);
        $response->assertSeeText($userAccount2->account_code);

        // Check that other user's account is not visible
        $response->assertDontSeeText($otherUserAccount->name);
        $response->assertDontSeeText($otherUserAccount->account_code);
    }

    public function test_chart_of_accounts_index_is_paginated(): void
    {
        $this->actingAs($this->user);

        // Create more accounts than the pagination limit (15 by default in controller)
        ChartOfAccount::factory()->count(20)->for($this->user)->create();

        $response = $this->get(route('chart-of-accounts.index'));

        $response->assertOk();
        $response->assertViewHas('accounts');

        // Check if the 'accounts' variable in the view is an instance of Paginator
        $accountsInView = $response->viewData('accounts');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $accountsInView);
        $this->assertCount(15, $accountsInView->items()); // Default page size
    }

    // ### CREATE TESTS ###

    public function test_guest_cannot_access_chart_of_accounts_create_page(): void
    {
        $response = $this->get(route('chart-of-accounts.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_chart_of_accounts_create_page(): void
    {
        $this->actingAs($this->user);
        ChartOfAccount::factory()->count(3)->for($this->user)->create(); // Some parent accounts

        $response = $this->get(route('chart-of-accounts.create'));

        $response->assertOk();
        $response->assertViewIs('chart-of-accounts.create');
        $response->assertViewHas('parentAccounts');
        $response->assertViewHas('accountTypes');

        // Check if some parent accounts are available in the view data
        $parentAccountsInView = $response->viewData('parentAccounts');
        $this->assertNotEmpty($parentAccountsInView);
        $this->assertCount(3, $parentAccountsInView);
    }

    // ### STORE TESTS ###

    public function test_guest_cannot_store_chart_of_account(): void
    {
        $accountData = ChartOfAccount::factory()->make()->toArray(); // Make, not create
        $response = $this->post(route('chart-of-accounts.store'), $accountData);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_store_valid_chart_of_account(): void
    {
        $this->actingAs($this->user);

        $accountData = [
            'account_code' => 'ASSET-001',
            'name' => 'Test Bank Account',
            'type' => 'Asset',
            'description' => 'A test description for the bank account.',
            'parent_id' => null,
            'is_active' => true,
            'allow_direct_posting' => true,
            'system_account_tag' => null,
        ];

        $response = $this->post(route('chart-of-accounts.store'), $accountData);

        $response->assertRedirect(route('chart-of-accounts.index'));
        $response->assertSessionHas('success', 'Account created successfully.');

        $this->assertDatabaseHas('chart_of_accounts', [
            'user_id' => $this->user->id,
            'account_code' => 'ASSET-001',
            'name' => 'Test Bank Account',
            'type' => 'asset', // Expect lowercase
            'description' => 'A test description for the bank account.',
            'is_active' => 1, // Expect 1 for true
            'allow_direct_posting' => 1, // Expect 1 for true
        ]);
    }

    public function test_store_chart_of_account_fails_with_missing_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('chart-of-accounts.store'), []); // Empty data

        $response->assertSessionHasErrors(['account_code', 'name', 'type']);
        $this->assertDatabaseCount('chart_of_accounts', 0); // No account should be created
    }

    public function test_store_chart_of_account_fails_with_non_unique_account_code_for_same_user(): void
    {
        $this->actingAs($this->user);

        ChartOfAccount::factory()->for($this->user)->create(['account_code' => 'DUP-CODE']);

        $accountData = [
            'account_code' => 'DUP-CODE', // Duplicate code for this user
            'name' => 'Another Account',
            'type' => 'Liability',
            'is_active' => true,
            'allow_direct_posting' => true,
        ];

        $response = $this->post(route('chart-of-accounts.store'), $accountData);

        $response->assertSessionHasErrors(['account_code']);
        // Only the initially created account should exist
        $this->assertDatabaseCount('chart_of_accounts', 1);
    }

    public function test_store_chart_of_account_with_parent_id(): void
    {
        $this->actingAs($this->user);
        $parentAccount = ChartOfAccount::factory()->for($this->user)->create();

        $accountData = [
            'account_code' => 'CHILD-001',
            'name' => 'Child Account',
            'type' => 'Asset',
            'parent_id' => $parentAccount->id,
            'is_active' => true,
            'allow_direct_posting' => true,
        ];

        $this->post(route('chart-of-accounts.store'), $accountData);

        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => 'CHILD-001',
            'parent_id' => $parentAccount->id,
        ]);
    }

    public function test_store_chart_of_account_with_optional_fields_null_and_defaults(): void
    {
        $this->actingAs($this->user);

        $accountData = [
            'account_code' => 'OPT-001',
            'name' => 'Optional Fields Test',
            'type' => 'Expense',
            // description, parent_id, system_account_tag are omitted
            // is_active and allow_direct_posting are omitted (should default to true)
        ];

        $this->post(route('chart-of-accounts.store'), $accountData);

        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => 'OPT-001',
            'name' => 'Optional Fields Test',
            'type' => 'expense', // Expect lowercase
            'description' => null,
            'parent_id' => null,
            'system_account_tag' => null,
            'is_active' => 1, // Expect 1 for true (default)
            'allow_direct_posting' => 1, // Expect 1 for true (default)
        ]);
    }

    public function test_store_chart_of_account_with_specific_is_active_and_allow_direct_posting_values(): void
    {
        $this->actingAs($this->user);

        $accountData = [
            'account_code' => 'BOOL-001',
            'name' => 'Boolean Fields Test',
            'type' => 'Revenue',
            'is_active' => false,
            'allow_direct_posting' => false,
        ];

        $this->post(route('chart-of-accounts.store'), $accountData);

        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => 'BOOL-001',
            'is_active' => 0, // Expect 0 for false
            'allow_direct_posting' => 0, // Expect 0 for false
        ]);
    }

    // ### SHOW TESTS ###

    public function test_guest_cannot_access_chart_of_account_show_page(): void
    {
        $account = ChartOfAccount::factory()->create(); // Needs an ID for the route
        $response = $this->get(route('chart-of-accounts.show', $account));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_own_chart_of_account(): void
    {
        $this->actingAs($this->user);
        $account = ChartOfAccount::factory()->for($this->user)->create();

        $response = $this->get(route('chart-of-accounts.show', $account));

        $response->assertOk();
        $response->assertViewIs('chart-of-accounts.show');
        $response->assertViewHas('chartOfAccount', function ($viewAccount) use ($account) {
            return $viewAccount->id === $account->id;
        });
        $response->assertSeeText($account->name);
    }

    public function test_authenticated_user_cannot_view_others_chart_of_account(): void
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherAccount = ChartOfAccount::factory()->for($otherUser)->create();

        $response = $this->get(route('chart-of-accounts.show', $otherAccount));
        $response->assertForbidden(); // Expecting 403
    }

    // ### EDIT TESTS ###

    public function test_guest_cannot_access_chart_of_accounts_edit_page(): void
    {
        $account = ChartOfAccount::factory()->create();
        $response = $this->get(route('chart-of-accounts.edit', $account));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_edit_page_for_their_own_account(): void
    {
        $this->actingAs($this->user);
        $account = ChartOfAccount::factory()->for($this->user)->create();
        // Create some other accounts for the parent dropdown
        ChartOfAccount::factory()->count(2)->for($this->user)->create();

        $response = $this->get(route('chart-of-accounts.edit', $account));

        $response->assertOk();
        $response->assertViewIs('chart-of-accounts.edit');
        $response->assertViewHas('chartOfAccount', function ($viewAccount) use ($account) {
            return $viewAccount->id === $account->id;
        });
        $response->assertViewHas('parentAccounts');
        $response->assertViewHas('accountTypes');

        // Ensure the current account is not in the parentAccounts list
        $parentAccountsInView = $response->viewData('parentAccounts');
        $this->assertNotContains($account->id, $parentAccountsInView->pluck('id')->toArray());
    }

    public function test_authenticated_user_cannot_access_edit_page_for_others_account(): void
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherAccount = ChartOfAccount::factory()->for($otherUser)->create();

        $response = $this->get(route('chart-of-accounts.edit', $otherAccount));
        $response->assertForbidden();
    }

    // ### UPDATE TESTS ###

    public function test_guest_cannot_update_chart_of_account(): void
    {
        $account = ChartOfAccount::factory()->create();
        $updatedData = ['name' => 'Updated Name'];
        $response = $this->put(route('chart-of-accounts.update', $account), $updatedData);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_their_own_chart_of_account(): void
    {
        $this->actingAs($this->user);
        $account = ChartOfAccount::factory()->for($this->user)->create([
            'name' => 'Original Name',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $updatedData = [
            'account_code' => $account->account_code, // Assuming code doesn't change or unique rule is handled
            'name' => 'Updated Account Name',
            'type' => 'liability', // Changed type
            'description' => 'Updated description.',
            'parent_id' => null,
            'is_active' => false, // Changed active state
            'allow_direct_posting' => false,
            'system_account_tag' => 'BANK',
        ];

        $response = $this->put(route('chart-of-accounts.update', $account), $updatedData);

        $response->assertRedirect(route('chart-of-accounts.index'));
        $response->assertSessionHas('success', 'Account updated successfully.');

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'user_id' => $this->user->id,
            'name' => 'Updated Account Name',
            'type' => 'liability', // Expect lowercase
            'description' => 'Updated description.',
            'is_active' => 0, // Expect 0 for false
            'allow_direct_posting' => 0, // Expect 0 for false
            'system_account_tag' => 'BANK',
        ]);
    }

    public function test_authenticated_user_cannot_update_others_chart_of_account(): void
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherAccount = ChartOfAccount::factory()->for($otherUser)->create(['name' => 'Other User Original Name']);

        $updatedData = ['name' => 'Malicious Update Name', 'account_code' => $otherAccount->account_code, 'type' => $otherAccount->type, 'is_active' => true, 'allow_direct_posting' => true];

        $response = $this->put(route('chart-of-accounts.update', $otherAccount), $updatedData);

        // UpdateChartOfAccountRequest's authorize method should deny this
        $response->assertForbidden();
        $this->assertDatabaseHas('chart_of_accounts', ['name' => 'Other User Original Name']); // Name should not have changed
    }

    public function test_update_chart_of_account_fails_with_missing_required_fields(): void
    {
        $this->actingAs($this->user);
        $account = ChartOfAccount::factory()->create(['user_id' => $this->user->id]);

        // Send only is_active and allow_direct_posting. account_code, name, type are optional (sometimes).
        $updateData = ['is_active' => false, 'allow_direct_posting' => false];
        $response = $this->put(route('chart-of-accounts.update', $account), $updateData);

        $response->assertRedirect(route('chart-of-accounts.index'));
        $response->assertSessionHasNoErrors(['account_code', 'name', 'type']);
        $response->assertSessionHas('success', 'Account updated successfully.');

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'is_active' => 0, // Expect 0 for false
            'allow_direct_posting' => 0, // Expect 0 for false
        ]);
    }

    public function test_update_chart_of_account_fails_with_non_unique_account_code_for_same_user_excluding_self(): void
    {
        $this->actingAs($this->user);
        $existingAccount1 = ChartOfAccount::factory()->for($this->user)->create(['account_code' => 'EXISTING-CODE']);
        $accountToUpdate = ChartOfAccount::factory()->for($this->user)->create(['account_code' => 'OLD-CODE']);

        $updatedData = [
            'account_code' => 'EXISTING-CODE', // Trying to use existingAccount1's code
            'name' => $accountToUpdate->name,
            'type' => $accountToUpdate->type,
            'is_active' => $accountToUpdate->is_active,
            'allow_direct_posting' => $accountToUpdate->allow_direct_posting,
        ];

        $response = $this->put(route('chart-of-accounts.update', $accountToUpdate), $updatedData);

        $response->assertSessionHasErrors(['account_code']);
        $this->assertDatabaseHas('chart_of_accounts', ['account_code' => 'OLD-CODE']); // Ensure it wasn't updated
    }

    public function test_update_chart_of_account_with_same_account_code_is_allowed(): void
    {
        $this->actingAs($this->user);
        $account = ChartOfAccount::factory()->for($this->user)->create(['account_code' => 'SAME-CODE']);

        $updatedData = [
            'account_code' => 'SAME-CODE', // Keeping the same code
            'name' => 'Updated Name Same Code',
            'type' => $account->type,
            'is_active' => $account->is_active,
            'allow_direct_posting' => $account->allow_direct_posting,
        ];

        $response = $this->put(route('chart-of-accounts.update', $account), $updatedData);
        $response->assertRedirect(route('chart-of-accounts.index'));
        $response->assertSessionDoesntHaveErrors(['account_code']);
        $response->assertSessionHas('success', 'Account updated successfully.');
        $this->assertDatabaseHas('chart_of_accounts', ['name' => 'Updated Name Same Code']);
    }

    // ### DESTROY TESTS ###

    public function test_guest_cannot_destroy_chart_of_account(): void
    {
        $account = ChartOfAccount::factory()->create();
        $response = $this->delete(route('chart-of-accounts.destroy', $account));
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id]); // Should still exist
    }

    public function test_authenticated_user_can_destroy_their_own_account_without_children(): void
    {
        $this->actingAs($this->user);
        $account = ChartOfAccount::factory()->for($this->user)->create();

        $response = $this->delete(route('chart-of-accounts.destroy', $account));

        $response->assertRedirect(route('chart-of-accounts.index'));
        $response->assertSessionHas('success', 'Account deleted successfully.');
        $this->assertSoftDeleted($account);
    }

    public function test_authenticated_user_cannot_destroy_others_chart_of_account(): void
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $otherAccount = ChartOfAccount::factory()->for($otherUser)->create();

        $response = $this->delete(route('chart-of-accounts.destroy', $otherAccount));

        $response->assertForbidden();
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $otherAccount->id]); // Should still exist
    }

    public function test_authenticated_user_cannot_destroy_account_with_child_accounts(): void
    {
        $this->actingAs($this->user);
        $parentAccount = ChartOfAccount::factory()->for($this->user)->create();
        ChartOfAccount::factory()->for($this->user)->withParent($parentAccount)->create(); // Child account

        $response = $this->delete(route('chart-of-accounts.destroy', $parentAccount));

        $response->assertRedirect(route('chart-of-accounts.index'));
        $response->assertSessionHas('error', 'Cannot delete account: It has child accounts. Please reassign or delete them first.');
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $parentAccount->id]); // Parent should still exist
    }
}
