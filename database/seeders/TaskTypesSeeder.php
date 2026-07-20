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

            [
                'slug' => 'accent_trainer',
                'name' => 'Тренажер ударений',
                'icon' => 'bx bx-font',
                'description' => 'Выбрать вариант слова с правильным ударением',
                'default_config' => [
                    'variants' => [
                        ['id' => '1', 'letter' => 'М'],
                        ['id' => '2', 'letter' => 'О'],
                        ['id' => '3', 'letter' => 'Л'],
                        ['id' => '4', 'letter' => 'О'],
                        ['id' => '5', 'letter' => 'К'],
                        ['id' => '6', 'letter' => 'О'],
                    ],
                    'correct_variant_ids' => ['2'],
                    'shuffle_variants' => true,
                ],
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'slug' => 'single_select_image_quiz',
                'name' => 'Викторина с картинками (один ответ)',
                'icon' => 'bx bx-image-alt',
                'description' => 'Выбрать одну правильную картинку из предложенных',
                'default_config' => [
                    'variants' => [
                        ['id' => '1', 'imageUrl' => '/images/quiz/apple.png'],
                        ['id' => '2', 'imageUrl' => '/images/quiz/banana.png'],
                    ],
                    'correct_variant_id' => '1',
                    'shuffle_variants' => true,
                ],
                'is_active' => true,
                'sort_order' => 15,
            ],

            [
                'slug' => 'fix_sentence',
                'name' => 'Исправь слово/предложение',
                'icon' => 'bx bx-edit',
                'description' => 'Найти ошибку и написать правильный вариант слова',
                'default_config' => [
                    'sentence' => '{{1}} дает молоко.',
                    'words' => ['Карове', 'Корове'],
                    'correctAnswer' => 'Корове',
                ],
                'is_active' => true,
                'sort_order' => 16,
            ],

            [
                'slug' => 'alphabetic_sorter',
                'name' => 'Сортировщик по алфавиту (слоты)',
                'icon' => 'bx bx-sort-z-a',
                'description' => 'Распределить слова в правильные слоты по алфавиту',
                'default_config' => [
                    'slots' => [
                        ['id' => 'slot-1', 'correctValue' => 'Арбуз', 'slotTitle' => 'Первое слово'],
                        ['id' => 'slot-2', 'correctValue' => 'Банан', 'slotTitle' => 'Второе слово'],
                    ],
                    'variants' => [
                        ['id' => 'variant-1', 'value' => 'Банан'],
                        ['id' => 'variant-2', 'value' => 'Арбуз'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 17,
            ],

            [
                'slug' => 'category_matcher',
                'name' => 'Соедини слова с категориями',
                'icon' => 'bx bx-grid-alt',
                'description' => 'Соедини слова с правильными категориями',
                'default_config' => [
                    'items' => [
                        ['id' => 'item_1', 'label' => 'Яблоко', 'correct' => 'cat_fruits', 'color' => '#green'],
                        ['id' => 'item_2', 'label' => 'Огурец', 'correct' => 'cat_vegetables', 'color' => '#red'],
                    ],
                    'categories' => [
                        ['id' => 'cat_fruits', 'label' => 'Фрукты', 'color' => '#green'],
                        ['id' => 'cat_vegetables', 'label' => 'Овощи', 'color' => '#red'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 18,
            ],
            [
                'slug' => 'colorize_words',
                'name' => 'Раскрась слова',
                'icon' => 'bx bx-paint',
                'description' => 'Выделить слова определенным цветом (маркером)',
                'default_config' => [
                    'tools' => [
                        ['type' => 'paint', 'toolName' => 'Красный маркер', 'toolColor' => '#FF0000'],
                        ['type' => 'paint', 'toolName' => 'Синий маркер', 'toolColor' => '#0000FF'],
                        ['type' => 'erase', 'toolName' => 'Стереть'],
                    ],
                    'variants' => [
                        ['id' => 'var-1', 'content' => 'Существительное', 'correctColor' => '#FF0000'],
                        ['id' => 'var-2', 'content' => 'Глагол', 'correctColor' => '#0000FF'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 19,
            ],
            [
                'slug' => 'conclusion',
                'name' => 'Сделай вывод',
                'icon' => 'bx bx-list-check',
                'description' => 'Заполнить пропущенные слоты логическими выводами',
                'default_config' => [
                    'data' => [
                        [
                            'id' => 'conclusion-1',
                            'value' => 'Слова в предложении  {{связаны}} между собой по смыслу .',
                            'completed' => false,
                            'variants' => [
                                ['id' => 'variant-101', 'value' => 'Следовательно'],
                                ['id' => 'variant-102', 'value' => 'связаны'],
                            ],
                            'slots' => [
                                ['id' => 'slot-1', 'current' => null, 'correct' => 'связаны'],
                            ],
                        ],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'slug' => 'delete_extra_letter',
                'name' => 'Удали лишнюю букву',
                'icon' => 'bx bx-eraser',
                'description' => 'Найти и убрать лишнюю букву в предложенном слове',
                'default_config' => [
                    'variants' => [
                        ['id' => 'l_1', 'letter' => 'П'],
                        ['id' => 'l_2', 'letter' => 'Р'],
                        ['id' => 'l_3', 'letter' => 'И'],
                        ['id' => 'l_4', 'letter' => 'В'],
                        ['id' => 'l_5', 'letter' => 'Е'],
                        ['id' => 'l_6', 'letter' => 'Т'],
                        ['id' => 'l_7', 'letter' => 'Т'],
                    ],
                    'correctVariantIds' => ['l_7'],
                ],
                'is_active' => true,
                'sort_order' => 21,
            ],
            [
                'slug' => 'drop_word_to_image',
                'name' => 'Перетащи слово к картинке',
                'icon' => 'bx bx-move',
                'description' => 'Перетащить текстовую плашку на соответствующее изображение',
                'default_config' => [
                    'items' => [
                        ['id' => '1-item', 'imageUrl' => '/images/animals/cat.png', 'correctVariantId' => '1-variant'],
                        ['id' => '2-item', 'imageUrl' => '/images/animals/dog.png', 'correctVariantId' => '2-variant'],
                    ],
                    'variants' => [
                        ['id' => '1-variant', 'value' => 'Кот'],
                        ['id' => '2-variant', 'value' => 'Собака'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 22,
            ],
            [
                'slug' => 'drop_word_to_text',
                'name' => 'Перетащи слово в текст',
                'icon' => 'bx bx-text',
                'description' => 'Вставить пропущенные слова в нужные места предложения',
                'default_config' => [
                    'items' => [
                        ['id' => 'item-1', 'content' => 'Зимой падает белый...', 'correctVariantId' => 'variant-5'],
                    ],
                    'variants' => [
                        ['id' => 'variant-5', 'value' => 'снег'],
                        ['id' => 'variant-6', 'value' => 'дождь'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 23,
            ],
            [
                'slug' => 'reorder_items',
                'name' => 'Упорядочивание элементов',
                'icon' => 'bx bx-transfer-alt',
                'description' => 'Разобрать хаотичный список и выстроить его в правильном порядке',
                'default_config' => [
                    'data' => [
                        ['id' => 'step_3', 'content' => 'Съесть пирог'],
                        ['id' => 'step_1', 'content' => 'Приготовить тесто'],
                        ['id' => 'step_2', 'content' => 'Испечь в духовке'],
                    ],
                    'correctOrderIds' => ['step_1', 'step_2', 'step_3'],
                ],
                'is_active' => true,
                'sort_order' => 25,
            ],
            [
                'slug' => 'multi_quiz',
                'name' => 'Викторина (множественный выбор)',
                'icon' => 'bx bx-select-multiple',
                'description' => 'Выбрать один или несколько правильных вариантов ответа',
                'default_config' => [
                    'variants' => [
                        ['id' => '1', 'itemNumber' => '1', 'title' => 'Огурец'],
                        ['id' => '2', 'itemNumber' => '2', 'title' => 'Яблоко'],
                        ['id' => '3', 'itemNumber' => '3', 'title' => 'Помидор'],
                    ],
                    'correctVariantIds' => ['1', '3'],
                ],
                'is_active' => true,
                'sort_order' => 24,
            ],
            [
                'slug' => 'sequence_builder',
                'name' => 'Конструктор последовательностей',
                'icon' => 'bx bx-git-commit',
                'description' => 'Собрать логическую цепочку в правильные слоты последовательности',
                'default_config' => [
                    'slots' => [
                        ['slotId' => 's1', 'content' => 'Первый элемент цепочки', 'correctValue' => 'A'],
                        ['slotId' => 's2', 'content' => 'Второй элемент цепочки', 'correctValue' => 'B'],
                    ],
                    'variants' => [
                        ['id' => 'var-1', 'content' => 'A'],
                        ['id' => 'var-2', 'content' => 'B'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 26,
            ],
            [
                'slug' => 'single_quiz',
                'name' => 'Викторина (один ответ)',
                'icon' => 'bx bx-radio-circle-marked',
                'description' => 'Классический текстовый вопрос с выбором одного верного ответа',
                'default_config' => [
                    'variants' => [
                        ['id' => '1', 'itemNumber' => '1', 'title' => 'Земля круглая?'],
                        ['id' => '2', 'itemNumber' => '2', 'title' => 'Земля плоская?'],
                    ],
                    'correctVariantId' => '1',
                ],
                'is_active' => true,
                'sort_order' => 27,
            ]
































































            ,
        ,
            [
                'slug' => 'word_by_image',
                'name' => 'Собрать слово по картинке',
                'icon' => 'bx bx-extension',
                'description' => 'Посмотреть на изображение и собрать слово из доступных букв',
                'default_config' => [
                    'id' => 'task_wbi_1',
                    'correctAnswer' => 'Дом',
                    'imageUrl' => '/images/objects/house.png',
                    'availableLetters' => [
                        ['id' => 'id-letter-1', 'letter' => 'Д'],
                        ['id' => 'id-letter-2', 'letter' => 'О'],
                        ['id' => 'id-letter-3', 'letter' => 'М'],
                        ['id' => 'id-letter-4', 'letter' => 'К'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 28,
            ],
            [
                'slug' => 'word_picker',
                'name' => 'Выбор слов в тексте',
                'icon' => 'bx bx-pointer',
                'description' => 'Кликнуть и выделить правильные слова внутри готового текста',
                'default_config' => [
                    'text' => 'Мама мыла раму мылом.',
                    'correctValues' => ['Мама', 'мыла', 'раму'],
                ],
                'is_active' => true,
                'sort_order' => 29,
            ],
            [
                'slug' => 'drag_word_to_pocket',
                'name' => 'Перетащи слово в кармашек',
                'icon' => 'bx bx-archive-in',
                'description' => 'Перетащи слово к нужной картинке',
                'default_config' => [
                    'items' => [
                        ['id' => 'item-1', 'imageUrl' => '/images/clothes/jacket.png', 'correctVariantId' => 'variant-1'],
                    ],
                    'variants' => [
                        ['id' => 'variant-1', 'value' => 'Куртка'],
                        ['id' => 'variant-2', 'value' => 'Шапка'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'slug' => 'phrase_image_matcher',
                'name' => 'Сопоставление фразы и картинки',
                'icon' => 'bx bx-images',
                'description' => 'Подобрать к каждой иллюстрации подходящее по смыслу предложение',
                'default_config' => [
                    'items' => [
                        ['id' => '1', 'correctVariantId' => '10', 'imageUrl' => '/images/scenes/park.png'],
                    ],
                    'variants' => [
                        ['id' => '10', 'value' => 'Дети играют в парке'],
                        ['id' => '20', 'value' => 'Машина едет по дороге'],
                    ],
                ],
                'is_active' => true,
                'sort_order' => 31,
            ],

        ];

        foreach ($types as $type) {
            TaskType::create($type);
        }
    }
}
