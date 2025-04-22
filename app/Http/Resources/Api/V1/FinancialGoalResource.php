<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialGoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'target_amount' => $this->target_amount,
            'current_amount' => $this->current_amount,
            'due_date' => $this->due_date,
            'description' => $this->description,
            'category' => $this->category,
            'is_completed' => $this->is_completed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
