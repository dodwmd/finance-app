<?php

namespace Tests\Feature;

use App\Models\FinancialGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialGoalFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_goals_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('goals.index'))
            ->assertStatus(200)
            ->assertSee('Financial Goals');
    }

    public function test_authenticated_user_can_create_goal()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'name' => 'Save for a car',
            'target_amount' => 5000,
            'type' => 'saving',
            'start_date' => now()->toDateString(),
            'target_date' => now()->addMonths(12)->toDateString(),
            'is_active' => true,
        ];

        $response = $this->post(route('goals.store'), $data);
        $response->assertRedirect();
        $this->assertDatabaseHas('financial_goals', [
            'name' => 'Save for a car',
            'user_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_update_goal()
    {
        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->for($user)->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($user);
        $response = $this->put(route('goals.update', $goal), [
            'name' => 'New Name',
            'target_amount' => $goal->target_amount,
            'type' => $goal->type,
            'start_date' => $goal->start_date->toDateString(),
            'target_date' => $goal->target_date->toDateString(),
            'is_active' => true,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('financial_goals', [
            'id' => $goal->id,
            'name' => 'New Name',
        ]);
    }

    public function test_authenticated_user_can_delete_goal()
    {
        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->for($user)->create();
        $this->actingAs($user);
        $response = $this->delete(route('goals.destroy', $goal));
        $response->assertRedirect();
        $this->assertDatabaseMissing('financial_goals', [
            'id' => $goal->id,
        ]);
    }
}
