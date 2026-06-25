<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EducationModulePiece extends Model
{
    protected $table = 'education_module_pieces';

    protected $fillable = [
        'name',
        'slug',
        'fon',
        'education_module_id',
        'description',
        'is_published',
        'is_required',
        'sort_order',
        'xp_reward',
        'estimated_time',
        'metadata',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'xp_reward' => 'integer',
        'estimated_time' => 'integer',
        'metadata' => 'array',
    ];

    // ============================================================
    // 🔗 СВЯЗИ (Relationships)
    // ============================================================

    /**
     * Связь с модулем
     * Внешний ключ: education_module_id
     */
    public function educationModule()
    {
        return $this->belongsTo(EducationModule::class, 'education_module_id');
    }

    /**
     * Связь с уроками
     * Внешний ключ в таблице lessons: piece_id
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'piece_id')->orderBy('sort_order');
    }

    /**
     * Связь с заданиями (через уроки)
     *
     * hasManyThrough(Task::class,            // Конечная модель
     *                Lesson::class,           // Промежуточная модель
     *                'piece_id',              // Внешний ключ в промежуточной модели (lessons)
     *                'lesson_id',             // Внешний ключ в конечной модели (tasks)
     *                'id',                    // Локальный ключ в текущей модели
     *                'id'                     // Локальный ключ в промежуточной модели
     *               )
     */
    public function tasks()
    {
        return $this->hasManyThrough(
            Task::class,
            Lesson::class,
            'piece_id',    // Внешний ключ в таблице lessons (связь с education_module_pieces)
            'lesson_id',   // Внешний ключ в таблице tasks (связь с lessons)
            'id',          // Локальный ключ в таблице education_module_pieces
            'id'           // Локальный ключ в таблице lessons
        );
    }

    // ============================================================
    // 📊 СКОУПЫ (Scopes)
    // ============================================================

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeInModule($query, int $moduleId)
    {
        return $query->where('education_module_id', $moduleId);
    }

    // ============================================================
    // 🎯 АКСЕССОРЫ (Accessors)
    // ============================================================

    public function getTotalLessonsAttribute(): int
    {
        return $this->lessons()->count();
    }

    public function getTotalTasksAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Получить прогресс для текущего пользователя
     */
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

    /**
     * Проверить, завершен ли раздел текущим пользователем
     */
    public function getIsCompletedAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return true;
        }

        $completedTasks = $this->tasks()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();

        return $completedTasks === $totalTasks;
    }

    // ============================================================
    // 🔧 МЕТОДЫ
    // ============================================================

    /**
     * Получить следующий невыполненный урок
     */
    public function getNextLesson(): ?Lesson
    {
        if (!auth()->check()) {
            return $this->lessons()->first();
        }

        $completedLessonIds = $this->lessons()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', UserLessonProgress::STATUS_COMPLETED);
            })
            ->pluck('id')
            ->toArray();

        return $this->lessons()
            ->whereNotIn('id', $completedLessonIds)
            ->first();
    }

    /**
     * Получить прогресс для конкретного пользователя
     */
    public function getProgressForUser(int $userId): float
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()
            ->whereHas('userProgress', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Получить данные для API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'fon' => $this->fon,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_published' => $this->is_published,
            'is_required' => $this->is_required,
            'xp_reward' => $this->xp_reward,
            'estimated_time' => $this->estimated_time,
            'total_lessons' => $this->total_lessons,
            'total_tasks' => $this->total_tasks,
            'progress' => $this->progress,
            'is_completed' => $this->is_completed,
        ];
    }

    // ============================================================
    // 🔄 BOOT / EVENTS
    // ============================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($piece) {
            if (empty($piece->slug)) {
                $piece->slug = Str::slug($piece->name);
            }
            if (empty($piece->sort_order)) {
                $piece->sort_order = 0;
            }
            if (empty($piece->xp_reward)) {
                $piece->xp_reward = 10;
            }
        });

        // При обновлении - если изменилось имя, обновляем slug
        static::updating(function ($piece) {
            if ($piece->isDirty('name') && empty($piece->slug)) {
                $piece->slug = Str::slug($piece->name);
            }
        });
    }
}
