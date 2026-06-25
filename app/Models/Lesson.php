<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'piece_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_published',
        'is_required',
        'xp_reward',
        'estimated_time',
        'metadata',
    ];

    // 🔗 Связи
    public function piece()
    {
        return $this->belongsTo(EducationModulePiece::class, 'piece_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('sort_order');
    }

    public function userProgress()
    {
        return $this->hasMany(UserLessonProgress::class);
    }

    // 📊 Скоупы
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // 🎯 Аксессоры
    public function getTotalTasksAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getProgressAttribute(): float
    {
        if (!auth()->check()) {
            return 0;
        }

        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public function getIsCompletedAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $requiredTasks = $this->tasks()->where('is_required', true)->count();
        if ($requiredTasks === 0) {
            return true;
        }

        $completedRequiredTasks = $this->tasks()
            ->where('is_required', true)
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();

        return $completedRequiredTasks === $requiredTasks;
    }

    // 🔧 Методы
    public function getNextTask(): ?Task
    {
        if (!auth()->check()) {
            return $this->tasks()->first();
        }

        $completedTaskIds = $this->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->pluck('id')
            ->toArray();

        return $this->tasks()
            ->whereNotIn('id', $completedTaskIds)
            ->orderBy('sort_order')
            ->first();
    }

    public function isLocked(): bool
    {
        if (!$this->is_published) {
            return true;
        }

        // Проверяем, пройден ли предыдущий урок
        $previousLesson = $this->piece->lessons()
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousLesson && !$previousLesson->is_completed) {
            return true;
        }

        return false;
    }

    public function getXpReward(): int
    {
        return $this->xp_reward ?? 10;
    }

    // Автоматическое создание slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = Str::slug($lesson->name);
            }
        });
    }
}
