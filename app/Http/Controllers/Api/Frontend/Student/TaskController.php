<?php

namespace App\Http\Controllers\Api\Frontend\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\EducationModulePiece;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\UserTaskProgress;
use App\Models\UserLessonProgress;
use App\Models\UserPieceProgress;
use App\Models\UserModuleProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    /**
     * Получить все задания урока
     */
    public function index(Request $request, EducationModule $module, EducationModulePiece $piece, Lesson $lesson): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ к модулю
        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что раздел принадлежит модулю
        if ($piece->education_module_id !== $module->id) {
            return response()->json(['message' => 'Раздел не принадлежит этому модулю'], 404);
        }

        // Проверяем, что урок принадлежит разделу
        if ($lesson->piece_id !== $piece->id) {
            return response()->json(['message' => 'Урок не принадлежит этому разделу'], 404);
        }

        $tasks = $lesson->tasks()
            ->with(['taskType'])
            ->orderBy('sort_order')
            ->get();

        $result = $tasks->map(function ($task) use ($user) {
            $progress = $user->taskProgress()
                ->where('task_id', $task->id)
                ->first();

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'task_type' => $task->taskType ? [
                    'id' => $task->taskType->id,
                    'name' => $task->taskType->name,
                    'slug' => $task->taskType->slug,
                ] : null,
                'sort_order' => $task->sort_order,
                'xp_reward' => $task->xp_reward,
                'is_required' => $task->is_required,
                'is_published' => $task->is_published,
                'max_attempts' => $task->max_attempts,
                'time_limit_seconds' => $task->time_limit_seconds,
                'has_hints' => !empty($task->hints),
                'hints_count' => count($task->hints ?? []),
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'is_completed' => $progress->is_completed,
                    'completed_at' => $progress->completed_at?->toISOString(),
                    'attempts' => $progress->attempts_count,
                    'attempts_left' => $progress->attempts_left,
                ] : [
                    'status' => 'not_started',
                    'is_completed' => false,
                    'completed_at' => null,
                    'attempts' => 0,
                    'attempts_left' => $task->max_attempts ?? 3,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'meta' => [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'piece_id' => $piece->id,
                'piece_name' => $piece->name,
                'lesson_id' => $lesson->id,
                'lesson_name' => $lesson->name,
                'total' => $result->count(),
            ]
        ]);
    }

    /**
     * Получить конкретное задание
     */
    public function show(Request $request, EducationModule $module, EducationModulePiece $piece, Lesson $lesson, Task $task): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ к модулю
        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что раздел принадлежит модулю
        if ($piece->education_module_id !== $module->id) {
            return response()->json(['message' => 'Раздел не принадлежит этому модулю'], 404);
        }

        // Проверяем, что урок принадлежит разделу
        if ($lesson->piece_id !== $piece->id) {
            return response()->json(['message' => 'Урок не принадлежит этому разделу'], 404);
        }

        // Проверяем, что задание принадлежит уроку
        if ($task->lesson_id !== $lesson->id) {
            return response()->json(['message' => 'Задание не принадлежит этому уроку'], 404);
        }

        // Загружаем тип задания
        $task->load(['taskType']);

        $progress = $user->taskProgress()
            ->where('task_id', $task->id)
            ->first();

        // Для задания с вариантами ответов скрываем правильный ответ
        $taskData = $task->toArray();

        // Добавляем информацию о типе задания
        $taskData['task_type'] = $task->taskType ? [
            'id' => $task->taskType->id,
            'name' => $task->taskType->name,
            'slug' => $task->taskType->slug,
            'description' => $task->taskType->description,
        ] : null;

        // Скрываем правильный ответ для некоторых типов
        if (in_array($task->taskType?->slug, ['choose_one', 'choose_three', 'match_pairs'])) {
            unset($taskData['config']['correct']);
            unset($taskData['config']['correct_answer']);
            $taskData['config']['has_correct_answer'] = true;
        }

        return response()->json([
            'success' => true,
            'task' => $taskData,
            'progress' => $progress ? [
                'status' => $progress->status,
                'is_completed' => $progress->is_completed,
                'started_at' => $progress->created_at?->toISOString(),
                'completed_at' => $progress->completed_at?->toISOString(),
                'attempts' => $progress->attempts_count,
                'last_answer' => $progress->last_answer,
                'attempts_left' => $progress->attempts_left,
            ] : [
                'status' => 'not_started',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'attempts' => 0,
                'last_answer' => null,
                'attempts_left' => $task->max_attempts ?? 3,
            ],
            'meta' => [
                'module_id' => $module->id,
                'piece_id' => $piece->id,
                'lesson_id' => $lesson->id,
            ]
        ]);
    }

    /**
     * Выполнить задание
     */
    public function complete(Request $request, EducationModule $module, EducationModulePiece $piece, Lesson $lesson, Task $task): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ
        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        if ($piece->education_module_id !== $module->id) {
            return response()->json(['message' => 'Раздел не принадлежит этому модулю'], 404);
        }

        if ($lesson->piece_id !== $piece->id) {
            return response()->json(['message' => 'Урок не принадлежит этому разделу'], 404);
        }

        if ($task->lesson_id !== $lesson->id) {
            return response()->json(['message' => 'Задание не принадлежит этому уроку'], 404);
        }

        // Загружаем тип задания
        $task->load(['taskType']);
        $taskTypeSlug = $task->taskType?->slug ?? 'unknown';

        // Валидация
        $rules = $this->getValidationRules($taskTypeSlug);
        $validated = $request->validate($rules);

        // Получаем или создаем прогресс задания
        $taskProgress = $user->taskProgress()
            ->where('task_id', $task->id)
            ->first();

        if (!$taskProgress) {
            $taskProgress = new UserTaskProgress([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'status' => UserTaskProgress::STATUS_PENDING,
                'attempts_count' => 0,
            ]);
        }

        // Проверяем, не выполнено ли уже задание
        if ($taskProgress->status === UserTaskProgress::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Задание уже выполнено',
                'is_completed' => true,
                'progress' => $taskProgress->toApiArray(),
            ]);
        }

        // Проверяем попытки
        if ($taskProgress->attempts_count >= ($task->max_attempts ?? 3)) {
            return response()->json([
                'message' => 'Попытки исчерпаны',
                'is_completed' => false,
                'attempts' => $taskProgress->attempts_count,
                'attempts_left' => 0,
            ], 422);
        }

        // Увеличиваем счетчик попыток
        $taskProgress->attempts_count += 1;

        // Сохраняем ответ
        $taskProgress->user_answers = is_array($validated['answer'])
            ? $validated['answer']
            : $validated['answer'];

        $taskProgress->last_answer = is_array($validated['answer'])
            ? json_encode($validated['answer'])
            : $validated['answer'];

        $taskProgress->last_activity_at = now();

        if (!$taskProgress->started_at) {
            $taskProgress->started_at = now();
        }

        if (isset($validated['time_spent'])) {
            $taskProgress->time_spent_seconds = ($taskProgress->time_spent_seconds ?? 0) + $validated['time_spent'];
        }

        // Проверяем правильность ответа
        $isCorrect = $this->checkAnswer($task, $validated['answer']);

        if ($isCorrect) {
            // ✅ Задание выполнено правильно
            $taskProgress->status = UserTaskProgress::STATUS_COMPLETED;
            $taskProgress->completed_at = now();
            $taskProgress->progress_percentage = 100;

            // Начисляем XP
            $user->addXp($task->xp_reward ?? 10, 'task_completed');

            $message = 'Задание выполнено правильно! +' . ($task->xp_reward ?? 10) . ' XP';
        } else {
            // ❌ Задание выполнено неправильно
            $taskProgress->status = UserTaskProgress::STATUS_FAILED;
            $message = 'Ответ неправильный. Попробуйте еще раз.';

            if ($taskProgress->attempts_count >= 3) {
                $hint = $this->getHint($task);
                if ($hint) {
                    $message .= ' Подсказка: ' . $hint;
                }
            }
        }

        $taskProgress->save();

        // ============================================================
        // 🚀 ОБНОВЛЯЕМ ПРОГРЕСС НА ВСЕХ УРОВНЯХ
        // ============================================================

        // 1. Обновляем прогресс урока
        $this->updateLessonProgress($user, $lesson);

        // 2. Обновляем прогресс раздела
        $this->updatePieceProgress($user, $piece);

        // 3. Обновляем прогресс модуля
        $this->updateModuleProgress($user, $module);

        // ============================================================
        // 📊 ВОЗВРАЩАЕМ ОТВЕТ С ПРОГРЕССОМ НА ВСЕХ УРОВНЯХ
        // ============================================================

        // Получаем обновленные прогрессы
        $lessonProgress = $user->lessonProgress()->where('lesson_id', $lesson->id)->first();
        $pieceProgress = $user->pieceProgress()->where('piece_id', $piece->id)->first();
        $moduleProgress = $user->moduleProgress()->where('module_id', $module->id)->first();

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'message' => $message,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'xp_reward' => $task->xp_reward,
            ],
            'task_type' => $task->taskType ? [
                'id' => $task->taskType->id,
                'name' => $task->taskType->name,
                'slug' => $task->taskType->slug,
            ] : null,
            'progress' => [
                'task' => $taskProgress->toApiArray(),
                'lesson' => $lessonProgress ? $lessonProgress->toApiArray() : null,
                'piece' => $pieceProgress ? $pieceProgress->toApiArray() : null,
                'module' => $moduleProgress ? $moduleProgress->toApiArray() : null,
            ],
            'xp_earned' => $isCorrect ? ($task->xp_reward ?? 10) : 0,
            'attempts' => $taskProgress->attempts_count,
            'attempts_left' => max(0, ($task->max_attempts ?? 3) - $taskProgress->attempts_count),
            'hint' => (!$isCorrect && $taskProgress->attempts_count >= 3) ? $this->getHint($task) : null,
        ]);
    }

    /**
     * Получить правила валидации в зависимости от типа задания
     */
    // private function getValidationRules(string $taskTypeSlug): array
    // {
    //     $rules = [
    //         'time_spent' => 'nullable|integer|min:0',
    //     ];

    //     switch ($taskTypeSlug) {
    //         case 'choose_one':
    //         case 'fill_word':
    //         case 'build_word':
    //         case 'word_from_image':
    //         case 'fix_word':
    //         case 'stress_mark':
    //         case 'find_extra_letter':
    //             $rules['answer'] = 'required|string|max:1000';
    //             break;

    //         case 'choose_three':
    //         case 'alphabet_letters':
    //         case 'alphabet_images':
    //         case 'alphabet_words':
    //         case 'connect_letters':
    //         case 'story_order':
    //         case 'find_by_rule':
    //         case 'find_by_condition':
    //         case 'build_dialogue':
    //             $rules['answer'] = 'required|array';
    //             break;

    //         case 'match_pairs':
    //         case 'drag_drop_text':
    //         case 'color_categories':
    //         case 'connect_category':
    //         case 'drag_to_image':
    //         case 'match_behavior':
    //             $rules['answer'] = 'required|array';
    //             break;

    //         default:
    //             $rules['answer'] = 'required|string|max:1000';
    //             break;
    //     }

    //     return $rules;
    // }
