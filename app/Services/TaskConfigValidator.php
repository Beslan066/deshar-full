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

        if (empty($rules)) {
            return $config;
        }

        $validator = validator($config, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $config;
    }

    /**
     * Получить правила валидации для типа задания
     */
   public function getValidationRules(string $type): array
{
    return match ($type) {
        'accent_trainer' => [
            'variants' => 'required|array|min:2',
            'variants.*.id' => 'required|string',
            'variants.*.letter' => 'required|string|max:5',
            'correct_variant_ids' => 'required|array|min:1',
            'correct_variant_ids.*' => 'required|string',
            'shuffle_variants' => 'boolean',
        ],
        'single_select_image_quiz' => [
            'variants' => 'required|array|min:2',
            'variants.*.id' => 'required|string',
            'variants.*.imageUrl' => 'required|string|max:255',
            'correct_variant_id' => 'required|string',
            'shuffle_variants' => 'boolean',
        ],
        'fix_sentence' => [
            'sentence' => 'required|string|max:500',
            'words' => 'required|array|min:1',
            'words.*' => 'required|string|max:255',
            'correctAnswer' => 'required|string|max:255',
        ],
        'alphabetic_sorter' => [
            'slots' => 'required|array|min:1',
            'slots.*.id' => 'required|string',
            'slots.*.correctValue' => 'required|string|max:255',
            'slots.*.slotTitle' => 'required|string|max:255',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|string',
            'variants.*.value' => 'required|string|max:255',
        ],
        'category_matcher' => [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.label' => 'required|string|max:255',
            'items.*.correct' => 'required|string',
            'items.*.color' => 'required|string|max:50',
            'categories' => 'required|array|min:1',
            'categories.*.id' => 'required|string',
            'categories.*.label' => 'required|string|max:255',
            'categories.*.color' => 'required|string|max:50',
        ],
        'colorize_words' => [
            'tools' => 'required|array|min:1',
            'tools.*.type' => 'required|string|max:255',
            'tools.*.toolName' => 'required|string|max:255',
            'tools.*.toolColor' => 'nullable|string|max:50',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|string',
            'variants.*.content' => 'required|string|max:255',
            'variants.*.correctColor' => 'required|string|max:50',
        ],
        'conclusion' => [
            'data' => 'required|array|min:1',
            'data.*.id' => 'required|string',
            'data.*.value' => 'required|string|max:1000',
            'data.*.completed' => 'boolean',
            'data.*.variants' => 'required|array|min:1',
            'data.*.variants.*.id' => 'required|string',
            'data.*.variants.*.value' => 'required|string|max:255',
            'data.*.slots' => 'required|array|min:1',
            'data.*.slots.*.id' => 'required|string',
            'data.*.slots.*.current' => 'nullable|string|max:255',
            'data.*.slots.*.correct' => 'required|string|max:255',
        ],
        'delete_extra_letter' => [
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|string',
            'variants.*.letter' => 'required|string|max:5',
            'correctVariantIds' => 'required|array|min:1',
            'correctVariantIds.*' => 'required|string',
        ],
        'drop_word_to_text' => [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.content' => 'required|string|max:1000',
            'items.*.correctVariantId' => 'required|string',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|string',
            'variants.*.value' => 'required|string|max:255',
        ],
        'reorder_items' => [
            'data' => 'required|array|min:2',
            'data.*.id' => 'required|string',
            'data.*.content' => 'required|string|max:255',
            'correctOrderIds' => 'required|array|min:2',
            'correctOrderIds.*' => 'required|string',
        ],
        'multi_quiz' => [
            'variants' => 'required|array|min:2',
            'variants.*.id' => 'required|string',
            'variants.*.itemNumber' => 'required|string',
            'variants.*.title' => 'required|string|max:255',
            'correctVariantIds' => 'required|array|min:1',
            'correctVariantIds.*' => 'required|string',
        ],
        'sequence_builder' => [
            'slots' => 'required|array|min:2',
            'slots.*.slotId' => 'required|string',
            'slots.*.content' => 'required|string|max:255',
            'slots.*.correctValue' => 'required|string|max:1000',
            'variants' => 'required|array|min:2',
            'variants.*.id' => 'required|string',
            'variants.*.content' => 'required|string|max:1000',
        ],
        'single_quiz' => [
            'variants' => 'required|array|min:2',
            'variants.*.id' => 'required|string',
            'variants.*.itemNumber' => 'required|string',
            'variants.*.title' => 'required|string|max:255',
            'correctVariantId' => 'required|string',
        ],
        'word_by_image' => [
            'id' => 'required|string',
            'correctAnswer' => 'required|string|max:255',
            'imageUrl' => 'required|string|max:255',
            'availableLetters' => 'required|array|min:1',
            'availableLetters.*.id' => 'required|string',
            'availableLetters.*.letter' => 'required|string|max:5',
        ],
        'word_picker' => [
            'text' => 'required|string|max:1000',
            'correctValues' => 'required|array|min:1',
            'correctValues.*' => 'required|string|max:255',
        ],
        'drag_word_to_pocket',
        'drop_word_to_image',
        'phrase_image_matcher' => [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.imageUrl' => 'required|string|max:255',
            'items.*.correctVariantId' => 'required|string',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|string',
            'variants.*.value' => 'required|string|max:255',
        ],
        default => [],
    };
}
}
