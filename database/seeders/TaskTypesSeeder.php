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
            ],
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
