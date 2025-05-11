<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BankAccountManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // --- Test CREATE (GET /bank-accounts/create) ---
    public function test_guest_cannot_access_bank_account_creation_page(): void
    {
        $response = $this->get(route('bank-accounts.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_bank_account_creation_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('bank-accounts.create'));
        $response->assertOk();
        $response->assertViewIs('bank-accounts.create');
    }

    // --- Test STORE (POST /bank-accounts) ---
    public function test_guest_cannot_create_bank_account(): void
    {
        $response = $this->post(route('bank-accounts.store'), [
            'account_name' => 'Test Bank Account Name',
            'type' => 'bank',
            'account_number' => '123456789',
            'bsb' => '012-345',
            'opening_balance' => 1000,
        ]);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_a_bank_account_with_valid_data(): void
    {
        $accountData = [
            'account_name' => $this->faker->company.' Checking', // User-facing account name
            'type' => 'bank',
            'account_number' => $this->faker->numerify('#########'),
            'bsb' => $this->faker->numerify('######'), // Ensure 6 digits
            'opening_balance' => $this->faker->randomFloat(2, 100, 5000),
            // Add other new fields if they are part of 'valid data' and not optional
            'bank_name' => $this->faker->company,
            'account_type' => 'chequing',
            'currency' => 'CAD',
        ];

        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), $accountData);

        $response->assertRedirect(route('bank-accounts.index'));
        $response->assertSessionHas('success', 'Bank account created successfully.');

        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $this->user->id,
            'account_name' => $accountData['account_name'],
            'type' => $accountData['type'],
            'account_number' => $accountData['account_number'],
            'bsb' => preg_replace('/[^0-9]/', '', $accountData['bsb']), // Assert stored format
            'opening_balance' => $accountData['opening_balance'],
            'current_balance' => $accountData['opening_balance'], // Current balance should equal opening balance on creation
            'bank_name' => $accountData['bank_name'],
            'account_type' => $accountData['account_type'],
            'currency' => $accountData['currency'],
            'is_active' => true, // Assuming new accounts are active by default
        ]);
    }

    public function test_authenticated_user_can_create_a_bank_account_without_optional_fields(): void
    {
        $accountData = [
            'account_name' => 'My Cash Stash',
            'type' => 'cash',
            'opening_balance' => 50.00,
        ];

        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), $accountData);

        $response->assertRedirect(route('bank-accounts.index'));
        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $this->user->id,
            'account_name' => $accountData['account_name'],
            'type' => $accountData['type'],
            'account_number' => null,
            'bsb' => null,
            'opening_balance' => $accountData['opening_balance'],
            'current_balance' => $accountData['opening_balance'],
        ]);
    }

    public function test_bank_account_creation_fails_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), []);
        $response->assertSessionHasErrors(['account_name', 'type', 'opening_balance']);

        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), ['account_name' => 'Test Account']);
        $response->assertSessionHasErrors(['type', 'opening_balance']);

        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), ['account_name' => 'Test Account', 'type' => 'bank']);
        $response->assertSessionHasErrors(['opening_balance']);
    }

    public function test_bank_account_creation_fails_with_invalid_type(): void
    {
        $accountData = [
            'account_name' => 'Some Account Name',
            'type' => 'invalid_type',
            'opening_balance' => 100,
        ];
        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), $accountData);
        $response->assertSessionHasErrors('type');
    }

    public function test_bank_account_creation_fails_if_account_number_is_not_unique_for_user(): void
    {
        $existingAccount = BankAccount::factory()->for($this->user)->create(['account_number' => '123456789']);

        $accountData = [
            'account_name' => 'Main Checking Duplicate',
            'type' => 'bank',
            'account_number' => '123456789', // Same as existing
            'opening_balance' => 200,
        ];

        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), $accountData);
        $response->assertSessionHasErrors('account_number');

        // Test that it IS unique for a DIFFERENT user
        $otherUser = User::factory()->create();
        $responseOtherUser = $this->actingAs($otherUser)->post(route('bank-accounts.store'), $accountData);
        $responseOtherUser->assertSessionDoesntHaveErrors('account_number');
        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $otherUser->id,
            'account_name' => $accountData['account_name'],
            'account_number' => '123456789',
        ]);
    }

    public function test_opening_balance_must_be_numeric_and_non_negative(): void
    {
        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), [
            'account_name' => 'Bad Balance Account',
            'type' => 'cash',
            'opening_balance' => 'not-a-number',
        ]);
        $response->assertSessionHasErrors('opening_balance');

        $response = $this->actingAs($this->user)->post(route('bank-accounts.store'), [
            'account_name' => 'Negative Balance Account',
            'type' => 'cash',
            'opening_balance' => -100,
        ]);
        $response->assertSessionHasErrors('opening_balance');
    }

    // --- Test EDIT (GET /bank-accounts/{bank_account}/edit) ---
    public function test_guest_cannot_access_edit_bank_account_page(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $response = $this->get(route('bank-accounts.edit', $account));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_edit_page_for_their_own_bank_account(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $response = $this->actingAs($this->user)->get(route('bank-accounts.edit', $account));
        $response->assertOk();
        $response->assertViewIs('bank-accounts.edit');
        $response->assertViewHas('bankAccount', $account);
    }

    public function test_authenticated_user_cannot_access_edit_page_for_another_users_bank_account(): void
    {
        $otherUser = User::factory()->create();
        $otherUserAccount = BankAccount::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)->get(route('bank-accounts.edit', $otherUserAccount));
        $response->assertForbidden();
    }

    // --- Test UPDATE (PUT /bank-accounts/{bank_account}) ---
    public function test_guest_cannot_update_bank_account(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $updateData = ['account_name' => 'Updated Name'];
        $response = $this->put(route('bank-accounts.update', $account), $updateData);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_their_own_bank_account_with_valid_data(): void
    {
        $account = BankAccount::factory()->for($this->user)->create([
            'account_name' => 'Old Account Name',
            'type' => 'bank',
            'account_number' => '111222333',
            'bsb' => '111222', // Ensure 6 digits
            'opening_balance' => 100.00,
            'current_balance' => 100.00,
        ]);

        $updateData = [
            'account_name' => 'New Fancy Name',
            'type' => 'credit_card',
            'account_number' => '999888777',
            'bsb' => '999888', // Ensure 6 digits
        ];

        $response = $this->actingAs($this->user)->put(route('bank-accounts.update', $account), $updateData);

        $response->assertRedirect(route('bank-accounts.index'));
        $response->assertSessionHas('success', 'Bank account updated successfully.');

        $account->refresh();
        $this->assertEquals($updateData['account_name'], $account->account_name);
        $this->assertEquals($updateData['type'], $account->type);
        $this->assertEquals($updateData['account_number'], $account->account_number);
        $this->assertEquals(preg_replace('/[^0-9]/', '', $updateData['bsb']), $account->bsb); // Assert stored BSB format
        $this->assertEquals(100.00, $account->opening_balance); // Opening balance should not change
    }

    public function test_authenticated_user_cannot_update_another_users_bank_account(): void
    {
        $otherUser = User::factory()->create();
        $otherUserAccount = BankAccount::factory()->for($otherUser)->create(['account_name' => 'Other User Original Name']);
        $updateData = ['account_name' => 'Attempted Update Name'];

        $response = $this->actingAs($this->user)->put(route('bank-accounts.update', $otherUserAccount), $updateData);
        $response->assertForbidden();
        $this->assertDatabaseHas('bank_accounts', ['account_name' => 'Other User Original Name']);
    }

    public function test_bank_account_update_fails_with_missing_required_fields(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $response = $this->actingAs($this->user)->put(route('bank-accounts.update', $account), [
            'account_name' => '',
            'type' => '',
        ]);

        $response->assertSessionHasErrors(['account_name', 'type']);
    }

    public function test_bank_account_update_fails_with_invalid_type(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $updateData = [
            'account_name' => 'Valid Account Name',
            'type' => 'invalid_type',
        ];
        $response = $this->actingAs($this->user)->put(route('bank-accounts.update', $account), $updateData);
        $response->assertSessionHasErrors('type');
    }

    public function test_bank_account_update_fails_if_account_number_is_not_unique_for_user_ignoring_self(): void
    {
        $accountToUpdate = BankAccount::factory()->for($this->user)->create(['account_number' => '123456789']);
        $anotherAccount = BankAccount::factory()->for($this->user)->create(['account_number' => '987654321']);

        // Try to update $accountToUpdate with $anotherAccount's number
        $updateData = ['account_number' => '987654321'];
        $response = $this->actingAs($this->user)->put(route('bank-accounts.update', $accountToUpdate), $updateData);
        $response->assertSessionHasErrors('account_number');

        // Try to update $accountToUpdate with its own number (should pass)
        $updateDataOwn = ['account_number' => '123456789'];
        $responseOwn = $this->actingAs($this->user)->put(route('bank-accounts.update', $accountToUpdate), $updateDataOwn);
        $responseOwn->assertSessionDoesntHaveErrors('account_number');
    }

    public function test_opening_balance_is_not_updated_during_update_action(): void
    {
        $originalOpeningBalance = 543.21;
        $account = BankAccount::factory()->for($this->user)->create(['opening_balance' => $originalOpeningBalance]);

        $updateData = [
            'account_name' => 'Name Change Only',
            'type' => 'bank',
            'opening_balance' => 9999.99, // Attempt to change opening balance
        ];

        $this->actingAs($this->user)->put(route('bank-accounts.update', $account), $updateData);
        $account->refresh();
        $this->assertEquals($originalOpeningBalance, $account->opening_balance);
        $this->assertNotEquals(9999.99, $account->opening_balance);
    }

    // --- Test DESTROY (DELETE /bank-accounts/{bank_account}) ---
    public function test_guest_cannot_delete_bank_account(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $response = $this->delete(route('bank-accounts.destroy', $account));
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('bank_accounts', ['id' => $account->id]);
    }

    public function test_authenticated_user_can_delete_their_own_bank_account(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $this->assertDatabaseHas('bank_accounts', ['id' => $account->id]);

        $response = $this->actingAs($this->user)->delete(route('bank-accounts.destroy', $account));

        $response->assertRedirect(route('bank-accounts.index'));
        $response->assertSessionHas('success', 'Bank account deleted successfully.');
        $this->assertDatabaseMissing('bank_accounts', ['id' => $account->id]);
    }

    public function test_authenticated_user_cannot_delete_another_users_bank_account(): void
    {
        $otherUser = User::factory()->create();
        $otherUserAccount = BankAccount::factory()->for($otherUser)->create();
        $this->assertDatabaseHas('bank_accounts', ['id' => $otherUserAccount->id]);

        $response = $this->actingAs($this->user)->delete(route('bank-accounts.destroy', $otherUserAccount));

        $response->assertForbidden();
        $this->assertDatabaseHas('bank_accounts', ['id' => $otherUserAccount->id]); // Ensure it's still there
    }

    // --- Test SHOW (GET /bank-accounts/{bank_account}) ---
    public function test_guest_cannot_view_bank_account_details(): void
    {
        $account = BankAccount::factory()->for($this->user)->create();
        $response = $this->get(route('bank-accounts.show', $account));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_own_bank_account_details(): void
    {
        $account = BankAccount::factory()->for($this->user)->create([
            'account_name' => 'My Test Account Show',
            'type' => 'cash',
            'current_balance' => 777.77,
        ]);

        $response = $this->actingAs($this->user)->get(route('bank-accounts.show', $account));

        $response->assertOk();
        $response->assertViewIs('bank-accounts.show');
        $response->assertViewHas('bankAccount', $account);
        $response->assertSeeText('My Test Account Show');
        $response->assertSeeText('Cash'); // Checking type formatting
        $response->assertSeeText('$777.77'); // Checking balance formatting
    }

    public function test_authenticated_user_cannot_view_another_users_bank_account_details(): void
    {
        $otherUser = User::factory()->create();
        $otherUserAccount = BankAccount::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)->get(route('bank-accounts.show', $otherUserAccount));

        $response->assertForbidden();
    }

    // Basic Index View Test
    public function test_authenticated_user_can_view_bank_accounts_index_page(): void
    {
        BankAccount::factory()->for($this->user)->count(3)->create();
        $otherUser = User::factory()->create();
        BankAccount::factory()->for($otherUser)->count(2)->create(); // Accounts for another user

        $response = $this->actingAs($this->user)->get(route('bank-accounts.index'));
        $response->assertOk();
        $response->assertViewIs('bank-accounts.index');
        $response->assertViewHas('bankAccounts', function ($accounts) {
            return $accounts->count() === 3 && $accounts->every(fn ($acc) => $acc->user_id === $this->user->id);
        });
    }

    public function test_guest_cannot_view_bank_accounts_index_page(): void
    {
        $response = $this->get(route('bank-accounts.index'));
        $response->assertRedirect(route('login'));
    }
}
