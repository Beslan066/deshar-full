<?php

namespace App\Http\Resources;

use App\Models\EducationModule;
use App\Models\EducationModulePiece;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\UserModuleProgress;
use App\Models\UserPieceProgress;
use App\Models\UserLessonProgress;
use App\Models\UserTaskProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Базовая информация о пользователе (используем существующий ApiUserResource)
            'user' => new ApiUserResource($this),

            // Общая статистика прогресса
            'stats' => $this->getProgressStats(),

            // Детальный прогресс по модулям
            'modules' => $this->getModulesWithProgress(),

            // Метрики
            'metrics' => $this->getMetrics(),

            // Рекомендации
            'recommended_modules' => $this->getRecommendedModules(),

            // Недавние достижения
            'recent_achievements' => $this->getRecentAchievements(),
        ];
    }

    /**
     * Получить общую статистику прогресса
     */
    private function getProgressStats(): array
    {
        $totalModules = EducationModule::count();
        $totalPieces = EducationModulePiece::count();
        $totalLessons = Lesson::count();
        $totalTasks = Task::count();

        $completedModules = $this->completedModules()->count();
        $completedPieces = $this->completedPieces()->count();
        $completedLessons = $this->completedLessons()->count();
        $completedTasks = $this->completedTasks()->count();

        return [
            'modules' => [
                'completed' => $completedModules,
                'total' => $totalModules,
                'percentage' => $totalModules > 0
                    ? round(($completedModules / $totalModules) * 100, 2)
                    : 0,
            ],
            'pieces' => [
                'completed' => $completedPieces,
                'total' => $totalPieces,
                'percentage' => $totalPieces > 0
                    ? round(($completedPieces / $totalPieces) * 100, 2)
                    : 0,
            ],
            'lessons' => [
                'completed' => $completedLessons,
                'total' => $totalLessons,
                'percentage' => $totalLessons > 0
                    ? round(($completedLessons / $totalLessons) * 100, 2)
                    : 0,
            ],
            'tasks' => [
                'completed' => $completedTasks,
                'total' => $totalTasks,
                'percentage' => $totalTasks > 0
                    ? round(($completedTasks / $totalTasks) * 100, 2)
                    : 0,
            ],
            'overall' => [
                'completion_rate' => $this->calculateOverallCompletionRate(),
                'total_xp' => $this->points ?? 0,
                'rank' => $this->getRank(),
            ],
        ];
    }

    /**
     * Получить все модули с прогрессом пользователя
     */
    private function getModulesWithProgress(): array
    {
        // Загружаем все модули с их структурой
        $modules = EducationModule::with([
            'pieces' => function ($query) {
                $query->with([
                    'lessons' => function ($query) {
                        $query->with([
                            'tasks'
                        ])->orderBy('order');
                    }
                ])->orderBy('order');
            }
        ])->orderBy('order')->get();

        $result = [];

        foreach ($modules as $module) {
            $moduleProgress = $this->getModuleProgress($module->id);

            $pieces = [];
            $moduleCompletedPieces = 0;
            $moduleTotalPieces = $module->pieces->count();
            $moduleTotalLessons = 0;
            $moduleCompletedLessons = 0;
            $moduleTotalTasks = 0;
            $moduleCompletedTasks = 0;

            foreach ($module->pieces as $piece) {
                $pieceProgress = $this->getPieceProgress($piece->id);
                $pieceCompletedLessons = 0;
                $pieceTotalLessons = $piece->lessons->count();
                $pieceCompletedTasks = 0;
                $pieceTotalTasks = 0;

                $lessons = [];
                foreach ($piece->lessons as $lesson) {
                    $lessonProgress = $this->getLessonProgress($lesson->id);
                    $lessonCompletedTasks = 0;
                    $lessonTotalTasks = $lesson->tasks->count();

                    $tasks = [];
                    foreach ($lesson->tasks as $task) {
                        $taskProgress = $this->getTaskProgress($task->id);
                        $isCompleted = $taskProgress &&
                            $taskProgress->status === UserTaskProgress::STATUS_COMPLETED;

                        if ($isCompleted) {
                            $lessonCompletedTasks++;
                        }

                        $tasks[] = [
                            'id' => $task->id,
                            'name' => $task->name,
                            'type' => $task->type,
                            'complexity' => $task->complexity ?? 1,
                            'order' => $task->order ?? 0,
                            'is_required' => $task->is_required ?? true,
                            'progress' => $taskProgress ? [
                                'status' => $taskProgress->status,
                                'is_completed' => $isCompleted,
                                'completed_at' => $taskProgress->completed_at?->toISOString(),
                                'attempts' => $taskProgress->attempts ?? 0,
                                'score' => $taskProgress->score ?? null,
                                'time_spent' => $taskProgress->time_spent ?? 0,
                                'xp_earned' => $taskProgress->xp_earned ?? 0,
                            ] : [
                                'status' => 'not_started',
                                'is_completed' => false,
                                'completed_at' => null,
                                'attempts' => 0,
                                'score' => null,
                                'time_spent' => 0,
                                'xp_earned' => 0,
                            ],
                        ];
                    }

                    $isLessonCompleted = $lessonProgress &&
                        $lessonProgress->status === UserLessonProgress::STATUS_COMPLETED;

                    if ($isLessonCompleted) {
                        $pieceCompletedLessons++;
                        $moduleCompletedLessons++;
                    }

                    $moduleCompletedTasks += $lessonCompletedTasks;
                    $pieceCompletedTasks += $lessonCompletedTasks;
                    $moduleTotalTasks += $lessonTotalTasks;
                    $pieceTotalTasks += $lessonTotalTasks;

                    $lessons[] = [
                        'id' => $lesson->id,
                        'name' => $lesson->name,
                        'description' => $lesson->description,
                        'order' => $lesson->order,
                        'type' => $lesson->type ?? 'regular',
                        'progress' => $lessonProgress ? [
                            'status' => $lessonProgress->status,
                            'percentage' => $lessonProgress->progress_percentage,
                            'is_completed' => $isLessonCompleted,
                            'completed_at' => $lessonProgress->completed_at?->toISOString(),
                            'time_spent' => $lessonProgress->time_spent ?? 0,
                            'xp_earned' => $lessonProgress->xp_earned ?? 0,
                        ] : [
                            'status' => 'not_started',
                            'percentage' => 0,
                            'is_completed' => false,
                            'completed_at' => null,
                            'time_spent' => 0,
                            'xp_earned' => 0,
                        ],
                        'tasks' => $tasks,
                        'stats' => [
                            'total_tasks' => $lessonTotalTasks,
                            'completed_tasks' => $lessonCompletedTasks,
                            'completion_rate' => $lessonTotalTasks > 0
                                ? round(($lessonCompletedTasks / $lessonTotalTasks) * 100, 2)
                                : 0,
                        ],
                    ];
                }

                $isPieceCompleted = $pieceProgress &&
                    $pieceProgress->status === UserPieceProgress::STATUS_COMPLETED;

                if ($isPieceCompleted) {
                    $moduleCompletedPieces++;
                }

                $pieces[] = [
                    'id' => $piece->id,
                    'name' => $piece->name,
                    'description' => $piece->description,
                    'image' => $piece->image,
                    'fon' => $piece->fon,
                    'order' => $piece->order,
                    'type' => $piece->type ?? 'regular',
                    'is_locked' => $this->isPieceLocked($piece),
                    'progress' => $pieceProgress ? [
                        'status' => $pieceProgress->status,
                        'percentage' => $pieceProgress->progress_percentage,
                        'is_completed' => $isPieceCompleted,
                        'completed_at' => $pieceProgress->completed_at?->toISOString(),
                        'time_spent' => $pieceProgress->time_spent ?? 0,
                        'xp_earned' => $pieceProgress->xp_earned ?? 0,
                    ] : [
                        'status' => 'not_started',
                        'percentage' => 0,
                        'is_completed' => false,
                        'completed_at' => null,
                        'time_spent' => 0,
                        'xp_earned' => 0,
                    ],
                    'lessons' => $lessons,
                    'stats' => [
                        'total_lessons' => $pieceTotalLessons,
                        'completed_lessons' => $pieceCompletedLessons,
                        'completion_rate' => $pieceTotalLessons > 0
                            ? round(($pieceCompletedLessons / $pieceTotalLessons) * 100, 2)
                            : 0,
                        'total_tasks' => $pieceTotalTasks,
                        'completed_tasks' => $pieceCompletedTasks,
                        'tasks_completion_rate' => $pieceTotalTasks > 0
                            ? round(($pieceCompletedTasks / $pieceTotalTasks) * 100, 2)
                            : 0,
                    ],
                ];
            }

            $isModuleCompleted = $moduleProgress &&
                $moduleProgress->status === UserModuleProgress::STATUS_COMPLETED;

            $result[] = [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'image' => $module->image,
                'complexity' => $module->complexity,
                'order' => $module->order,
                'is_locked' => $this->isModuleLocked($module),
                'progress' => $moduleProgress ? [
                    'status' => $moduleProgress->status,
                    'percentage' => $moduleProgress->progress_percentage,
                    'is_completed' => $isModuleCompleted,
                    'completed_at' => $moduleProgress->completed_at?->toISOString(),
                    'time_spent' => $moduleProgress->time_spent ?? 0,
                    'xp_earned' => $moduleProgress->xp_earned ?? 0,
                ] : [
                    'status' => 'not_started',
                    'percentage' => 0,
                    'is_completed' => false,
                    'completed_at' => null,
                    'time_spent' => 0,
                    'xp_earned' => 0,
                ],
                'pieces' => $pieces,
                'stats' => [
                    'total_pieces' => $moduleTotalPieces,
                    'completed_pieces' => $moduleCompletedPieces,
                    'completion_rate' => $moduleTotalPieces > 0
                        ? round(($moduleCompletedPieces / $moduleTotalPieces) * 100, 2)
                        : 0,
                    'total_lessons' => $moduleTotalLessons,
                    'completed_lessons' => $moduleCompletedLessons,
                    'lessons_completion_rate' => $moduleTotalLessons > 0
                        ? round(($moduleCompletedLessons / $moduleTotalLessons) * 100, 2)
                        : 0,
                    'total_tasks' => $moduleTotalTasks,
                    'completed_tasks' => $moduleCompletedTasks,
                    'tasks_completion_rate' => $moduleTotalTasks > 0
                        ? round(($moduleCompletedTasks / $moduleTotalTasks) * 100, 2)
                        : 0,
                ],
            ];
        }

        return $result;
    }

    /**
     * Получить прогресс модуля
     */
    private function getModuleProgress(int $moduleId)
    {
        return $this->moduleProgress()
            ->where('module_id', $moduleId)
            ->first();
    }

    /**
     * Получить прогресс части
     */
    private function getPieceProgress(int $pieceId)
    {
        return $this->pieceProgress()
            ->where('piece_id', $pieceId)
            ->first();
    }

    /**
     * Получить прогресс урока
     */
    private function getLessonProgress(int $lessonId)
    {
        return $this->lessonProgress()
            ->where('lesson_id', $lessonId)
            ->first();
    }

    /**
     * Получить прогресс задания
     */
    private function getTaskProgress(int $taskId)
    {
        return $this->taskProgress()
            ->where('task_id', $taskId)
            ->first();
    }

    /**
     * Проверить, заблокирован ли модуль
     */
    private function isModuleLocked($module): bool
    {
        // Если нет требования к предыдущему модулю - не блокируем
        if (!$module->requires_previous) {
            return false;
        }

        // Проверяем завершение предыдущего модуля
        $previousModule = EducationModule::where('order', '<', $module->order)
            ->orderBy('order', 'desc')
            ->first();

        if (!$previousModule) {
            return false;
        }

        $previousProgress = $this->moduleProgress()
            ->where('module_id', $previousModule->id)
            ->first();

        return !$previousProgress ||
            $previousProgress->status !== UserModuleProgress::STATUS_COMPLETED;
    }

    /**
     * Проверить, заблокирована ли часть
     */
    private function isPieceLocked($piece): bool
    {
        // Проверяем, завершена ли предыдущая часть
        $previousPiece = EducationModulePiece::where('module_id', $piece->module_id)
            ->where('order', '<', $piece->order)
            ->orderBy('order', 'desc')
            ->first();

        if (!$previousPiece) {
            return false;
        }

        $previousProgress = $this->pieceProgress()
            ->where('piece_id', $previousPiece->id)
            ->first();

        return !$previousProgress ||
            $previousProgress->status !== UserPieceProgress::STATUS_COMPLETED;
    }

    /**
     * Рассчитать общий процент завершения
     */
    private function calculateOverallCompletionRate(): float
    {
        $totalModules = EducationModule::count();
        $completedModules = $this->completedModules()->count();

        if ($totalModules === 0) {
            return 0;
        }

        return round(($completedModules / $totalModules) * 100, 2);
    }

    /**
     * Получить метрики
     */
    private function getMetrics(): array
    {
        return [
            'total_modules' => EducationModule::count(),
            'total_pieces' => EducationModulePiece::count(),
            'total_lessons' => Lesson::count(),
            'total_tasks' => Task::count(),
            'completed_modules' => $this->completedModules()->count(),
            'completed_pieces' => $this->completedPieces()->count(),
            'completed_lessons' => $this->completedLessons()->count(),
            'completed_tasks' => $this->completedTasks()->count(),
            'completion_rate' => $this->calculateOverallCompletionRate(),
            'total_xp_earned' => $this->points ?? 0,
            'average_score' => $this->calculateAverageScore(),
            'total_time_spent' => $this->calculateTotalTimeSpent(),
        ];
    }

    /**
     * Рассчитать средний балл
     */
    private function calculateAverageScore(): float
    {
        $scores = $this->taskProgress()
            ->whereNotNull('score')
            ->pluck('score');

        if ($scores->isEmpty()) {
            return 0;
        }

        return round($scores->avg(), 2);
    }

    /**
     * Рассчитать общее время
     */
    private function calculateTotalTimeSpent(): int
    {
        $taskTime = $this->taskProgress()->sum('time_spent');
        $lessonTime = $this->lessonProgress()->sum('time_spent');
        $pieceTime = $this->pieceProgress()->sum('time_spent');
        $moduleTime = $this->moduleProgress()->sum('time_spent');

        return $taskTime + $lessonTime + $pieceTime + $moduleTime;
    }

    /**
     * Получить рекомендованные модули
     */
    private function getRecommendedModules(int $limit = 3): array
    {
        $completedModuleIds = $this->moduleProgress()
            ->where('status', UserModuleProgress::STATUS_COMPLETED)
            ->pluck('module_id')
            ->toArray();

        // Также исключаем модули, которые уже начаты
        $inProgressModuleIds = $this->moduleProgress()
            ->where('status', UserModuleProgress::STATUS_IN_PROGRESS)
            ->pluck('module_id')
            ->toArray();

        $excludeIds = array_merge($completedModuleIds, $inProgressModuleIds);

        $recommended = EducationModule::whereNotIn('id', $excludeIds)
            ->orderBy('complexity')
            ->limit($limit)
            ->get();

        return $recommended->map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'image' => $module->image,
                'complexity' => $module->complexity,
                'total_lessons' => $module->lessons()->count(),
                'total_pieces' => $module->pieces()->count(),
            ];
        })->toArray();
    }

    /**
     * Получить недавние достижения
     */
    private function getRecentAchievements(int $limit = 5): array
    {
        $achievements = [];

        // Проверяем различные достижения
        $completedTasks = $this->completedTasks()->count();
        $completedLessons = $this->completedLessons()->count();
        $currentStreak = $this->current_streak ?? 0;
        $level = $this->level ?? 0;

        if ($completedTasks >= 10) {
            $achievements[] = [
                'name' => 'Первые шаги',
                'description' => 'Выполнено 10 заданий',
                'icon' => '🎯',
                'type' => 'tasks',
                'progress' => min(100, ($completedTasks / 10) * 100),
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($completedTasks >= 50) {
            $achievements[] = [
                'name' => 'Усердный ученик',
                'description' => 'Выполнено 50 заданий',
                'icon' => '📚',
                'type' => 'tasks',
                'progress' => min(100, ($completedTasks / 50) * 100),
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($completedTasks >= 100) {
            $achievements[] = [
                'name' => 'Трудоголик',
                'description' => 'Выполнено 100 заданий',
                'icon' => '💪',
                'type' => 'tasks',
                'progress' => 100,
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($completedLessons >= 5) {
            $achievements[] = [
                'name' => 'Исследователь',
                'description' => 'Завершено 5 уроков',
                'icon' => '🔍',
                'type' => 'lessons',
                'progress' => min(100, ($completedLessons / 5) * 100),
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($currentStreak >= 7) {
            $achievements[] = [
                'name' => 'Недельный стрик',
                'description' => '7 дней подряд',
                'icon' => '🔥',
                'type' => 'streak',
                'progress' => min(100, ($currentStreak / 7) * 100),
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($currentStreak >= 30) {
            $achievements[] = [
                'name' => 'Месячный стрик',
                'description' => '30 дней подряд',
                'icon' => '⚡',
                'type' => 'streak',
                'progress' => 100,
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($level >= 3) {
            $achievements[] = [
                'name' => 'Знаток',
                'description' => 'Достигнут 3 уровень',
                'icon' => '⭐',
                'type' => 'level',
                'progress' => min(100, ($level / 3) * 100),
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        if ($level >= 5) {
            $achievements[] = [
                'name' => 'Мастер',
                'description' => 'Достигнут 5 уровень',
                'icon' => '🏆',
                'type' => 'level',
                'progress' => 100,
                'earned_at' => now()->toISOString(),
                'is_earned' => true,
            ];
        }

        // Сортируем по прогрессу (сначала завершенные)
        usort($achievements, function ($a, $b) {
            return $b['progress'] <=> $a['progress'];
        });

        return array_slice($achievements, 0, $limit);
    }
}
