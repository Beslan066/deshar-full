<?php

namespace App\Http\Resources\Frontend\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserModuleProgress;

class EducationModuleDetailedResource extends JsonResource
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

        // Загружаем разделы с уроками
        $this->load(['pieces' => function ($query) {
            $query->orderBy('sort_order');
        }, 'pieces.lessons' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        return [
            'module' => [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'image' => $this->image,
                'description' => $this->description,
                'complexity' => $this->complexity,
                'total_xp_reward' => $this->total_xp_reward,
                'total_pieces' => $this->pieces->count(),
                'total_lessons' => $this->lessons()->count(),
                'total_tasks' => $this->tasks()->count(),
                'is_published' => $this->is_published,
                'sort_order' => $this->sort_order,
            ],
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
            'pieces' => $this->pieces->map(function ($piece) use ($user) {
                $pieceProgress = $user ? $user->pieceProgress()->where('piece_id', $piece->id)->first() : null;

                return [
                    'id' => $piece->id,
                    'name' => $piece->name,
                    'fon' => $piece->fon,
                    'sort_order' => $piece->sort_order,
                    'total_lessons' => $piece->lessons->count(),
                    'progress' => $pieceProgress ? [
                        'status' => $pieceProgress->status,
                        'progress_percentage' => $pieceProgress->progress_percentage,
                        'is_completed' => $pieceProgress->is_completed,
                        'started_at' => $pieceProgress->started_at?->toISOString(),
                        'completed_at' => $pieceProgress->completed_at?->toISOString(),
                    ] : [
                        'status' => 'not_started',
                        'progress_percentage' => 0,
                        'is_completed' => false,
                        'started_at' => null,
                        'completed_at' => null,
                    ],
                    'lessons' => $piece->lessons->map(function ($lesson) use ($user) {
                        $lessonProgress = $user ? $user->lessonProgress()->where('lesson_id', $lesson->id)->first() : null;

                        return [
                            'id' => $lesson->id,
                            'name' => $lesson->name,
                            'sort_order' => $lesson->sort_order,
                            'progress' => $lessonProgress ? [
                                'status' => $lessonProgress->status,
                                'progress_percentage' => $lessonProgress->progress_percentage,
                                'is_completed' => $lessonProgress->is_completed,
                                'started_at' => $lessonProgress->started_at?->toISOString(),
                                'completed_at' => $lessonProgress->completed_at?->toISOString(),
                            ] : [
                                'status' => 'not_started',
                                'progress_percentage' => 0,
                                'is_completed' => false,
                                'started_at' => null,
                                'completed_at' => null,
                            ],
                        ];
                    }),
                ];
            }),
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
            'success' => true,
            'message' => 'Детальная информация о модуле успешно получена',
        ];
    }
}
