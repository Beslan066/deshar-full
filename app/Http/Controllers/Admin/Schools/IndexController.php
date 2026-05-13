<?php

namespace App\Http\Controllers\Admin\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Schools\StoreRequest;
use App\Http\Requests\Admin\Schools\UpdateRequest;
use App\Models\District;
use App\Models\Locality;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $schools = School::paginate(10);

        return view('admin.school.index', ['schools' => $schools]);
    }

    public function create() {

        $localities = Locality::paginate(10);
        $districts = District::paginate(10);
        $managers = User::query()->where('role_id', '2')->paginate(10);
        $supervisors = User::query()->where('role_id', '6')->paginate(10);

        return view('admin.school.create', [
            'localities' => $localities,
            'districts' => $districts,
            'managers' => $managers,
            'supervisors' => $supervisors,
        ]);
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        // Установка дефолтного значения для country_id
        $data['country_id'] = $request->input('country_id', 1);
        $data['region_id'] = $request->input('region_id', 1);

        $school = School::create($data);

        return redirect()->route('admin.schools.index');
    }

    public function edit(School $school)
    {

        $localities = Locality::all();

        return view('admin.school.edit', [
            'school' => $school,
            'localities' => $localities
        ]);
    }

    public function update(UpdateRequest $request, School $school) {

        $data = $request->validated();

        $school->update($data);

        return redirect()->route('admin.schools.index');
    }

    public function destroy(School $school) {

        $school->delete();

        return redirect()->route('admin.schools.index');
    }
}

