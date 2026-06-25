<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\TaskType;
use App\Services\TaskConfigValidator;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    protected $configValidator;

    public function __construct(TaskConfigValidator $configValidator)
    {
        $this->configValidator = $configValidator;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::with(['lesson', 'taskType']);

        // Фильтр по уроку
        if ($request->has('lesson_id') && $request->lesson_id) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Фильтр по типу задания
        if ($request->has('task_type_id') && $request->task_type_id) {
            $query->where('task_type_id', $request->task_type_id);
        }

        $tasks = $query->orderBy('sort_order')->paginate(20);

        return view('admin.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $lessons = Lesson::where('is_published', true)->get();
        $taskTypes = TaskType::where('is_active', true)->get();

        // Если передан lesson_id, выбираем его
        $selectedLessonId = $request->lesson_id ?? null;

        return view('admin.tasks.create', compact('lessons', 'taskTypes', 'selectedLessonId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'task_type_id' => 'required|exists:task_types,id',
            'sort_order' => 'nullable|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'config' => 'required|array',
            'max_attempts' => 'nullable|integer|min:1',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'xp_reward' => 'nullable|integer|min:0',
            'hints' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
        ]);

        // Валидируем конфиг
        $taskType = TaskType::find($request->task_type_id);
        $validatedConfig = $this->configValidator->validate($request->config, $taskType->slug);

        $data = $request->all();
        $data['config'] = $validatedConfig;
        $data['is_published'] = $request->has('is_published');
        $data['is_required'] = $request->has('is_required');

        // Преобразуем hints в массив
        if ($request->has('hints')) {
            $data['hints'] = array_filter($request->hints, function ($hint) {
                return !empty($hint);
            });
        }

        $task = Task::create($data);

        return redirect()
            ->route('admin.tasks.index', ['lesson_id' => $task->lesson_id])
            ->with('success', 'Задание "' . ($task->title ?? '#' . $task->id) . '" успешно создано!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $lessons = Lesson::where('is_published', true)->get();
        $taskTypes = TaskType::where('is_active', true)->get();

        return view('admin.tasks.edit', compact('task', 'lessons', 'taskTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'task_type_id' => 'required|exists:task_types,id',
            'sort_order' => 'nullable|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'config' => 'required|array',
            'max_attempts' => 'nullable|integer|min:1',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'xp_reward' => 'nullable|integer|min:0',
            'hints' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
        ]);

        // Валидируем конфиг
        $taskType = TaskType::find($request->task_type_id);
        $validatedConfig = $this->configValidator->validate($request->config, $taskType->slug);

        $data = $request->all();
        $data['config'] = $validatedConfig;
        $data['is_published'] = $request->has('is_published');
        $data['is_required'] = $request->has('is_required');

        if ($request->has('hints')) {
            $data['hints'] = array_filter($request->hints, function ($hint) {
                return !empty($hint);
            });
        }

        $task->update($data);

        return redirect()
            ->route('admin.tasks.index', ['lesson_id' => $task->lesson_id])
            ->with('success', 'Задание "' . ($task->title ?? '#' . $task->id) . '" успешно обновлено!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $title = $task->title ?? 'Задание #' . $task->id;
        $lessonId = $task->lesson_id;

        $task->delete();

        return redirect()
            ->route('admin.tasks.index', ['lesson_id' => $lessonId])
            ->with('success', 'Задание "' . $title . '" успешно удалено!');
    }

    /**
     * Получить дефолтный конфиг для типа задания (AJAX)
     */
    public function getDefaultConfig(Request $request)
    {
        $taskType = TaskType::find($request->task_type_id);
        if (!$taskType) {
            return response()->json(['error' => 'Тип задания не найден'], 404);
        }

        $defaultConfig = $taskType->default_config ?? [];

        return response()->json([
            'config' => $defaultConfig,
            'validation_rules' => $taskType->getValidationRules(),
        ]);
    }
}
