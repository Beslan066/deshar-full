<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class TaskConfigValidator
{
    /**
     * Валидировать конфиг задания
     */
    public function validate(array $config, string $typeSlug): array
    {
        $rules = $this->getValidationRules($typeSlug);
        $validator = validator($config, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $config;
    }

    /**
     * Получить правила валидации для типа задания
     */
    private function getValidationRules(string $type): array
    {
        return match ($type) {
            'choose_one' => [
                'question' => 'required|string|max:500',
                'options' => 'required|array|size:4',
                'options.*.id' => 'required|string',
                'options.*.text' => 'required|string|max:255',
                'options.*.is_correct' => 'required|boolean',
                'shuffle_options' => 'boolean',
                'explanation' => 'nullable|string',
            ],
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
            'match_images' => [
                'pairs' => 'required|array|min:2',
                'pairs.*.id' => 'required|integer',
                'pairs.*.text' => 'required|string|max:255',
                'pairs.*.image' => 'required|string|max:255',
                'pairs.*.correct_match' => 'required|string|max:255',
                'shuffle_pairs' => 'boolean',
            ],
            'build_word' => [
                'image' => 'nullable|string|max:255',
                'correct_word' => 'required|string|max:255',
                'letters' => 'required|array|min:2',
                'letters.*' => 'string|max:1',
                'extra_letters' => 'array',
                'hint' => 'nullable|string',
                'shuffle_letters' => 'boolean',
            ],
            'stress_mark' => [
                'word' => 'required|string|max:255',
                'letters' => 'required|array|min:1',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.is_stressed' => 'boolean',
                'letters.*.position' => 'required|integer|min:0',
                'correct_index' => 'required|integer|min:0',
            ],
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
            'story_order' => [
                'parts' => 'required|array|min:2',
                'parts.*.id' => 'required|integer',
                'parts.*.text' => 'required|string|max:1000',
                'parts.*.correct_order' => 'required|integer|min:1',
                'shuffle_parts' => 'boolean',
                'show_numbers' => 'boolean',
            ],
            'fix_word' => [
                'sentence' => 'required|string|max:500',
                'wrong_word' => 'required|string|max:255',
                'correct_forms' => 'required|array|min:2',
                'correct_forms.*' => 'string|max:255',
                'correct_form' => 'required|string|max:255',
                'hint' => 'nullable|string',
            ],
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
            'alphabet_letters' => [
                'letters' => 'required|array|min:2',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.correct_position' => 'required|integer|min:0',
                'shuffled_letters' => 'required|array',
                'shuffled_letters.*' => 'string|max:1',
                'alphabet' => 'nullable|string',
            ],
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
            'alphabet_words' => [
                'words' => 'required|array|min:2',
                'words.*.id' => 'required|integer',
                'words.*.text' => 'required|string|max:255',
                'words.*.correct_position' => 'required|integer|min:0',
                'shuffled_words' => 'required|array',
                'shuffled_words.*' => 'integer',
                'alphabet' => 'nullable|string',
            ],
            'connect_letters' => [
                'letters' => 'required|array|min:2',
                'letters.*.id' => 'required|integer',
                'letters.*.letter' => 'required|string|max:1',
                'letters.*.position.x' => 'required|integer',
                'letters.*.position.y' => 'required|integer',
                'correct_order' => 'required|array',
                'correct_order.*' => 'string',
                'alphabet' => 'nullable|string',
            ],
            'word_from_image' => [
                'image' => 'required|string|max:255',
                'correct_word' => 'required|string|max:255',
                'letters' => 'required|array|min:2',
                'letters.*' => 'string|max:1',
                'extra_letters' => 'array',
                'hint' => 'nullable|string',
                'shuffle_letters' => 'boolean',
            ],
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
            'drag_to_image' => [
                'pairs' => 'required|array|min:2',
                'pairs.*.id' => 'required|integer',
                'pairs.*.word' => 'required|string|max:255',
                'pairs.*.image' => 'required|string|max:255',
                'shuffle_words' => 'boolean',
                'shuffle_images' => 'boolean',
            ],
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
            default => [],
        };
    }
}
