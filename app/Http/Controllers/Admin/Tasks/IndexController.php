<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\TaskType;
use App\Services\TaskConfigValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    protected $configValidator;

    public function __construct(TaskConfigValidator $configValidator)
    {
        $this->configValidator = $configValidator;
    }

    public function index(Request $request)
    {
        $query = Task::with(['lesson', 'taskType']);

        if ($request->has('lesson_id') && $request->lesson_id) {
            $query->where('lesson_id', $request->lesson_id);
        }

        if ($request->has('task_type_id') && $request->task_type_id) {
            $query->where('task_type_id', $request->task_type_id);
        }

        $tasks = $query->orderBy('sort_order')->paginate(20);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function create(Request $request)
    {
        $lessons = Lesson::where('is_published', true)->get();
        $taskTypes = TaskType::where('is_active', true)->get();
        $selectedLessonId = $request->lesson_id ?? null;

        return view('admin.tasks.create', compact('lessons', 'taskTypes', 'selectedLessonId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'task_type_id' => 'required|exists:task_types,id',
            'sort_order' => 'nullable|integer|min:0',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|string',
            'max_attempts' => 'nullable|integer|min:1',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'xp_reward' => 'nullable|integer|min:0',
            'hints' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            // Медиа
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        // ПРЕОБРАЗУЕМ CONFIG В МАССИВ
        $configData = $request->input('config');

        if (is_string($configData) && !empty($configData)) {
            $configData = json_decode($configData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()
                    ->back()
                    ->withErrors(['config' => 'Неверный формат JSON в конфигурации'])
                    ->withInput();
            }
        } elseif (empty($configData)) {
            $configData = [];
        }

        if (is_array($configData)) {
            foreach ($configData as $key => $value) {
                if (is_string($value) && $this->isJson($value)) {
                    $configData[$key] = json_decode($value, true);
                }
            }
        }

        // Валидируем конфиг
        $taskType = TaskType::find($request->task_type_id);
        $validatedConfig = $this->configValidator->validate($configData, $taskType->slug);

        $data = $request->except(['config', 'audio', 'image', 'video']);
        $data['config'] = $validatedConfig;
        $data['is_published'] = $request->has('is_published') ? true : false;
        $data['is_required'] = $request->has('is_required') ? true : false;

        if ($request->has('hints')) {
            $data['hints'] = array_filter($request->hints, function ($hint) {
                return !empty($hint);
            });
        }

        // ============================================================
        // ОБРАБОТКА МЕДИАФАЙЛОВ - СОХРАНЯЕМ В ОТДЕЛЬНЫЕ ПОЛЯ
        // ============================================================

        // Аудио - сохраняем путь в поле audio
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            if ($file->isValid()) {
                $path = $file->store('tasks/audio', 'public');
                $data['audio'] = $path; // Сохраняем путь в отдельное поле
            }
        }

        // Изображение - сохраняем путь в поле image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $path = $file->store('tasks/images', 'public');
                $data['image'] = $path; // Сохраняем путь в отдельное поле
            }
        }

        // Видео - сохраняем путь в поле video
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            if ($file->isValid()) {
                $path = $file->store('tasks/videos', 'public');
                $data['video'] = $path; // Сохраняем путь в отдельное поле
            }
        }

        $task = Task::create($data);

        return redirect()
            ->route('admin.tasks.index', ['lesson_id' => $task->lesson_id])
            ->with('success', 'Задание "' . ($task->title ?? '#' . $task->id) . '" успешно создано!');
    }

    public function edit(Task $task)
    {
        $lessons = Lesson::where('is_published', true)->get();
        $taskTypes = TaskType::where('is_active', true)->get();

        return view('admin.tasks.edit', compact('task', 'lessons', 'taskTypes'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'task_type_id' => 'required|exists:task_types,id',
            'sort_order' => 'nullable|integer|min:0',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|string',
            'max_attempts' => 'nullable|integer|min:1',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'xp_reward' => 'nullable|integer|min:0',
            'hints' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            // Медиа
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        // ПРЕОБРАЗУЕМ CONFIG В МАССИВ
        $configData = $request->input('config');

        if (is_string($configData) && !empty($configData)) {
            $configData = json_decode($configData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()
                    ->back()
                    ->withErrors(['config' => 'Неверный формат JSON в конфигурации'])
                    ->withInput();
            }
        } elseif (empty($configData)) {
            $configData = [];
        }

        if (is_array($configData)) {
            foreach ($configData as $key => $value) {
                if (is_string($value) && $this->isJson($value)) {
                    $configData[$key] = json_decode($value, true);
                }
            }
        }

        // Валидируем конфиг
        $taskType = TaskType::find($request->task_type_id);
        $validatedConfig = $this->configValidator->validate($configData, $taskType->slug);

        $data = $request->except(['config', 'audio', 'image', 'video']);
        $data['config'] = $validatedConfig;
        $data['is_published'] = $request->has('is_published') ? true : false;
        $data['is_required'] = $request->has('is_required') ? true : false;

        if ($request->has('hints')) {
            $data['hints'] = array_filter($request->hints, function ($hint) {
                return !empty($hint);
            });
        }

        // ============================================================
        // ОБРАБОТКА МЕДИАФАЙЛОВ
        // ============================================================

        // Аудио
        if ($request->hasFile('audio')) {
            // Удаляем старый файл
            if ($task->audio) {
                Storage::disk('public')->delete($task->audio);
            }

            $file = $request->file('audio');
            if ($file->isValid()) {
                $path = $file->store('tasks/audio', 'public');
                $data['audio'] = $path;
            }
        }

        // Изображение
        if ($request->hasFile('image')) {
            if ($task->image) {
                Storage::disk('public')->delete($task->image);
            }

            $file = $request->file('image');
            if ($file->isValid()) {
                $path = $file->store('tasks/images', 'public');
                $data['image'] = $path;
            }
        }

        // Видео
        if ($request->hasFile('video')) {
            if ($task->video) {
                Storage::disk('public')->delete($task->video);
            }

            $file = $request->file('video');
            if ($file->isValid()) {
                $path = $file->store('tasks/videos', 'public');
                $data['video'] = $path;
            }
        }

        $task->update($data);

        return redirect()
            ->route('admin.tasks.index', ['lesson_id' => $task->lesson_id])
            ->with('success', 'Задание "' . ($task->title ?? '#' . $task->id) . '" успешно обновлено!');
    }

    public function destroy(Task $task)
    {
        $title = $task->title ?? 'Задание #' . $task->id;
        $lessonId = $task->lesson_id;

        // Удаляем медиафайлы
        $mediaFields = ['audio_url', 'image_url', 'video_url'];
        foreach ($mediaFields as $field) {
            if (isset($task->config[$field])) {
                $path = str_replace('/storage/', '', $task->config[$field]);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $task->delete();

        return redirect()
            ->route('admin.tasks.index', ['lesson_id' => $lessonId])
            ->with('success', 'Задание "' . $title . '" успешно удалено!');
    }

    public function getDefaultConfig(Request $request)
    {
        $taskType = TaskType::find($request->task_type_id);
        if (!$taskType) {
            return response()->json(['error' => 'Тип задания не найден'], 404);
        }

        $defaultConfig = $taskType->default_config ?? [];

        return response()->json([
            'slug' => $taskType->slug,
            'config' => $defaultConfig,
            'validation_rules' => $taskType->getValidationRules($taskType->slug),
        ]);
    }

    /**
     * Проверяет, является ли строка валидным JSON
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function uploadConfigImage(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    ]);

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('tasks', 'public');

        return response()->json([
            'url' => Storage::url($path)
        ]);
    }

    return response()->json(['error' => 'Файл не загружен'], 400);
}
}
