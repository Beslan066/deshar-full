<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPieceProgress extends Model
{
    use HasFactory;

    protected $table = 'user_piece_progress';

    protected $fillable = [
        'user_id',
        'piece_id',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at',
        'last_activity_at',
        'time_spent_seconds',
        'metadata',
    ];

    protected $casts = [
        'progress_percentage' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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
     * Связь с разделом
     */
    public function piece()
    {
        return $this->belongsTo(EducationModulePiece::class, 'piece_id');
    }

    /**
     * Связь с модулем (через раздел)
     */
    public function module()
    {
        return $this->hasOneThrough(
            EducationModule::class,
            EducationModulePiece::class,
            'id',
            'id',
            'piece_id',
            'education_module_id'
        );
    }

    // ============================================================
    // 📊 СКОУПЫ (Scopes)
    // ============================================================

    /**
     * Только завершенные разделы
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
     * Фильтр по разделу
     */
    public function scopeForPiece($query, int $pieceId)
    {
        return $query->where('piece_id', $pieceId);
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
     * Завершен ли раздел
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * В процессе ли раздел
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Не начат ли раздел
     */
    public function getIsNotStartedAttribute(): bool
    {
        return $this->status === self::STATUS_NOT_STARTED;
    }

    /**
     * Провален ли раздел
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
     * Получить общее количество уроков в разделе
     */
    public function getTotalLessonsAttribute(): int
    {
        return $this->piece?->lessons()->count() ?? 0;
    }

    /**
     * Получить количество завершенных уроков
     */
    public function getCompletedLessonsAttribute(): int
    {
        if (!$this->piece) {
            return 0;
        }

        return $this->piece->lessons()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserLessonProgress::STATUS_COMPLETED);
            })
            ->count();
    }

    /**
     * Получить количество оставшихся уроков
     */
    public function getRemainingLessonsAttribute(): int
    {
        return $this->total_lessons - $this->completed_lessons;
    }

    /**
     * Получить XP, заработанный в разделе
     */
    public function getEarnedXpAttribute(): int
    {
        if (!$this->piece) {
            return 0;
        }

        return $this->piece->lessons()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserLessonProgress::STATUS_COMPLETED);
            })
            ->sum('xp_reward');
    }

    /**
     * Получить максимальный XP в разделе
     */
    public function getMaxXpAttribute(): int
    {
        return $this->piece?->lessons()->sum('xp_reward') ?? 0;
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

    // ============================================================
    // 🔧 МЕТОДЫ (Methods)
    // ============================================================

    /**
     * Обновить прогресс раздела
     */
    public function updateProgress(): void
    {
        $piece = $this->piece;
        if (!$piece) {
            return;
        }

        $totalLessons = $piece->lessons()->count();

        if ($totalLessons === 0) {
            $this->progress_percentage = 100;
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
            $this->save();
            return;
        }

        $completedLessons = $piece->lessons()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserLessonProgress::STATUS_COMPLETED);
            })
            ->count();

        $this->progress_percentage = round(($completedLessons / $totalLessons) * 100, 2);

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

        // Обновляем прогресс модуля
        $this->updateModuleProgress();
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

        // Создаем или обновляем прогресс модуля
        $moduleProgress = UserModuleProgress::firstOrCreate([
            'user_id' => $this->user_id,
            'module_id' => $module->id,
        ]);

        $moduleProgress->updateProgress();
    }

    /**
     * Отметить раздел как завершенный
     */
    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->progress_percentage = 100;
        $this->completed_at = now();
        $this->last_activity_at = now();
        $this->save();

        // Бонус XP за завершение раздела
        $bonusXp = 50; // Можно настроить
        $this->user->increment('points', $bonusXp);

        // Обновляем прогресс модуля
        $this->updateModuleProgress();
    }

    /**
     * Отметить раздел как проваленный
     */
    public function markFailed(): void
    {
        $this->status = self::STATUS_FAILED;
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Сбросить прогресс раздела
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

        // Сбрасываем прогресс всех уроков в разделе
        $this->piece?->lessons()->each(function ($lesson) {
            $lessonProgress = UserLessonProgress::where([
                'user_id' => $this->user_id,
                'lesson_id' => $lesson->id,
            ])->first();

            if ($lessonProgress) {
                $lessonProgress->reset();
            }
        });

        // Обновляем прогресс модуля
        $this->updateModuleProgress();
    }

    /**
     * Обновить время, проведенное в разделе
     */
    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Проверить, доступен ли следующий раздел
     */
    public function isNextPieceAvailable(): bool
    {
        if (!$this->piece) {
            return false;
        }

        // Проверяем, завершен ли текущий раздел
        if (!$this->is_completed) {
            return false;
        }

        // Проверяем, есть ли следующий раздел
        $nextPiece = $this->piece->educationModule?->pieces()
            ->where('sort_order', '>', $this->piece->sort_order)
            ->first();

        return $nextPiece !== null;
    }

    /**
     * Получить следующий раздел
     */
    public function getNextPiece(): ?EducationModulePiece
    {
        if (!$this->piece) {
            return null;
        }

        return $this->piece->educationModule?->pieces()
            ->where('sort_order', '>', $this->piece->sort_order)
            ->first();
    }

    /**
     * Получить прогресс в виде массива для API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_name' => $this->status_with_emoji,
            'status_color' => $this->status_color,
            'progress' => $this->progress_percentage,
            'progress_formatted' => $this->progress_formatted,
            'completed_lessons' => $this->completed_lessons,
            'total_lessons' => $this->total_lessons,
            'remaining_lessons' => $this->remaining_lessons,
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
            'piece' => $this->piece?->only(['id', 'name', 'fon']),
        ];
    }

    // ============================================================
    // 📊 СТАТИСТИКА
    // ============================================================

    /**
     * Получить детальную статистику по разделу
     */
    public function getDetailedStats(): array
    {
        $piece = $this->piece;
        if (!$piece) {
            return [];
        }

        $lessons = $piece->lessons()->orderBy('sort_order')->get();
        $lessonsProgress = [];

        foreach ($lessons as $lesson) {
            $progress = $lesson->userProgress()
                ->where('user_id', $this->user_id)
                ->first();

            $lessonsProgress[] = [
                'lesson_id' => $lesson->id,
                'name' => $lesson->name,
                'status' => $progress?->status ?? UserLessonProgress::STATUS_NOT_STARTED,
                'progress' => $progress?->progress_percentage ?? 0,
                'is_completed' => $progress?->is_completed ?? false,
                'tasks_count' => $lesson->tasks()->count(),
                'completed_tasks' => $lesson->tasks()
                    ->whereHas('userProgress', function ($query) {
                        $query->where('user_id', $this->user_id)
                            ->where('status', UserTaskProgress::STATUS_COMPLETED);
                    })
                    ->count(),
            ];
        }

        return [
            'piece' => [
                'id' => $piece->id,
                'name' => $piece->name,
                'total_lessons' => $this->total_lessons,
            ],
            'progress' => $this->toApiArray(),
            'lessons' => $lessonsProgress,
            'statistics' => [
                'average_time_per_lesson' => $this->total_lessons > 0
                    ? round($this->time_spent_seconds / $this->total_lessons, 2)
                    : 0,
                'lessons_completed_rate' => $this->total_lessons > 0
                    ? round(($this->completed_lessons / $this->total_lessons) * 100, 2)
                    : 0,
                'xp_efficiency' => $this->max_xp > 0
                    ? round(($this->earned_xp / $this->max_xp) * 100, 2)
                    : 0,
            ],
        ];
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
            'piece_name' => $this->piece?->name,
            'progress' => $this->progress_percentage,
            'progress_formatted' => $this->progress_formatted,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'completed_lessons' => $this->completed_lessons,
            'total_lessons' => $this->total_lessons,
            'remaining_lessons' => $this->remaining_lessons,
            'time_spent' => $this->time_spent_formatted,
            'earned_xp' => $this->earned_xp,
            'max_xp' => $this->max_xp,
        ];
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
                'message' => 'Попробуйте пройти раздел еще раз',
                'action' => 'reset',
            ];
        }

        if ($this->time_spent_seconds > 0) {
            $avgTimePerLesson = $this->time_spent_seconds / max(1, $this->total_lessons);
            if ($avgTimePerLesson > 300) {
                $recommendations[] = [
                    'type' => 'speed',
                    'message' => 'Вы тратите много времени на уроки. Попробуйте работать быстрее.',
                ];
            }
        }

        if ($this->progress_percentage > 0 && $this->progress_percentage < 30) {
            $recommendations[] = [
                'type' => 'encouragement',
                'message' => 'Вы только начали раздел! Продолжайте в том же духе! 💪',
            ];
        }

        if ($this->progress_percentage >= 30 && $this->progress_percentage < 70) {
            $recommendations[] = [
                'type' => 'encouragement',
                'message' => 'Вы на полпути! Отлично прогрессируете! 🚀',
            ];
        }

        if ($this->progress_percentage >= 70 && $this->progress_percentage < 100) {
            $recommendations[] = [
                'type' => 'encouragement',
                'message' => 'Почти финиш! Осталось совсем немного! 🎯',
            ];
        }

        return $recommendations;
    }
}
