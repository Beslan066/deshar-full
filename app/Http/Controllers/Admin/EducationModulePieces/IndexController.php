<?php

namespace App\Http\Controllers\Admin\EducationModulePieces;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\EducationModulePiece;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EducationModulePiece::with('educationModule');

        // Фильтр по модулю
        if ($request->has('module_id') && $request->module_id) {
            $query->where('education_module_id', $request->module_id);
        }

        $pieces = $query->orderBy('sort_order')->paginate(20);

        return view('admin.module-piece.index', [
            'pieces' => $pieces,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = EducationModule::where('is_published', true)->get();
        return view('admin.module-piece.create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'education_module_id' => 'required|exists:education_modules,id',
            'fon' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'xp_reward' => 'nullable|integer',
            'estimated_time' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('fon')) {
            $path = $request->file('fon')->store('pieces', 'public');
            $data['fon'] = '/storage/' . $path;
        }

        // ✅ ВАЖНО: Явно приводим к boolean
        $data['is_published'] = $request->has('is_published') ? true : false;
        $data['is_required'] = $request->has('is_required') ? true : false;
        $data['sort_order'] = (int) ($request->sort_order ?? 0);

        $piece = EducationModulePiece::create($data);

        return redirect()
            ->route('admin.educationModulesPieces.index')
            ->with('success', 'Раздел "' . $piece->name . '" успешно создан!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EducationModulePiece $piece)
    {
        $modules = EducationModule::where('is_published', true)->get();
        return view('admin.module-piece.edit', compact('piece', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EducationModulePiece $piece)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'education_module_id' => 'required|exists:education_modules,id',
            'fon' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'xp_reward' => 'nullable|integer',
            'estimated_time' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('fon')) {
            if ($piece->fon && file_exists(public_path($piece->fon))) {
                unlink(public_path($piece->fon));
            }
            $path = $request->file('fon')->store('pieces', 'public');
            $data['fon'] = '/storage/' . $path;
        }

        // ✅ ВАЖНО: Явно приводим к boolean
        $data['is_published'] = $request->has('is_published') ? true : false;
        $data['is_required'] = $request->has('is_required') ? true : false;

        $piece->update($data);

        return redirect()
            ->route('admin.educationModulesPieces.index')
            ->with('success', 'Раздел "' . $piece->name . '" успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EducationModulePiece $piece)
    {
        $name = $piece->name;

        if ($piece->fon && file_exists(public_path($piece->fon))) {
            unlink(public_path($piece->fon));
        }

        $piece->delete();

        return redirect()
            ->route('admin.educationModulesPieces.index')
            ->with('success', 'Раздел "' . $name . '" успешно удален!');
    }
}