private function getValidationRules(string $taskTypeSlug): array
{
    $rules = [
        'time_spent' => 'nullable|integer|min:0',
    ];

    switch ($taskTypeSlug) {
        case 'choose_one':
        case 'fill_word':
        case 'build_word':
        case 'word_from_image':
        case 'fix_word':
        case 'stress_mark':
        case 'find_extra_letter':
        case 'fix_sentence': // (Строка) добавим сюда для порядка
        case 'single_quiz':  // (Строка/Число)
        case 'word_by_image': // (Строка)
            $rules['answer'] = 'required|string|max:1000';
            break;

        case 'choose_three':
        case 'alphabet_letters':
        case 'alphabet_images':
        case 'alphabet_words':
        case 'connect_letters':
        case 'story_order':
        case 'find_by_rule':
        case 'find_by_condition':
        case 'build_dialogue':
        case 'accent_trainer':
        case 'delete_extra_letter':
        case 'multi_quiz':
        case 'word_picker':
        case 'reorder_items':
        case 'alphabetic_sorter':
        case 'category_matcher':
        case 'colorize_words':
        case 'conclusion':
        case 'drop_word_to_image':
        case 'drop_word_to_text':
        case 'drag_word_to_pocket':
        case 'phrase_image_matcher':
        case'sequence_builder':
            $rules['answer'] = 'required|array';
            break;

        case 'match_pairs':
        case 'drag_drop_text':
        case 'color_categories':
        case 'connect_category':
        case 'drag_to_image':
        case 'match_behavior':
            $rules['answer'] = 'required|array';
            break;

        default:
            $rules['answer'] = 'required|string|max:1000';
            break;
    }

    return $rules;
}
    /**
     * Проверить ответ на задание
     */
    private function checkAnswer(Task $task, mixed $answer): bool
    {
        $taskTypeSlug = $task->taskType?->slug ?? 'unknown';
        $config = $task->config ?? [];

        switch ($taskTypeSlug) {
            case 'choose_one':
                $correctOptions = array_filter($config['options'] ?? [], function ($option) {
                    return $option['is_correct'] ?? false;
                });
                $correctId = !empty($correctOptions) ? reset($correctOptions)['id'] : null;
                return $answer === $correctId;

            case 'choose_three':
                $correctOptions = array_filter($config['options'] ?? [], function ($option) {
                    return $option['is_correct'] ?? false;
                });
                $correctIds = array_column($correctOptions, 'id');

                if (!is_array($answer) || count($answer) !== count($correctIds)) {
                    return false;
                }

                sort($correctIds);
                $userIds = $answer;
                sort($userIds);
                return $correctIds === $userIds;

            case 'fill_word':
                $correct = $config['correct'] ?? '';
                $caseSensitive = $config['case_sensitive'] ?? false;
                return $caseSensitive
                    ? $answer === $correct
                    : strtolower($answer) === strtolower($correct);

            case 'match_pairs':
                $pairs = $config['pairs'] ?? [];
                if (!is_array($answer) || count($answer) !== count($pairs)) {
                    return false;
                }
                foreach ($pairs as $pair) {
                    $match = $answer[$pair['id']] ?? null;
                    if ($match !== $pair['correct_match']) {
                        return false;
                    }
                }
                return true;

            case 'build_word':
                $correct = $config['correct_word'] ?? '';
                return strtoupper($answer) === strtoupper($correct);

            default:
                if (method_exists($task, 'checkAnswer')) {
                    return $task->checkAnswer($answer);
                }
                $correct = $task->getCorrectAnswer();
                return $answer === $correct;
        }
    }

    /**
     * Получить подсказку для задания
     */
    private function getHint(Task $task): ?string
    {
        return $task->metadata['hint'] ?? 'Подумайте еще раз внимательно.';
    }

    /**
     * Обновить прогресс урока
     */
    private function updateLessonProgress($user, Lesson $lesson): void
    {
        $progress = $user->lessonProgress()
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$progress) {
            $progress = new UserLessonProgress([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'status' => 'not_started',
            ]);
        }

        $totalTasks = $lesson->tasks()->count();

        if ($totalTasks === 0) {
            $progress->progress_percentage = 100;
            $progress->status = 'completed';
            $progress->completed_at = now();
            $progress->save();
            return;
        }

        $taskIds = $lesson->tasks()->pluck('id')->toArray();
        $completedTasks = UserTaskProgress::where('user_id', $user->id)
            ->whereIn('task_id', $taskIds)
            ->where('status', UserTaskProgress::STATUS_COMPLETED)
            ->count();

        $progress->progress_percentage = round(($completedTasks / $totalTasks) * 100, 2);

        if ($progress->progress_percentage >= 100) {
            $progress->status = 'completed';
            $progress->completed_at = now();
        } elseif ($progress->progress_percentage > 0) {
            $progress->status = 'in_progress';
            if (!$progress->started_at) {
                $progress->started_at = now();
            }
        }

        $progress->last_activity_at = now();
        $progress->save();
    }

    /**
     * Обновить прогресс раздела
     */
    private function updatePieceProgress($user, EducationModulePiece $piece): void
    {
        $progress = $user->pieceProgress()
            ->where('piece_id', $piece->id)
            ->first();

        if (!$progress) {
            $progress = new UserPieceProgress([
                'user_id' => $user->id,
                'piece_id' => $piece->id,
                'status' => 'not_started',
            ]);
        }

        $totalLessons = $piece->lessons()->count();

        if ($totalLessons === 0) {
            $progress->progress_percentage = 100;
            $progress->status = 'completed';
            $progress->completed_at = now();
            $progress->save();
            return;
        }

        $lessonIds = $piece->lessons()->pluck('id')->toArray();
        $completedLessons = UserLessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->count();

        $progress->progress_percentage = round(($completedLessons / $totalLessons) * 100, 2);

        if ($progress->progress_percentage >= 100) {
            $progress->status = 'completed';
            $progress->completed_at = now();
        } elseif ($progress->progress_percentage > 0) {
            $progress->status = 'in_progress';
            if (!$progress->started_at) {
                $progress->started_at = now();
            }
        }

        $progress->last_activity_at = now();
        $progress->save();
    }

    /**
     * Обновить прогресс модуля
     */
    private function updateModuleProgress($user, EducationModule $module): void
    {
        $progress = $user->moduleProgress()
            ->where('module_id', $module->id)
            ->first();

        if (!$progress) {
            $progress = new UserModuleProgress([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'status' => 'not_started',
            ]);
        }

        $totalPieces = $module->pieces()->count();

        if ($totalPieces === 0) {
            $progress->progress_percentage = 100;
            $progress->status = 'completed';
            $progress->completed_at = now();
            $progress->save();
            return;
        }

        $pieceIds = $module->pieces()->pluck('id')->toArray();
        $completedPieces = UserPieceProgress::where('user_id', $user->id)
            ->whereIn('piece_id', $pieceIds)
            ->where('status', 'completed')
            ->count();

        $progress->progress_percentage = round(($completedPieces / $totalPieces) * 100, 2);

        if ($progress->progress_percentage >= 100) {
            $progress->status = 'completed';
            $progress->completed_at = now();

            // Бонус за завершение модуля
            $user->addXp($module->total_xp_reward ?? 50, 'module_completed');
        } elseif ($progress->progress_percentage > 0) {
            $progress->status = 'in_progress';
            if (!$progress->started_at) {
                $progress->started_at = now();
            }
        }

        $progress->last_activity_at = now();
        $progress->save();
    }
}
