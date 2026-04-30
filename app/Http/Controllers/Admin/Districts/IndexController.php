<?php

namespace App\Http\Controllers\Admin\Districts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Districts\StoreRequest;
use App\Http\Requests\Admin\Districts\UpdateRequest;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $districts = District::paginate(10);

        return view('admin.district.index', ['districts' => $districts]);
    }

    public function create() {

        $users = User::all();

        $regions = Region::all();

        return view('admin.district.create', [
            'users' => $users,
            'regions' => $regions
        ]);
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        $district = District::create($data);

        return redirect()->route('admin.districts.index');
    }

    public function edit(District $district)
    {

        return view('admin.district.edit', compact('district'));
    }

    public function update(UpdateRequest $request, District $district) {

        $data = $request->validated();

        $district->update($data);

        return redirect()->route('admin.districts.index');
    }

    public function destroy(District $district) {

        $district->delete();

        return redirect()->route('admin.district.index');
    }
}
