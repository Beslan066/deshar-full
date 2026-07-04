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
use Illuminate\Support\Facades\Log;

class UserProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        try {
            return [
                'user' => new ApiUserResource($this),
                'stats' => $this->getProgressStats(),
                'modules' => $this->getModulesWithProgress(),
                'metrics' => $this->getMetrics(),
                'recommended_modules' => $this->getRecommendedModules(),
                'recent_achievements' => $this->getRecentAchievements(),
            ];
        } catch (\Exception $e) {
            Log::error('Error in UserProgressResource: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'user' => new ApiUserResource($this),
                'error' => 'Unable to load progress data',
            ];
        }
    }

    /**
     * Получить общую статистику прогресса
     */
    private function getProgressStats(): array
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error in getProgressStats: ' . $e->getMessage());
            return [
                'modules' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'pieces' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'lessons' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'tasks' => ['completed' => 0, 'total' => 0, 'percentage' => 0],
                'overall' => ['completion_rate' => 0, 'total_xp' => 0, 'rank' => 0],
            ];
        }
    }

    /**
     * Получить все модули с прогрессом
     */
    private function getModulesWithProgress(): array
    {
        try {
            // Загружаем все модули с их структурой
            $modules = EducationModule::with([
                'pieces' => function ($query) {
                    $query->with([
                        'lessons' => function ($query) {
                            $query->with(['tasks']);
                        }
                    ]);
                }
            ])->get();

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
                                'name' => $task->name ?? 'Задание',
                                'type' => $task->type ?? 'default',
                                'complexity' => $task->complexity ?? 1,
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
                            'name' => $lesson->name ?? 'Урок',
                            'description' => $lesson->description ?? '',
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
                        'name' => $piece->name ?? 'Часть',
                        'description' => $piece->description ?? '',
                        'image' => $piece->image ?? null,
                        'fon' => $piece->fon ?? null,
                        'type' => $piece->type ?? 'regular',
                        'is_locked' => false,
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
                    'name' => $module->name ?? 'Модуль',
                    'description' => $module->description ?? '',
                    'image' => $module->image ?? null,
                    'complexity' => $module->complexity ?? 1,
                    'is_locked' => false,
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
        } catch (\Exception $e) {
            Log::error('Error in getModulesWithProgress: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Получить прогресс модуля
     */
    private function getModuleProgress(int $moduleId)
    {
        try {
            return $this->moduleProgress()
                ->where('module_id', $moduleId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Error in getModuleProgress: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить прогресс части
     */
    private function getPieceProgress(int $pieceId)
    {
        try {
            return $this->pieceProgress()
                ->where('piece_id', $pieceId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Error in getPieceProgress: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить прогресс урока
     */
    private function getLessonProgress(int $lessonId)
    {
        try {
            return $this->lessonProgress()
                ->where('lesson_id', $lessonId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Error in getLessonProgress: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить прогресс задания
     */
    private function getTaskProgress(int $taskId)
    {
        try {
            return $this->taskProgress()
                ->where('task_id', $taskId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Error in getTaskProgress: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Рассчитать общий процент завершения
     */
    private function calculateOverallCompletionRate(): float
    {
        try {
            $totalModules = EducationModule::count();
            $completedModules = $this->completedModules()->count();

            if ($totalModules === 0) {
                return 0;
            }

            return round(($completedModules / $totalModules) * 100, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Получить метрики
     */
    private function getMetrics(): array
    {
        try {
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
        } catch (\Exception $e) {
            return [
                'total_modules' => 0,
                'total_pieces' => 0,
                'total_lessons' => 0,
                'total_tasks' => 0,
                'completed_modules' => 0,
                'completed_pieces' => 0,
                'completed_lessons' => 0,
                'completed_tasks' => 0,
                'completion_rate' => 0,
                'total_xp_earned' => 0,
                'average_score' => 0,
                'total_time_spent' => 0,
            ];
        }
    }

    /**
     * Рассчитать средний балл
     */
    private function calculateAverageScore(): float
    {
        try {
            $scores = $this->taskProgress()
                ->whereNotNull('score')
                ->pluck('score');

            if ($scores->isEmpty()) {
                return 0;
            }

            return round($scores->avg(), 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Рассчитать общее время
     */
    private function calculateTotalTimeSpent(): int
    {
        try {
            $taskTime = $this->taskProgress()->sum('time_spent');
            $lessonTime = $this->lessonProgress()->sum('time_spent');
            $pieceTime = $this->pieceProgress()->sum('time_spent');
            $moduleTime = $this->moduleProgress()->sum('time_spent');

            return $taskTime + $lessonTime + $pieceTime + $moduleTime;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Получить рекомендованные модули
     */
    private function getRecommendedModules(int $limit = 3): array
    {
        try {
            $completedModuleIds = $this->moduleProgress()
                ->where('status', UserModuleProgress::STATUS_COMPLETED)
                ->pluck('module_id')
                ->toArray();

            $inProgressModuleIds = $this->moduleProgress()
                ->where('status', UserModuleProgress::STATUS_IN_PROGRESS)
                ->pluck('module_id')
                ->toArray();

            $excludeIds = array_merge($completedModuleIds, $inProgressModuleIds);

            $recommended = EducationModule::whereNotIn('id', $excludeIds)
                ->limit($limit)
                ->get();

            return $recommended->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name ?? 'Модуль',
                    'description' => $module->description ?? '',
                    'image' => $module->image ?? null,
                    'complexity' => $module->complexity ?? 1,
                    'total_lessons' => $module->lessons()->count(),
                    'total_pieces' => $module->pieces()->count(),
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Получить недавние достижения
     */
    private function getRecentAchievements(int $limit = 5): array
    {
        try {
            $achievements = [];

            $completedTasks = $this->completedTasks()->count();
            $completedLessons = $this->completedLessons()->count();
            $currentStreak = $this->current_streak ?? 0;
            $level = $this->level ?? 0;

            // Достижения за задания
            if ($completedTasks >= 10) {
                $achievements[] = [
                    'name' => 'Первые шаги',
                    'description' => 'Выполнено 10 заданий',
                    'icon' => '🎯',
                    'type' => 'tasks',
                    'progress' => min(100, ($completedTasks / 10) * 100),
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
                    'is_earned' => true,
                ];
            }

            // Достижения за уроки
            if ($completedLessons >= 5) {
                $achievements[] = [
                    'name' => 'Исследователь',
                    'description' => 'Завершено 5 уроков',
                    'icon' => '🔍',
                    'type' => 'lessons',
                    'progress' => min(100, ($completedLessons / 5) * 100),
                    'is_earned' => true,
                ];
            }

            if ($completedLessons >= 20) {
                $achievements[] = [
                    'name' => 'Эрудит',
                    'description' => 'Завершено 20 уроков',
                    'icon' => '🧠',
                    'type' => 'lessons',
                    'progress' => min(100, ($completedLessons / 20) * 100),
                    'is_earned' => true,
                ];
            }

            // Достижения за стрик
            if ($currentStreak >= 7) {
                $achievements[] = [
                    'name' => 'Недельный стрик',
                    'description' => '7 дней подряд',
                    'icon' => '🔥',
                    'type' => 'streak',
                    'progress' => min(100, ($currentStreak / 7) * 100),
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
                    'is_earned' => true,
                ];
            }

            // Достижения за уровень
            if ($level >= 3) {
                $achievements[] = [
                    'name' => 'Знаток',
                    'description' => 'Достигнут 3 уровень',
                    'icon' => '⭐',
                    'type' => 'level',
                    'progress' => min(100, ($level / 3) * 100),
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
                    'is_earned' => true,
                ];
            }

            if ($level >= 10) {
                $achievements[] = [
                    'name' => 'Легенда',
                    'description' => 'Достигнут 10 уровень',
                    'icon' => '👑',
                    'type' => 'level',
                    'progress' => 100,
                    'is_earned' => true,
                ];
            }

            // Сортируем по прогрессу
            usort($achievements, function ($a, $b) {
                return $b['progress'] <=> $a['progress'];
            });

            return array_slice($achievements, 0, $limit);
        } catch (\Exception $e) {
            return [];
        }
    }
}
