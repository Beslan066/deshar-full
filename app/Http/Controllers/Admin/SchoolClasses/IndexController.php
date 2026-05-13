<?php

namespace App\Http\Controllers\Admin\SchoolClasses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolClasses\StoreRequest;
use App\Http\Requests\Admin\SchoolClasses\UpdateRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $schoolClasses = SchoolClass::paginate(10);

        return view('admin.school-class.index', ['classes' => $schoolClasses]);
    }

    public function create() {

        $schools = School::paginate(10);
        $teachers = User::query()->where('role_id', '8')->paginate(10);

        return view('admin.school-class.create', [
            'schools' => $schools,
            'teachers' => $teachers,
        ]);
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        $schoolClass = SchoolClass::create($data);

        return redirect()->route('admin.schoolClasses.index');
    }

    public function edit(SchoolClass $schoolClass)
    {

        $schools = School::paginate(10);
        $teachers = User::query()->where('role_id', '8')->paginate(10);

        return view('admin.school-class.edit', [
            'schools' => $schools,
            'teachers' => $teachers,
            'schoolClass' => $schoolClass,
        ]);
    }

    public function update(UpdateRequest $request, SchoolClass $schoolClass) {

        $data = $request->validated();

        $schoolClass->update($data);

        return redirect()->route('admin.schoolClasses.index');
    }

    public function destroy(SchoolClass $schoolClass) {

        $schoolClass->delete();

        return redirect()->route('admin.schoolClasses.index');
    }
}
