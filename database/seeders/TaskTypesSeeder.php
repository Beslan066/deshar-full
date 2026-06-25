<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaskType;

class TaskTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'slug' => 'choose_one',
                'name' => 'Выбери один из 4',
                'icon' => 'bx bx-check-circle',
                'description' => 'Выбрать правильный вариант из 4 предложенных',
                'default_config' => [
                    'question' => '',
                    'options' => [
                        ['id' => 'a', 'text' => '', 'is_correct' => false],
                        ['id' => 'b', 'text' => '', 'is_correct' => false],
                        ['id' => 'c', 'text' => '', 'is_correct' => false],
                        ['id' => 'd', 'text' => '', 'is_correct' => false],
                    ],
                    'shuffle_options' => true,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'choose_three',
                'name' => 'Выбери 3 из 6',
                'icon' => 'bx bx-checkbox-checked',
                'description' => 'Выбрать 3 правильных варианта из 6',
                'default_config' => [
                    'question' => '',
                    'options' => [
                        ['id' => 'a', 'text' => '', 'is_correct' => false],
                        ['id' => 'b', 'text' => '', 'is_correct' => false],
                        ['id' => 'c', 'text' => '', 'is_correct' => false],
                        ['id' => 'd', 'text' => '', 'is_correct' => false],
                        ['id' => 'e', 'text' => '', 'is_correct' => false],
                        ['id' => 'f', 'text' => '', 'is_correct' => false],
                    ],
                    'min_select' => 3,
                    'max_select' => 3,
                    'shuffle_options' => true,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'match_images',
                'name' => 'Сопоставь с изображениями',
                'icon' => 'bx bx-images',
                'description' => 'Сопоставить текст с картинками',
                'default_config' => [
                    'pairs' => [
                        ['text' => '', 'image' => '', 'correct_match' => ''],
                    ],
                    'shuffle_pairs' => true,
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'build_word',
                'name' => 'Собери слово из букв',
                'icon' => 'bx bx-puzzle-piece',
                'description' => 'Собрать слово из перемешанных букв',
                'default_config' => [
                    'image' => null,
                    'correct_word' => '',
                    'letters' => [],
                    'extra_letters' => [],
                    'hint' => null,
                    'shuffle_letters' => true,
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'stress_mark',
                'name' => 'Поставь ударение',
                'icon' => 'bx bx-highlight',
                'description' => 'Кликнуть на правильную букву с ударением',
                'default_config' => [
                    'word' => '',
                    'letters' => [],
                    'correct_index' => 0,
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'slug' => 'drag_drop_text',
                'name' => 'Перемести слова',
                'icon' => 'bx bx-move',
                'description' => 'Перетащить слова в пропуски',
                'default_config' => [
                    'sentences' => [],
                    'words' => [],
                    'extra_words' => [],
                    'shuffle_words' => true,
                ],
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'slug' => 'story_order',
                'name' => 'Расставь части истории',
                'icon' => 'bx bx-list-ol',
                'description' => 'Расставить части истории по порядку',
                'default_config' => [
                    'parts' => [],
                    'shuffle_parts' => true,
                    'show_numbers' => false,
                ],
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'slug' => 'fix_word',
                'name' => 'Исправь слово',
                'icon' => 'bx bx-edit-alt',
                'description' => 'Кликать пока не появится правильный вариант',
                'default_config' => [
                    'sentence' => '',
                    'wrong_word' => '',
                    'correct_forms' => [],
                    'correct_form' => '',
                    'hint' => null,
                ],
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'slug' => 'color_categories',
                'name' => 'Сопоставь цвета с категориями',
                'icon' => 'bx bx-palette',
                'description' => 'Сопоставить слова с категориями по цветам',
                'default_config' => [
                    'items' => [],
                    'categories' => [],
                    'shuffle_items' => true,
                ],
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'slug' => 'alphabet_letters',
                'name' => 'Расставь буквы',
                'icon' => 'bx bx-sort-a-z',
                'description' => 'Расставить буквы в алфавитном порядке',
                'default_config' => [
                    'letters' => [],
                    'shuffled_letters' => [],
                    'alphabet' => 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
                ],
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'slug' => 'alphabet_images',
                'name' => 'Расставь изображения',
                'icon' => 'bx bx-sort-a-z',
                'description' => 'Расставить картинки по алфавиту',
                'default_config' => [
                    'items' => [],
                    'shuffled_items' => [],
                    'show_names' => true,
                ],
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'slug' => 'alphabet_words',
                'name' => 'Расставь слова',
                'icon' => 'bx bx-sort-a-z',
                'description' => 'Расставить слова по алфавиту',
                'default_config' => [
                    'words' => [],
                    'shuffled_words' => [],
                    'alphabet' => 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
                ],
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'slug' => 'connect_letters',
                'name' => 'Соедини буквы',
                'icon' => 'bx bx-line-chart',
                'description' => 'Соединить буквы в алфавитном порядке',
                'default_config' => [
                    'letters' => [],
                    'correct_order' => [],
                    'alphabet' => 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
                ],
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'slug' => 'word_from_image',
                'name' => 'Составь слово по картинке',
                'icon' => 'bx bx-image',
                'description' => 'Составить слово по изображению',
                'default_config' => [
                    'image' => '',
                    'correct_word' => '',
                    'letters' => [],
                    'extra_letters' => [],
                    'hint' => null,
                    'shuffle_letters' => true,
                ],
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'slug' => 'find_by_rule',
                'name' => 'Найди по признаку',
                'icon' => 'bx bx-search-alt',
                'description' => 'Найти слова по правилу (твердые/мягкие)',
                'default_config' => [
                    'words' => [],
                    'rule' => ['type' => '', 'description' => '', 'example' => ''],
                    'min_select' => 1,
                    'shuffle_words' => true,
                ],
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'slug' => 'find_extra_letter',
                'name' => 'Найди лишнюю букву',
                'icon' => 'bx bx-x-circle',
                'description' => 'Найти лишнюю букву в слове',
                'default_config' => [
                    'image' => null,
                    'word' => '',
                    'letters' => [],
                    'extra_letter' => '',
                    'correct_index' => 0,
                    'hint' => null,
                ],
                'is_active' => true,
                'sort_order' => 16,
            ],
            [
                'slug' => 'connect_category',
                'name' => 'Соедини с категорией',
                'icon' => 'bx bx-link',
                'description' => 'Соединить слова с категориями',
                'default_config' => [
                    'items' => [],
                    'categories' => [],
                    'shuffle_items' => true,
                    'line_colors' => ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'],
                ],
                'is_active' => true,
                'sort_order' => 17,
            ],
            [
                'slug' => 'drag_to_image',
                'name' => 'Перетащи к картинке',
                'icon' => 'bx bx-drag',
                'description' => 'Перетащить слова к картинкам',
                'default_config' => [
                    'pairs' => [],
                    'shuffle_words' => true,
                    'shuffle_images' => true,
                ],
                'is_active' => true,
                'sort_order' => 18,
            ],
            [
                'slug' => 'find_by_condition',
                'name' => 'Найди по условию',
                'icon' => 'bx bx-filter',
                'description' => 'Найти картинку по условию',
                'default_config' => [
                    'images' => [],
                    'condition' => ['text' => '', 'type' => '', 'correct_indices' => []],
                    'min_select' => 1,
                    'max_select' => 1,
                ],
                'is_active' => true,
                'sort_order' => 19,
            ],
            [
                'slug' => 'match_behavior',
                'name' => 'Сопоставь с поведением',
                'icon' => 'bx bx-user-voice',
                'description' => 'Сопоставить ситуации с поведением',
                'default_config' => [
                    'items' => [],
                    'behaviors' => [],
                    'shuffle_items' => true,
                ],
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'slug' => 'build_dialogue',
                'name' => 'Составь диалог',
                'icon' => 'bx bx-chat',
                'description' => 'Расставить реплики в диалоге',
                'default_config' => [
                    'dialogues' => [],
                    'options' => [],
                    'shuffle_options' => true,
                    'show_speakers' => true,
                ],
                'is_active' => true,
                'sort_order' => 21,
            ],
        ];

        foreach ($types as $type) {
            TaskType::create($type);
        }
    }
}
