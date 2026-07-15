<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTaskProgress extends Model
{
    protected $table = 'user_task_progress';

    protected $fillable = [
        'user_id',
        'task_id',
        'status',
        'attempts_count',
        'score',
        'max_score',
        'answer_history',
        'user_answers',
        'time_spent_seconds',
        'completed_at',
        'last_answer',
        'last_activity_at',
    ];

    protected $casts = [
        'answer_history' => 'array',
        'user_answers' => 'array',
        'last_answer' => 'array', // Каст для JSON
        'attempts_count' => 'integer',
        'score' => 'integer',
        'max_score' => 'integer',
        'time_spent_seconds' => 'integer',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Константы статусов
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_IN_PROGRESS = 'in_progress';

    // 🔗 Связи
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // 📊 Скоупы
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // 🎯 Аксессоры
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getAttemptsLeftAttribute(): int
    {
        return max(0, ($this->task->max_attempts ?? 3) - $this->attempts_count);
    }

    public function getLastAnswerDecodedAttribute()
    {
        return $this->last_answer;
    }

    // 🔧 Методы
    public function markCompleted(int $score = null): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();

        if ($score !== null) {
            $this->score = $score;
        }

        $this->save();

        // Обновляем XP пользователя
        $xpReward = $this->task->xp_reward ?? 10;
        $this->user->increment('points', $xpReward);
        $this->user->updateStreak();

        // Обновляем прогресс урока
        $this->updateLessonProgress();
    }

    public function markFailed(): void
    {
        $this->status = self::STATUS_FAILED;
        $this->save();
    }

    public function addAttempt(array $answer, bool $isCorrect): void
    {
        $this->attempts_count++;

        $history = $this->answer_history ?? [];
        $history[] = [
            'answer' => $answer,
            'is_correct' => $isCorrect,
            'timestamp' => now()->toISOString(),
        ];
        $this->answer_history = $history;
        $this->last_answer = $answer; // Сохраняем последний ответ

        if (!$isCorrect && $this->attempts_count >= ($this->task->max_attempts ?? 3)) {
            $this->markFailed();
        }

        $this->save();
    }

    public function toApiArray(): array
    {
        return [
            'status' => $this->status,
            'is_completed' => $this->is_completed,
            'attempts' => $this->attempts_count,
            'attempts_left' => $this->attempts_left,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'time_spent' => $this->time_spent_seconds,
            'last_answer' => $this->last_answer,
            'started_at' => $this->created_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }

    private function updateLessonProgress(): void
    {
        $lesson = $this->task->lesson;
        if (!$lesson) {
            return;
        }

        $completedTasks = $lesson->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', self::STATUS_COMPLETED);
            })
            ->count();

        $totalRequired = $lesson->tasks()->where('is_required', true)->count();

        UserLessonProgress::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'lesson_id' => $lesson->id,
            ],
            [
                'progress_percentage' => $totalRequired > 0
                    ? round(($completedTasks / $totalRequired) * 100, 2)
                    : 100,
                'status' => $totalRequired > 0 && $completedTasks >= $totalRequired
                    ? UserLessonProgress::STATUS_COMPLETED
                    : UserLessonProgress::STATUS_IN_PROGRESS,
                'completed_at' => $totalRequired > 0 && $completedTasks >= $totalRequired
                    ? now()
                    : null,
            ]
        );
    }
}
