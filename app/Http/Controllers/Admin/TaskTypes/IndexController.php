<?php

namespace App\Http\Controllers\Admin\TaskTypes;

use App\Http\Controllers\Controller;
use App\Models\TaskType;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taskTypes = TaskType::orderBy('sort_order')->paginate(20);
        return view('admin.task_types.index', compact('taskTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.task_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Подготавливаем все данные
        $data = $request->all();

        // Преобразуем is_active в boolean ДО валидации
        $data['is_active'] = $request->has('is_active') && $request->is_active === 'on';

        // Преобразуем default_config из JSON строки в массив
        if ($request->has('default_config') && !empty($request->default_config)) {
            $jsonString = trim($request->default_config);
            $defaultConfig = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $data['default_config'] = $defaultConfig;
            } else {
                return back()
                    ->withInput()
                    ->withErrors(['default_config' => 'Неверный формат JSON: ' . json_last_error_msg()]);
            }
        } else {
            $data['default_config'] = [];
        }

        // 2. Валидируем подготовленные данные
        $validator = validator($data, [
            'slug' => 'required|string|max:50|unique:task_types,slug',
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'default_config' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator);
        }

        $validatedData = $validator->validated();
        $validatedData['sort_order'] = $request->sort_order ?? 0;

        $taskType = TaskType::create($validatedData);

        return redirect()
            ->route('admin.taskTypes.index')
            ->with('success', 'Тип задания "' . $taskType->name . '" успешно создан!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskType $taskType)
    {
        return view('admin.task_types.edit', compact('taskType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskType $taskType)
    {
        // 1. Подготавливаем все данные (так же как в store)
        $data = $request->all();

        // Преобразуем is_active в boolean ДО валидации
        $data['is_active'] = $request->has('is_active') && $request->is_active === 'on';

        // Преобразуем default_config из JSON строки в массив
        if ($request->has('default_config') && !empty($request->default_config)) {
            $jsonString = trim($request->default_config);
            $defaultConfig = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $data['default_config'] = $defaultConfig;
            } else {
                return back()
                    ->withInput()
                    ->withErrors(['default_config' => 'Неверный формат JSON: ' . json_last_error_msg()]);
            }
        } else {
            $data['default_config'] = [];
        }

        // 2. Валидируем подготовленные данные
        $validator = validator($data, [
            'slug' => 'required|string|max:50|unique:task_types,slug,' . $taskType->id,
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'default_config' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator);
        }

        // 3. Подготавливаем финальные данные
        $validatedData = $validator->validated();
        $validatedData['sort_order'] = $request->sort_order ?? 0;

        $taskType->update($validatedData);

        return redirect()
            ->route('admin.taskTypes.index')
            ->with('success', 'Тип задания "' . $taskType->name . '" успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskType $taskType)
    {
        $name = $taskType->name;
        $taskType->delete();

        return redirect()
            ->route('admin.taskTypes.index')
            ->with('success', 'Тип задания "' . $name . '" успешно удален!');
    }
}
