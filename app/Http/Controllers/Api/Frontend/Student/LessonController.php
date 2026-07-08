<?php

namespace App\Http\Controllers\Api\Frontend\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\EducationModulePiece;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    /**
     * Получить все уроки раздела
     */
    public function index(Request $request, EducationModule $module, EducationModulePiece $piece): JsonResponse
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

        $lessons = $piece->lessons()
            ->orderBy('sort_order')
            ->get();

        $result = $lessons->map(function ($lesson) use ($user) {
            $progress = $user->lessonProgress()
                ->where('lesson_id', $lesson->id)
                ->first();

            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'slug' => $lesson->slug,
                'description' => $lesson->description,
                'image' => $lesson->image,
                'audio' => $lesson->audio,
                'video' => $lesson->video,
                'sort_order' => $lesson->sort_order,
                'is_published' => $lesson->is_published,
                'is_required' => $lesson->is_required,
                'xp_reward' => $lesson->xp_reward,
                'estimated_time' => $lesson->estimated_time,
                'total_tasks' => $lesson->tasks()->count(),
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'is_completed' => $progress->is_completed,
                    'started_at' => $progress->started_at?->toISOString(),
                    'completed_at' => $progress->completed_at?->toISOString(),
                ] : [
                    'status' => 'not_started',
                    'progress_percentage' => 0,
                    'is_completed' => false,
                    'started_at' => null,
                    'completed_at' => null,
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
                'total' => $result->count(),
            ]
        ]);
    }

    /**
     * Получить детальную информацию об уроке с заданиями
     */
    public function show(Request $request, EducationModule $module, EducationModulePiece $piece, Lesson $lesson): JsonResponse
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

        // Загружаем задания
        $lesson->load(['tasks' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        $lessonProgress = $user->lessonProgress()
            ->where('lesson_id', $lesson->id)
            ->first();

        $tasksData = $lesson->tasks->map(function ($task) use ($user) {
            $taskProgress = $user->taskProgress()
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
                'options' => $task->options, // для заданий с вариантами
                'progress' => $taskProgress ? [
                    'status' => $taskProgress->status,
                    'is_completed' => $taskProgress->is_completed,
                    'completed_at' => $taskProgress->completed_at?->toISOString(),
                ] : [
                    'status' => 'not_started',
                    'is_completed' => false,
                    'completed_at' => null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'slug' => $lesson->slug,
                'description' => $lesson->description,
                'image' => $lesson->image,
                'audio' => $lesson->audio,
                'video' => $lesson->video,
                'sort_order' => $lesson->sort_order,
                'is_published' => $lesson->is_published,
                'is_required' => $lesson->is_required,
                'xp_reward' => $lesson->xp_reward,
                'estimated_time' => $lesson->estimated_time,
                'total_tasks' => $lesson->tasks->count(),
                'metadata' => $lesson->metadata,
            ],
            'progress' => $lessonProgress ? [
                'status' => $lessonProgress->status,
                'progress_percentage' => $lessonProgress->progress_percentage,
                'is_completed' => $lessonProgress->is_completed,
                'started_at' => $lessonProgress->started_at?->toISOString(),
                'completed_at' => $lessonProgress->completed_at?->toISOString(),
                'time_spent_seconds' => $lessonProgress->time_spent_seconds,
            ] : [
                'status' => 'not_started',
                'progress_percentage' => 0,
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
            'tasks' => $tasksData,
        ]);
    }

    /**
     * Получить прогресс по уроку
     */
    public function progress(Request $request, EducationModule $module, EducationModulePiece $piece, Lesson $lesson): JsonResponse
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

        $progress = $user->lessonProgress()
            ->where('lesson_id', $lesson->id)
            ->first();

        // Прогресс по заданиям в уроке
        $tasksProgress = [];
        $tasks = $lesson->tasks()->orderBy('sort_order')->get();

        foreach ($tasks as $task) {
            $taskProgress = $user->taskProgress()
                ->where('task_id', $task->id)
                ->first();

            $tasksProgress[] = [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'sort_order' => $task->sort_order,
                'status' => $taskProgress ? $taskProgress->status : 'not_started',
                'is_completed' => $taskProgress ? $taskProgress->is_completed : false,
                'completed_at' => $taskProgress?->completed_at?->toISOString(),
            ];
        }

        return response()->json([
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
            ],
            'piece' => [
                'id' => $piece->id,
                'name' => $piece->name,
            ],
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
            ],
            'progress' => $progress ? [
                'status' => $progress->status,
                'progress_percentage' => $progress->progress_percentage,
                'is_completed' => $progress->is_completed,
                'started_at' => $progress->started_at?->toISOString(),
                'completed_at' => $progress->completed_at?->toISOString(),
                'time_spent_seconds' => $progress->time_spent_seconds,
            ] : [
                'status' => 'not_started',
                'progress_percentage' => 0,
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
            'tasks_progress' => $tasksProgress,
        ]);
    }

    /**
     * Получить следующее задание в уроке
     */
    public function nextTask(Request $request, EducationModule $module, EducationModulePiece $piece, Lesson $lesson): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ
        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        if ($piece->education_module_id !== $module->id || $lesson->piece_id !== $piece->id) {
            return response()->json(['message' => 'Неверная структура'], 404);
        }

        // Получаем ID завершенных заданий
        $completedTaskIds = $user->taskProgress()
            ->whereIn('task_id', $lesson->tasks()->pluck('id'))
            ->where('status', 'completed')
            ->pluck('task_id')
            ->toArray();

        // Находим следующее незавершенное задание
        $nextTask = $lesson->tasks()
            ->whereNotIn('id', $completedTaskIds)
            ->orderBy('sort_order')
            ->first();

        if (!$nextTask) {
            return response()->json([
                'message' => 'Все задания в уроке выполнены',
                'is_completed' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $nextTask->id,
                'title' => $nextTask->title,
                'description' => $nextTask->description,
                'type' => $nextTask->type,
                'options' => $nextTask->options,
                'xp_reward' => $nextTask->xp_reward,
                'sort_order' => $nextTask->sort_order,
            ],
        ]);
    }
}
