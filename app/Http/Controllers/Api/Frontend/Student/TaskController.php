<?php

namespace App\Http\Controllers\Api\Frontend\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\EducationModulePiece;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\UserTaskProgress;
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
                'type' => $task->type,
                'sort_order' => $task->sort_order,
                'xp_reward' => $task->xp_reward,
                'is_required' => $task->is_required,
                'is_published' => $task->is_published,
                'options' => $task->options, // для заданий с вариантами
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'is_completed' => $progress->is_completed,
                    'completed_at' => $progress->completed_at?->toISOString(),
                    'attempts' => $progress->attempts,
                ] : [
                    'status' => 'not_started',
                    'is_completed' => false,
                    'completed_at' => null,
                    'attempts' => 0,
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

        $progress = $user->taskProgress()
            ->where('task_id', $task->id)
            ->first();

        // Для задания с вариантами ответов скрываем правильный ответ
        $taskData = $task->toArray();
        if ($task->type === 'choose' || $task->type === 'multiple') {
            unset($taskData['correct_answer']); // Скрываем правильный ответ
        }

        return response()->json([
            'success' => true,
            'task' => $taskData,
            'progress' => $progress ? [
                'status' => $progress->status,
                'is_completed' => $progress->is_completed,
                'started_at' => $progress->started_at?->toISOString(),
                'completed_at' => $progress->completed_at?->toISOString(),
                'attempts' => $progress->attempts,
                'last_answer' => $progress->last_answer,
            ] : [
                'status' => 'not_started',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'attempts' => 0,
                'last_answer' => null,
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

        // Валидация входящих данных
        $validated = $request->validate([
            'answer' => 'required|string|max:1000',
            'time_spent' => 'nullable|integer|min:0', // время в секундах
        ]);

        // Получаем или создаем прогресс задания
        $progress = $user->taskProgress()
            ->where('task_id', $task->id)
            ->first();

        if (!$progress) {
            $progress = new UserTaskProgress([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'status' => UserTaskProgress::STATUS_IN_PROGRESS,
                'attempts' => 0,
            ]);
        }

        // Проверяем, не выполнено ли уже задание
        if ($progress->status === UserTaskProgress::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Задание уже выполнено',
                'is_completed' => true,
                'progress' => $progress->toApiArray(),
            ]);
        }

        // Увеличиваем счетчик попыток
        $progress->attempts += 1;
        $progress->last_answer = $validated['answer'];
        $progress->last_activity_at = now();

        if (!$progress->started_at) {
            $progress->started_at = now();
        }

        // Добавляем время, если передано
        if (isset($validated['time_spent'])) {
            $progress->time_spent_seconds = ($progress->time_spent_seconds ?? 0) + $validated['time_spent'];
        }

        // Проверяем правильность ответа
        $isCorrect = $this->checkAnswer($task, $validated['answer']);

        if ($isCorrect) {
            // Задание выполнено правильно
            $progress->status = UserTaskProgress::STATUS_COMPLETED;
            $progress->completed_at = now();
            $progress->progress_percentage = 100;

            // Начисляем XP пользователю
            $user->addXp($task->xp_reward ?? 10, 'task_completed');

            // Обновляем прогресс урока
            $this->updateLessonProgress($user, $lesson);

            // Обновляем прогресс раздела
            $this->updatePieceProgress($user, $piece);

            // Обновляем прогресс модуля
            $this->updateModuleProgress($user, $module);

            $message = 'Задание выполнено правильно! +' . ($task->xp_reward ?? 10) . ' XP';
        } else {
            // Задание выполнено неправильно
            $progress->status = UserTaskProgress::STATUS_FAILED;
            $message = 'Ответ неправильный. Попробуйте еще раз.';

            // Если много попыток, можно дать подсказку
            if ($progress->attempts >= 3) {
                $message .= ' Подсказка: ' . $this->getHint($task);
            }
        }

        $progress->save();

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'message' => $message,
            'progress' => $progress->toApiArray(),
            'xp_earned' => $isCorrect ? ($task->xp_reward ?? 10) : 0,
            'attempts' => $progress->attempts,
            'hint' => (!$isCorrect && $progress->attempts >= 3) ? $this->getHint($task) : null,
        ]);
    }

    /**
     * Проверить ответ на задание
     */
    private function checkAnswer(Task $task, string $answer): bool
    {
        // В зависимости от типа задания
        switch ($task->type) {
            case 'choose':
            case 'single':
                // Для одиночного выбора - сравниваем с правильным ответом
                return strtolower(trim($answer)) === strtolower(trim($task->correct_answer));

            case 'multiple':
                // Для множественного выбора - нужно сравнить массивы
                $userAnswers = json_decode($answer, true);
                $correctAnswers = json_decode($task->correct_answer, true);
                if (!is_array($userAnswers) || !is_array($correctAnswers)) {
                    return false;
                }
                sort($userAnswers);
                sort($correctAnswers);
                return $userAnswers === $correctAnswers;

            case 'text':
                // Для текстового ответа - сравниваем с правильным (можно добавить нечеткое сравнение)
                return strtolower(trim($answer)) === strtolower(trim($task->correct_answer));

            case 'drag_drop':
                // Для drag&drop - нужно сравнивать соответствия
                $userMatches = json_decode($answer, true);
                $correctMatches = json_decode($task->correct_answer, true);
                return $userMatches === $correctMatches;

            default:
                return false;
        }
    }

    /**
     * Получить подсказку для задания
     */
    private function getHint(Task $task): ?string
    {
        // Можно хранить подсказки в metadata
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
            $progress = new \App\Models\UserLessonProgress([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'status' => 'in_progress',
            ]);
        }

        $totalTasks = $lesson->tasks()->count();
        $completedTasks = $lesson->tasks()
            ->whereHas('userProgress', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', UserTaskProgress::STATUS_COMPLETED);
            })
            ->count();

        $progress->progress_percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 100;

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
            $progress = new \App\Models\UserPieceProgress([
                'user_id' => $user->id,
                'piece_id' => $piece->id,
                'status' => 'not_started',
            ]);
        }

        $totalLessons = $piece->lessons()->count();
        $completedLessons = $piece->lessons()
            ->whereHas('userProgress', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'completed');
            })
            ->count();

        $progress->progress_percentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 100;

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
            $progress = new \App\Models\UserModuleProgress([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'status' => 'not_started',
            ]);
        }

        $totalPieces = $module->pieces()->count();
        $completedPieces = $module->pieces()
            ->whereHas('userProgress', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'completed');
            })
            ->count();

        $progress->progress_percentage = $totalPieces > 0 ? round(($completedPieces / $totalPieces) * 100, 2) : 100;

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
