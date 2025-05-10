<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
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
            'account_code' => $this->account_code,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'is_active' => (bool) $this->is_active,
            'allow_direct_posting' => (bool) $this->allow_direct_posting,
            'system_account_tag' => $this->system_account_tag,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'parent' => new ChartOfAccountResource($this->whenLoaded('parent')),
            'children' => ChartOfAccountResource::collection($this->whenLoaded('children')),
        ];
    }
}
