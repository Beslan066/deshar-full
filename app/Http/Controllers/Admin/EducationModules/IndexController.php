<?php

namespace App\Http\Controllers\Admin\EducationModules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EducationModules\StoreRequest;
use App\Http\Requests\Admin\EducationModules\UpdateRequest;
use App\Models\EducationModule;
use App\Models\SchoolClass;
use App\Models\SchoolClassType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function index()
    {
        $modules = EducationModule::paginate(10);

        return view('admin.module.index', ['modules' => $modules]);
    }

    public function create() {

        $schoolClassTypes = SchoolClassType::all();

        return view('admin.module.create', [
            'schoolClassTypes' => $schoolClassTypes
        ]);
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        if (isset($data['image'])) {
            $path = Storage::put('images', $data['image']);
            $data['image'] = $path ?? null;
        }

        $module = EducationModule::create($data);

        return redirect()->route('admin.educationModules.index');
    }

    public function edit(EducationModule $educationModule)
    {
        $schoolClassTypes = SchoolClassType::all();


        return view('admin.module.edit', [
            'schoolClassTypes' => $schoolClassTypes,
        ]);
    }

    public function update(UpdateRequest $request, EducationModule $educationModule) {

        $data = $request->validated();

        if (isset($data['image'])) {
            // Удаляем старое изображение
            if ($educationModule->image) {
                Storage::delete($educationModule->image);
            }
            $path = Storage::put('images', $data['image']);
            $data['image'] = $path;
        }

        $educationModule->update($data);

        return redirect()->route('admin.educationModules.index');
    }

    public function destroy(EducationModule $educationModule) {

        $educationModule->delete();

        return redirect()->route('admin.educationModules.index');
    }
}

