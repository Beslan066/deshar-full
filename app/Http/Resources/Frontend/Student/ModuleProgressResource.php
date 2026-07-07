<?php

namespace App\Http\Resources\Frontend\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserModuleProgress;

class ModuleProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'module_id' => $this->id,
            'module_name' => $this->name,
            'module_slug' => $this->slug,
            'module_image' => $this->image,
            'complexity' => $this->complexity,
            'progress' => $this->getProgressForUser($user),
            'summary' => [
                'total_pieces' => $this->pieces()->count(),
                'completed_pieces' => $this->getCompletedPiecesCount($user),
                'total_lessons' => $this->lessons()->count(),
                'completed_lessons' => $this->getCompletedLessonsCount($user),
                'total_tasks' => $this->tasks()->count(),
                'completed_tasks' => $this->getCompletedTasksCount($user),
            ],
        ];
    }

    /**
     * Get progress for specific user
     */
    protected function getProgressForUser($user): array
    {
        if (!$user) {
            return [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
            ];
        }

        $progress = $user->moduleProgress()->where('module_id', $this->id)->first();

        if (!$progress) {
            return [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
            ];
        }

        return [
            'status' => $progress->status,
            'progress_percentage' => $progress->progress_percentage,
            'progress_formatted' => $progress->progress_formatted,
            'is_completed' => $progress->is_completed,
            'started_at' => $progress->started_at?->toISOString(),
            'completed_at' => $progress->completed_at?->toISOString(),
            'time_spent_seconds' => $progress->time_spent_seconds,
        ];
    }

    /**
     * Get completed pieces count for user
     */
    protected function getCompletedPiecesCount($user): int
    {
        if (!$user) {
            return 0;
        }

        return $this->pieces()
            ->whereHas('userProgress', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'completed');
            })
            ->count();
    }

    /**
     * Get completed lessons count for user
     */
    protected function getCompletedLessonsCount($user): int
    {
        if (!$user) {
            return 0;
        }

        return $this->lessons()
            ->whereHas('userProgress', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'completed');
            })
            ->count();
    }

    /**
     * Get completed tasks count for user
     */
    protected function getCompletedTasksCount($user): int
    {
        if (!$user) {
            return 0;
        }

        return $this->tasks()
            ->whereHas('userProgress', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'completed');
            })
            ->count();
    }
}
