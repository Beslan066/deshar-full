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
                'metrics' => $this->getMetrics(),
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
