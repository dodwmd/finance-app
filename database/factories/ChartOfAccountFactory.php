<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChartOfAccount>
 */
class ChartOfAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChartOfAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense', 'costofgoodssold'];
        $type = $this->faker->randomElement($accountTypes);

        // Generate a plausible account code based on type
        $prefix = match ($type) {
            'asset' => '1',
            'liability' => '2',
            'equity' => '3',
            'revenue' => '4',
            'expense' => '5',
            'costofgoodssold' => '6',
            default => '9',
        };
        $accountCode = $prefix.'-'.$this->faker->unique()->numerify('####');

        return [
            'user_id' => User::factory(),
            'account_code' => $accountCode,
            'name' => $this->faker->words(3, true),
            'type' => $type,
            'description' => $this->faker->optional()->sentence,
            'parent_id' => null, // Can be set specifically in tests/seeders
            'is_active' => true,
            'allow_direct_posting' => true,
            'system_account_tag' => null, // Can be set specifically for system accounts
        ];
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the account does not allow direct posting.
     */
    public function noDirectPosting(): static
    {
        return $this->state(fn () => [
            'allow_direct_posting' => false,
        ]);
    }

    /**
     * Assign a parent account.
     */
    public function withParent(ChartOfAccount $parentAccount): static
    {
        return $this->state(fn () => [
            'parent_id' => $parentAccount->id,
        ]);
    }

    /**
     * Assign a specific type.
     */
    public function withType(string $type): static
    {
        // Regenerate account code if type changes to maintain plausible prefix
        $lcType = strtolower($type); // Ensure consistency if PascalCase is passed
        $prefix = match ($lcType) {
            'asset' => '1',
            'liability' => '2',
            'equity' => '3',
            'revenue' => '4',
            'expense' => '5',
            'costofgoodssold' => '6',
            default => '9',
        };
        $accountCode = $prefix.'-'.$this->faker->unique()->numerify('####');

        return $this->state(fn () => [
            'type' => $lcType, // Store lowercase
            'account_code' => $accountCode,
        ]);
    }
}
