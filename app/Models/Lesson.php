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
        'audio',
        'video',
        'image'
    ];

    /*
     * Что хранит metadata
        1. Для уроков (lessons)
        json
        {
            "learning_objectives": ["Уметь читать букву А", "Знать слова на букву А"],
            "keywords": ["Аба", "Алфавит", "Буква"],
            "difficulty_rating": 3,
            "estimated_time": 45,
            "prerequisites": ["Урок 1: Знакомство с алфавитом"],
            "next_lessons": ["Урок 3: Буква Б"],
            "tags": ["алфавит", "буква", "начальный"],
            "teacher_notes": "Обратить внимание на произношение",
            "homework": "Написать 5 слов на букву А",
            "resources": [
                {"type": "pdf", "url": "/resources/lesson2.pdf"},
                {"type": "video", "url": "/videos/lesson2.mp4"}
            ],
            "interactive_elements": ["drag_drop", "choose_one"],
            "bonus_content": "Дополнительный материал для отличников",
            "language_level": "A1",
            "custom_fields": {
                "field1": "value1",
                "field2": "value2"
            }
        }
        🎯 За что отвечает metadata
        Назначение	Описание	Пример
        Цели урока	Чему научится ученик	learning_objectives: ["Уметь читать букву А"]
        Ключевые слова	Основные термины урока	keywords: ["Аба", "Алфавит"]
        Сложность	Рейтинг сложности	difficulty_rating: 3
        Время	Примерное время прохождения	estimated_time: 45
        Связи	Зависимости между уроками	prerequisites: ["Урок 1"], next_lessons: ["Урок 3"]
        Теги	Для поиска и фильтрации	tags: ["алфавит", "буква"]
        Для учителя	Заметки для преподавателя	teacher_notes: "Обратить внимание..."
        Домашнее задание	Задание на дом	homework: "Написать 5 слов"
        Ресурсы	Дополнительные материалы	resources: [{"type": "pdf", "url": "..."}]
        Типы заданий	Какие интерактивы используются	interactive_elements: ["drag_drop"]
        Кастомные поля	Любые дополнительные данные	custom_fields: {...}
        💡 Зачем использовать metadata вместо обычных колонок
        ✅ Плюсы:
        Гибкость — можно добавлять любые поля без миграций

        Независимость — не нужно менять схему БД для новых фич

        Масштабируемость — легко расширять структуру данных

        JSONB индексы — быстрый поиск внутри JSON


     */

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

    // Получить значение из metadata
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    // Установить значение в metadata
    public function setMetadataValue(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    // Получить цели урока
    public function getLearningObjectives(): array
    {
        return $this->getMetadataValue('learning_objectives', []);
    }

    // Получить теги
    public function getTags(): array
    {
        return $this->getMetadataValue('tags', []);
    }

    // Получить ресурсы
    public function getResources(): array
    {
        return $this->getMetadataValue('resources', []);
    }
}
