<?php

namespace App\Http\Controllers\Admin\EducationModules;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\SchoolClassType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = EducationModule::with('schoolClassType')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.module.index', compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schoolClassTypes = SchoolClassType::all();
        return view('admin.module.create', compact('schoolClassTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'school_class_type_id' => 'required|exists:school_class_types,id',
            'complexity' => 'nullable|integer|min:1|max:5',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('modules', 'public');
            $data['image'] = '/storage/' . $path;
        }

        // ✅ ВАЖНО: правильно обрабатываем is_published
        $data['is_published'] = $request->has('is_published') ? true : false;
        $data['sort_order'] = $request->sort_order ?? 0;

        $module = EducationModule::create($data);

        return redirect()
            ->route('admin.educationModules.index')
            ->with('success', 'Модуль "' . $module->name . '" успешно создан!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EducationModule $educationModule)
    {
        $schoolClassTypes = SchoolClassType::all();
        return view('admin.module.edit', compact('educationModule', 'schoolClassTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EducationModule $educationModule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'school_class_type_id' => 'required|exists:school_class_types,id',
            'complexity' => 'nullable|integer|min:1|max:5',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            // Удаляем старую картинку
            if ($educationModule->image && file_exists(public_path($educationModule->image))) {
                unlink(public_path($educationModule->image));
            }
            $path = $request->file('image')->store('modules', 'public');
            $data['image'] = '/storage/' . $path;
        }

        // ✅ ВАЖНО: правильно обрабатываем is_published
        $data['is_published'] = $request->has('is_published') ? true : false;

        $educationModule->update($data);

        return redirect()
            ->route('admin.educationModules.index')
            ->with('success', 'Модуль "' . $educationModule->name . '" успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EducationModule $educationModule)
    {
        $name = $educationModule->name;

        // Удаляем картинку
        if ($educationModule->image && file_exists(public_path($educationModule->image))) {
            unlink(public_path($educationModule->image));
        }

        $educationModule->delete();

        return redirect()
            ->route('admin.educationModules.index')
            ->with('success', 'Модуль "' . $name . '" успешно удален!');
    }
}
