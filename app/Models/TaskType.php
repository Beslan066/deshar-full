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

    /**
     * Получить правила валидации для типа задания
     * Используется в TaskConfigValidator
     */
    public function getValidationRules(): array
    {
        return match ($this->slug) {
            // 1. Выбери один из 4
            'choose_one' => [
                'question' => 'required|string|max:500',
                'options' => 'required|array|size:4',
                'options.*.id' => 'required|string',
                'options.*.text' => 'required|string|max:255',
                'options.*.is_correct' => 'required|boolean',
                'shuffle_options' => 'boolean',
                'explanation' => 'nullable|string',
            ],

            // 2. Выбери 3 из 6
            'choose_three' => [
                'question' => 'required|string|max:500',
                'options' => 'required|array|size:6',
                'options.*.id' => 'required|string',
                'options.*.text' => 'required|string|max:255',
                'options.*.is_correct' => 'required|boolean',
                'min_select' => 'integer|min:1',
                'max_select' => 'integer|min:1',
                'shuffle_options' => 'boolean',
            ],

            // 3. Сопоставь с изображениями
            'match_images' => [
                'pairs' => 'required|array|min:2',
                'pairs.*.id' => 'required|integer',
                'pairs.*.text' => 'required|string|max:255',
                'pairs.*.image' => 'required|string|max:255',
                'pairs.*.correct_match' => 'required|string|max:255',
                'shuffle_pairs' => 'boolean',
            ],

            // 4. Собери слово из букв
            'build_word' => [
                'image' => 'nullable|string|max:255',
                'correct_word' => 'required|string|max:255',
                'letters' => 'required|array|min:2',
                'letters.*' => 'string|max:1',
                'extra_letters' => 'array',
                'hint' => 'nullable|string',
                'shuffle_letters' => 'boolean',
            ],

            // 5. Поставь ударение
            'stress_mark' => [
                'word' => 'required|string|max:255',
                'letters' => 'required|array|min:1',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.is_stressed' => 'boolean',
                'letters.*.position' => 'required|integer|min:0',
                'correct_index' => 'required|integer|min:0',
            ],

            // 6. Перемести слова
            'drag_drop_text' => [
                'sentences' => 'required|array|min:2',
                'sentences.*.id' => 'required|integer',
                'sentences.*.text' => 'required|string|max:500',
                'sentences.*.blank_position' => 'required|integer|min:0',
                'sentences.*.correct_word' => 'required|string|max:255',
                'words' => 'required|array|min:2',
                'words.*' => 'string|max:255',
                'extra_words' => 'array',
                'shuffle_words' => 'boolean',
            ],

            // 7. Расставь части истории
            'story_order' => [
                'parts' => 'required|array|min:2',
                'parts.*.id' => 'required|integer',
                'parts.*.text' => 'required|string|max:1000',
                'parts.*.correct_order' => 'required|integer|min:1',
                'shuffle_parts' => 'boolean',
                'show_numbers' => 'boolean',
            ],

            // 8. Исправь слово
            'fix_word' => [
                'sentence' => 'required|string|max:500',
                'wrong_word' => 'required|string|max:255',
                'correct_forms' => 'required|array|min:2',
                'correct_forms.*' => 'string|max:255',
                'correct_form' => 'required|string|max:255',
                'hint' => 'nullable|string',
            ],

            // 9. Сопоставь цвета с категориями
            'color_categories' => [
                'items' => 'required|array|min:2',
                'items.*.id' => 'required|integer',
                'items.*.text' => 'required|string|max:255',
                'items.*.category' => 'required|string|max:255',
                'categories' => 'required|array|min:2',
                'categories.*.id' => 'required|string',
                'categories.*.name' => 'required|string|max:255',
                'categories.*.color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
                'shuffle_items' => 'boolean',
            ],

            // 10. Расставь буквы
            'alphabet_letters' => [
                'letters' => 'required|array|min:2',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.correct_position' => 'required|integer|min:0',
                'shuffled_letters' => 'required|array',
                'shuffled_letters.*' => 'string|max:1',
                'alphabet' => 'nullable|string',
            ],

            // 11. Расставь изображения
            'alphabet_images' => [
                'items' => 'required|array|min:2',
                'items.*.id' => 'required|integer',
                'items.*.name' => 'required|string|max:255',
                'items.*.image' => 'required|string|max:255',
                'items.*.correct_order' => 'required|integer|min:0',
                'shuffled_items' => 'required|array',
                'shuffled_items.*' => 'integer',
                'show_names' => 'boolean',
            ],

            // 12. Расставь слова
            'alphabet_words' => [
                'words' => 'required|array|min:2',
                'words.*.id' => 'required|integer',
                'words.*.text' => 'required|string|max:255',
                'words.*.correct_position' => 'required|integer|min:0',
                'shuffled_words' => 'required|array',
                'shuffled_words.*' => 'integer',
                'alphabet' => 'nullable|string',
            ],

            // 13. Соедини буквы
            'connect_letters' => [
                'letters' => 'required|array|min:2',
                'letters.*.id' => 'required|integer',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.position' => 'required|array',
                'letters.*.position.x' => 'required|integer',
                'letters.*.position.y' => 'required|integer',
                'correct_order' => 'required|array',
                'correct_order.*' => 'string',
                'alphabet' => 'nullable|string',
            ],

            // 14. Составь слово по картинке
            'word_from_image' => [
                'image' => 'required|string|max:255',
                'correct_word' => 'required|string|max:255',
                'letters' => 'required|array|min:2',
                'letters.*' => 'string|max:1',
                'extra_letters' => 'array',
                'hint' => 'nullable|string',
                'shuffle_letters' => 'boolean',
            ],

            // 15. Найди по признаку
            'find_by_rule' => [
                'words' => 'required|array|min:2',
                'words.*.id' => 'required|integer',
                'words.*.text' => 'required|string|max:255',
                'words.*.is_correct' => 'required|boolean',
                'rule' => 'required|array',
                'rule.type' => 'required|string|max:255',
                'rule.description' => 'required|string|max:500',
                'rule.example' => 'nullable|string',
                'min_select' => 'integer|min:1',
                'shuffle_words' => 'boolean',
            ],

            // 16. Найди лишнюю букву
            'find_extra_letter' => [
                'image' => 'nullable|string|max:255',
                'word' => 'required|string|max:255',
                'letters' => 'required|array|min:2',
                'letters.*.id' => 'required|integer',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.is_extra' => 'required|boolean',
                'extra_letter' => 'required|string|max:1',
                'correct_index' => 'required|integer|min:0',
                'hint' => 'nullable|string',
            ],

            // 17. Соедини с категорией
            'connect_category' => [
                'items' => 'required|array|min:2',
                'items.*.id' => 'required|integer',
                'items.*.word' => 'required|string|max:255',
                'items.*.category' => 'required|string|max:255',
                'categories' => 'required|array|min:2',
                'categories.*.id' => 'required|string',
                'categories.*.name' => 'required|string|max:255',
                'categories.*.color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
                'shuffle_items' => 'boolean',
                'line_colors' => 'array',
                'line_colors.*' => 'string|regex:/^#[0-9A-F]{6}$/i',
            ],

            // 18. Перетащи к картинке
            'drag_to_image' => [
                'pairs' => 'required|array|min:2',
                'pairs.*.id' => 'required|integer',
                'pairs.*.word' => 'required|string|max:255',
                'pairs.*.image' => 'required|string|max:255',
                'shuffle_words' => 'boolean',
                'shuffle_images' => 'boolean',
            ],

            // 19. Найди по условию
            'find_by_condition' => [
                'images' => 'required|array|min:2',
                'images.*.id' => 'required|integer',
                'images.*.url' => 'required|string|max:255',
                'images.*.alt' => 'required|string|max:255',
                'condition' => 'required|array',
                'condition.text' => 'required|string|max:500',
                'condition.type' => 'required|string|max:255',
                'condition.correct_indices' => 'required|array',
                'condition.correct_indices.*' => 'integer',
                'min_select' => 'integer|min:1',
                'max_select' => 'integer|min:1',
            ],

            // 20. Сопоставь с поведением
            'match_behavior' => [
                'items' => 'required|array|min:2',
                'items.*.id' => 'required|integer',
                'items.*.situation' => 'required|string|max:500',
                'items.*.behavior' => 'required|string|max:255',
                'items.*.image' => 'nullable|string|max:255',
                'behaviors' => 'required|array|min:2',
                'behaviors.*' => 'string|max:255',
                'shuffle_items' => 'boolean',
            ],

            // 21. Составь диалог
            'build_dialogue' => [
                'dialogues' => 'required|array|min:2',
                'dialogues.*.id' => 'required|integer',
                'dialogues.*.speaker' => 'required|string|max:255',
                'dialogues.*.text' => 'required|string|max:500',
                'dialogues.*.correct_order' => 'required|integer|min:1',
                'options' => 'required|array|min:4',
                'options.*.id' => 'required|integer',
                'options.*.text' => 'required|string|max:500',
                'shuffle_options' => 'boolean',
                'show_speakers' => 'boolean',
            ],

            // Сопоставление пар (дополнительный тип)
            'match_pairs' => [
                'pairs' => 'required|array|min:2',
                'pairs.*.left' => 'required|string|max:255',
                'pairs.*.right' => 'required|string|max:255',
                'pairs.*.image' => 'nullable|string|max:255',
                'shuffle_pairs' => 'boolean',
                'time_limit' => 'nullable|integer|min:0',
            ],

            default => [],
        };
    }

    // Красивое название для отображения
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->icon ? " {$this->icon}" : '');
    }
}
