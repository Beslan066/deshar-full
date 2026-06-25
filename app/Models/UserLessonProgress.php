<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'user_lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'progress_percentage',
        'completed_at',
        'started_at',
        'last_activity_at',
        'time_spent_seconds',
        'metadata',
    ];

    protected $casts = [
        'progress_percentage' => 'float',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'metadata' => 'array',
    ];

    // ============================================================
    // 🔗 КОНСТАНТЫ СТАТУСОВ
    // ============================================================

    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // ============================================================
    // 🔗 СВЯЗИ (Relationships)
    // ============================================================

    /**
     * Связь с пользователем
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с уроком
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Связь с разделом (через урок)
     */
    public function piece()
    {
        return $this->hasOneThrough(
            EducationModulePiece::class,
            Lesson::class,
            'id',
            'id',
            'lesson_id',
            'piece_id'
        );
    }

    /**
     * Связь с модулем (через урок и раздел)
     */
    public function module()
    {
        return $this->hasOneThrough(
            EducationModule::class,
            EducationModulePiece::class,
            'id',
            'id',
            'lesson_id',
            'education_module_id'
        );
    }

    // ============================================================
    // 📊 СКОУПЫ (Scopes)
    // ============================================================

    /**
     * Только завершенные уроки
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Только в процессе
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Только не начатые
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', self::STATUS_NOT_STARTED);
    }

    /**
     * Только проваленные
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Фильтр по пользователю
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Фильтр по уроку
     */
    public function scopeForLesson($query, int $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Сортировка по последней активности
     */
    public function scopeRecentlyActive($query)
    {
        return $query->orderBy('last_activity_at', 'desc');
    }

    /**
     * Прогресс больше указанного процента
     */
    public function scopeProgressGreaterThan($query, float $percentage)
    {
        return $query->where('progress_percentage', '>=', $percentage);
    }

    /**
     * Прогресс меньше указанного процента
     */
    public function scopeProgressLessThan($query, float $percentage)
    {
        return $query->where('progress_percentage', '<', $percentage);
    }

    // ============================================================
    // 🎯 АКСЕССОРЫ (Accessors)
    // ============================================================

    /**
     * Завершен ли урок
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * В процессе ли урок
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Не начат ли урок
     */
    public function getIsNotStartedAttribute(): bool
    {
        return $this->status === self::STATUS_NOT_STARTED;
    }

    /**
     * Провален ли урок
     */
    public function getIsFailedAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Получить прогресс в виде строки
     */
    public function getProgressFormattedAttribute(): string
    {
        return round($this->progress_percentage, 1) . '%';
    }

    /**
     * Получить статус с эмодзи
     */
    public function getStatusWithEmojiAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => '✅ ' . __('Завершен'),
            self::STATUS_IN_PROGRESS => '🔄 ' . __('В процессе'),
            self::STATUS_NOT_STARTED => '⬜ ' . __('Не начат'),
            self::STATUS_FAILED => '❌ ' . __('Провален'),
            default => $this->status,
        };
    }

    /**
     * Получить цвет статуса для UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'green',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_NOT_STARTED => 'gray',
            self::STATUS_FAILED => 'red',
            default => 'gray',
        };
    }

    /**
     * Получить иконку статуса
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'check-circle',
            self::STATUS_IN_PROGRESS => 'clock',
            self::STATUS_NOT_STARTED => 'circle',
            self::STATUS_FAILED => 'x-circle',
            default => 'circle',
        };
    }

    /**
     * Получить время в удобном формате
     */
    public function getTimeSpentFormattedAttribute(): string
    {
        if (!$this->time_spent_seconds) {
            return '0 мин';
        }

        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;

        if ($minutes === 0) {
            return "{$seconds} сек";
        }

        if ($seconds === 0) {
            return "{$minutes} мин";
        }

        return "{$minutes} мин {$seconds} сек";
    }

    /**
     * Получить общее количество заданий в уроке
     */
    public function getTotalTasksAttribute(): int
    {
        return $this->lesson?->tasks()->count() ?? 0;
    }

    /**
     * Получить количество выполненных заданий
     */
    public function getCompletedTasksAttribute(): int
    {
        if (!$this->lesson) {
            return 0;
        }

        return $this->lesson->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();
    }

    /**
     * Получить количество оставшихся заданий
     */
    public function getRemainingTasksAttribute(): int
    {
        return $this->total_tasks - $this->completed_tasks;
    }

    /**
     * Получить XP, заработанный в уроке
     */
    public function getEarnedXpAttribute(): int
    {
        if (!$this->lesson) {
            return 0;
        }

        return $this->lesson->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->sum('xp_reward');
    }

    /**
     * Получить максимальный XP в уроке
     */
    public function getMaxXpAttribute(): int
    {
        return $this->lesson?->tasks()->sum('xp_reward') ?? 0;
    }

    /**
     * Получить процент выполнения в виде числа (0-100)
     */
    public function getProgressAttribute(): float
    {
        return $this->progress_percentage ?? 0;
    }

    // ============================================================
    // 🔧 МЕТОДЫ (Methods)
    // ============================================================

    /**
     * Обновить прогресс урока
     */
    public function updateProgress(): void
    {
        $lesson = $this->lesson;
        if (!$lesson) {
            return;
        }

        $totalTasks = $lesson->tasks()->count();

        if ($totalTasks === 0) {
            $this->progress_percentage = 100;
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
            $this->save();
            return;
        }

        $completedTasks = $lesson->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();

        $this->progress_percentage = round(($completedTasks / $totalTasks) * 100, 2);

        // Обновляем статус
        if ($this->progress_percentage >= 100) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
        } elseif ($this->progress_percentage > 0 && $this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_IN_PROGRESS;
            if (!$this->started_at) {
                $this->started_at = now();
            }
        } elseif ($this->progress_percentage === 0 && $this->status === self::STATUS_NOT_STARTED) {
            $this->status = self::STATUS_IN_PROGRESS;
            $this->started_at = now();
        }

        $this->last_activity_at = now();
        $this->save();

        // Обновляем прогресс раздела и модуля
        $this->updatePieceProgress();
        $this->updateModuleProgress();
    }

    /**
     * Обновить прогресс раздела
     */
    private function updatePieceProgress(): void
    {
        $piece = $this->piece;
        if (!$piece) {
            return;
        }

        // Можно создать отдельную таблицу user_piece_progress
        // Или вычислять на лету
        // Для простоты обновляем через метод в EducationModulePiece
        $piece->updateUserProgress($this->user_id);
    }

    /**
     * Обновить прогресс модуля
     */
    private function updateModuleProgress(): void
    {
        $module = $this->module;
        if (!$module) {
            return;
        }

        // Аналогично для модуля
        $module->updateUserProgress($this->user_id);
    }

    /**
     * Отметить урок как завершенный
     */
    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->progress_percentage = 100;
        $this->completed_at = now();
        $this->last_activity_at = now();
        $this->save();

        // Обновляем XP пользователя (бонус за завершение урока)
        $bonusXp = $this->lesson?->xp_reward ?? 10;
        $this->user->increment('points', $bonusXp);
    }

    /**
     * Отметить урок как проваленный
     */
    public function markFailed(): void
    {
        $this->status = self::STATUS_FAILED;
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Сбросить прогресс урока
     */
    public function reset(): void
    {
        $this->status = self::STATUS_NOT_STARTED;
        $this->progress_percentage = 0;
        $this->completed_at = null;
        $this->started_at = null;
        $this->last_activity_at = null;
        $this->time_spent_seconds = 0;
        $this->metadata = null;
        $this->save();

        // Удаляем прогресс всех заданий в уроке
        $this->lesson?->tasks()->each(function ($task) {
            $task->userProgress()
                ->where('user_id', $this->user_id)
                ->delete();
        });
    }

    /**
     * Обновить время, проведенное в уроке
     */
    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Проверить, доступен ли следующий урок
     */
    public function isNextLessonAvailable(): bool
    {
        if (!$this->lesson) {
            return false;
        }

        // Проверяем, завершен ли текущий урок
        if (!$this->is_completed) {
            return false;
        }

        // Проверяем, есть ли следующий урок
        $nextLesson = $this->lesson->piece?->lessons()
            ->where('sort_order', '>', $this->lesson->sort_order)
            ->first();

        return $nextLesson !== null;
    }

    /**
     * Получить следующий урок
     */
    public function getNextLesson(): ?Lesson
    {
        if (!$this->lesson) {
            return null;
        }

        return $this->lesson->piece?->lessons()
            ->where('sort_order', '>', $this->lesson->sort_order)
            ->first();
    }

    /**
     * Получить предыдущий урок
     */
    public function getPreviousLesson(): ?Lesson
    {
        if (!$this->lesson) {
            return null;
        }

        return $this->lesson->piece?->lessons()
            ->where('sort_order', '<', $this->lesson->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
    }

    /**
     * Получить прогресс в виде массива для API
     */
    public function toApiArray(): array
    {
        return [
            'status' => $this->status,
            'status_name' => $this->status_with_emoji,
            'status_color' => $this->status_color,
            'progress' => $this->progress_percentage,
            'progress_formatted' => $this->progress_formatted,
            'completed_tasks' => $this->completed_tasks,
            'total_tasks' => $this->total_tasks,
            'remaining_tasks' => $this->remaining_tasks,
            'earned_xp' => $this->earned_xp,
            'max_xp' => $this->max_xp,
            'time_spent' => $this->time_spent_seconds,
            'time_spent_formatted' => $this->time_spent_formatted,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'is_completed' => $this->is_completed,
            'is_in_progress' => $this->is_in_progress,
            'is_failed' => $this->is_failed,
        ];
    }

    // ============================================================
    // 📊 СТАТИСТИКА
    // ============================================================

    /**
     * Получить детальную статистику по уроку
     */
    public function getDetailedStats(): array
    {
        $lesson = $this->lesson;
        if (!$lesson) {
            return [];
        }

        $tasks = $lesson->tasks()->orderBy('sort_order')->get();
        $taskProgress = [];

        foreach ($tasks as $task) {
            $progress = $task->userProgress()
                ->where('user_id', $this->user_id)
                ->first();

            $taskProgress[] = [
                'task_id' => $task->id,
                'title' => $task->title,
                'type' => $task->taskType?->name,
                'status' => $progress?->status ?? UserTaskProgress::STATUS_PENDING,
                'attempts' => $progress?->attempts_count ?? 0,
                'score' => $progress?->score ?? 0,
                'is_completed' => $progress?->is_completed ?? false,
            ];
        }

        return [
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'total_tasks' => $this->total_tasks,
            ],
            'progress' => $this->toApiArray(),
            'tasks' => $taskProgress,
            'statistics' => [
                'average_time_per_task' => $this->total_tasks > 0
                    ? round($this->time_spent_seconds / $this->total_tasks, 2)
                    : 0,
                'tasks_completed_rate' => $this->total_tasks > 0
                    ? round(($this->completed_tasks / $this->total_tasks) * 100, 2)
                    : 0,
                'xp_efficiency' => $this->max_xp > 0
                    ? round(($this->earned_xp / $this->max_xp) * 100, 2)
                    : 0,
            ],
        ];
    }

    /**
     * Получить время, потраченное на каждый тип задания
     */
    public function getTimeByTaskType(): array
    {
        if (!$this->lesson) {
            return [];
        }

        $taskTypes = $this->lesson->tasks()
            ->with('taskType')
            ->get()
            ->groupBy('task_type_id')
            ->map(function ($tasks) {
                $typeName = $tasks->first()->taskType?->name ?? 'Unknown';
                $totalTime = 0;
                $count = 0;

                foreach ($tasks as $task) {
                    $progress = $task->userProgress()
                        ->where('user_id', $this->user_id)
                        ->first();

                    if ($progress) {
                        $totalTime += $progress->time_spent_seconds ?? 0;
                        $count++;
                    }
                }

                return [
                    'type_name' => $typeName,
                    'total_time' => $totalTime,
                    'total_time_formatted' => $this->formatTime($totalTime),
                    'average_time' => $count > 0 ? round($totalTime / $count, 2) : 0,
                    'task_count' => $count,
                ];
            })
            ->values()
            ->toArray();

        return $taskTypes;
    }

    /**
     * Форматировать время
     */
    private function formatTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} сек";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($remainingSeconds === 0) {
            return "{$minutes} мин";
        }

        return "{$minutes} мин {$remainingSeconds} сек";
    }

    // ============================================================
    // 🔄 BOOT / EVENTS
    // ============================================================

    protected static function boot()
    {
        parent::boot();

        // При создании устанавливаем начальный статус
        static::creating(function ($progress) {
            if (empty($progress->status)) {
                $progress->status = self::STATUS_NOT_STARTED;
            }
            if (empty($progress->progress_percentage)) {
                $progress->progress_percentage = 0;
            }
        });

        // При обновлении статуса
        static::updating(function ($progress) {
            // Если прогресс стал 100%, но статус не завершен
            if ($progress->progress_percentage >= 100 && $progress->status !== self::STATUS_COMPLETED) {
                $progress->status = self::STATUS_COMPLETED;
                $progress->completed_at = now();
            }
        });
    }

    // ============================================================
    // 🎨 ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ
    // ============================================================

    /**
     * Получить прогресс для виджета
     */
    public function getWidgetData(): array
    {
        return [
            'lesson_name' => $this->lesson?->name,
            'progress' => $this->progress_percentage,
            'progress_formatted' => $this->progress_formatted,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'completed_tasks' => $this->completed_tasks,
            'total_tasks' => $this->total_tasks,
            'remaining_tasks' => $this->remaining_tasks,
            'time_spent' => $this->time_spent_formatted,
        ];
    }

    /**
     * Проверить, есть ли активный прогресс
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS
            || $this->status === self::STATUS_NOT_STARTED;
    }

    /**
     * Получить рекомендации по улучшению
     */
    public function getRecommendations(): array
    {
        $recommendations = [];

        if ($this->is_failed) {
            $recommendations[] = [
                'type' => 'retry',
                'message' => 'Попробуйте пройти урок еще раз',
                'action' => 'reset',
            ];
        }

        if ($this->time_spent_seconds > 0) {
            $avgTimePerTask = $this->time_spent_seconds / max(1, $this->total_tasks);
            if ($avgTimePerTask > 120) {
                $recommendations[] = [
                    'type' => 'speed',
                    'message' => 'Вы тратите больше времени на задания. Попробуйте работать быстрее.',
                ];
            }
        }

        if ($this->progress_percentage > 0 && $this->progress_percentage < 50) {
            $recommendations[] = [
                'type' => 'encouragement',
                'message' => 'Вы уже начали урок! Продолжайте в том же духе! 💪',
            ];
        }

        if ($this->progress_percentage >= 50 && $this->progress_percentage < 100) {
            $recommendations[] = [
                'type' => 'encouragement',
                'message' => 'Вы почти закончили! Осталось совсем немного! 🎯',
            ];
        }

        return $recommendations;
    }

    /**
     * Получить прогресс в виде JSON для charts
     */
    public function getChartData(): array
    {
        $lesson = $this->lesson;
        if (!$lesson) {
            return [];
        }

        $tasks = $lesson->tasks()->orderBy('sort_order')->get();
        $data = [];

        foreach ($tasks as $task) {
            $progress = $task->userProgress()
                ->where('user_id', $this->user_id)
                ->first();

            $data[] = [
                'task_id' => $task->id,
                'task_title' => $task->title ?? 'Задание ' . $task->sort_order,
                'status' => $progress?->status ?? UserTaskProgress::STATUS_PENDING,
                'attempts' => $progress?->attempts_count ?? 0,
                'score' => $progress?->score ?? 0,
                'time_spent' => $progress?->time_spent_seconds ?? 0,
                'is_completed' => $progress?->is_completed ?? false,
            ];
        }

        return $data;
    }
}
