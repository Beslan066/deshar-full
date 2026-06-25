<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'description',
        'default_config',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_config' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // 🔗 Связи
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // 📊 Скоупы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // 🎯 Аксессоры
    public function getTaskCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    // 🔧 Методы
    public function getDefaultConfig(): array
    {
        return $this->default_config ?? [];
    }

    public function getValidationRules(): array
    {
        // Возвращает правила валидации для этого типа
        // Используется в TaskConfigValidator
        return match ($this->slug) {
            'choose_one' => [
                'question' => 'required|string|max:500',
                'options' => 'required|array|size:4',
                'options.*.text' => 'required|string|max:255',
                'options.*.is_correct' => 'required|boolean',
            ],
            // ... остальные типы
            default => [],
        };
    }

    // Красивое название для отображения
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->icon ? " {$this->icon}" : '');
    }
}
