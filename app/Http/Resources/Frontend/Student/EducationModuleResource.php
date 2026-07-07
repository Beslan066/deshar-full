<?php

namespace App\Http\Resources\Frontend\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserModuleProgress;

class EducationModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $moduleProgress = $user ? $user->moduleProgress()->where('module_id', $this->id)->first() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'description' => $this->description,
            'complexity' => $this->complexity,
            'total_xp_reward' => $this->total_xp_reward,
            'total_pieces' => $this->pieces()->count(),
            'total_lessons' => $this->lessons()->count(),
            'total_tasks' => $this->tasks()->count(),
            'is_published' => $this->is_published,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'progress' => $moduleProgress ? [
                'status' => $moduleProgress->status,
                'progress_percentage' => $moduleProgress->progress_percentage,
                'progress_formatted' => $moduleProgress->progress_formatted,
                'is_completed' => $moduleProgress->is_completed,
                'started_at' => $moduleProgress->started_at?->toISOString(),
                'completed_at' => $moduleProgress->completed_at?->toISOString(),
                'time_spent_seconds' => $moduleProgress->time_spent_seconds,
            ] : [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
