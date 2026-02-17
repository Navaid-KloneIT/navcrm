<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'close_date' => $this->close_date?->toDateString(),
            'probability' => $this->probability,
            'weighted_amount' => $this->weighted_amount,
            'description' => $this->description,
            'next_steps' => $this->next_steps,
            'competitor' => $this->competitor,
            'source' => $this->source,
            'won_at' => $this->won_at?->toIso8601String(),
            'lost_at' => $this->lost_at?->toIso8601String(),
            'lost_reason' => $this->lost_reason,
            'stage' => new PipelineStageResource($this->whenLoaded('stage')),
            'account' => new AccountResource($this->whenLoaded('account')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'team_members' => $this->whenLoaded('teamMembers', function () {
                return $this->teamMembers->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'split_percentage' => $user->pivot->split_percentage,
                ]);
            }),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'quotes_count' => $this->whenCounted('quotes'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
