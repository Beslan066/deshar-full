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
        $request->validate([
            'slug' => 'required|string|max:50|unique:task_types,slug',
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'default_config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $request->sort_order ?? 0;

        $taskType = TaskType::create($data);

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
        $request->validate([
            'slug' => 'required|string|max:50|unique:task_types,slug,' . $taskType->id,
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'default_config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $taskType->update($data);

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
