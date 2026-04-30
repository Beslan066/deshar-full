<?php

namespace App\Http\Controllers\Admin\Localities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Localities\StoreRequest;
use App\Http\Requests\Admin\Localities\UpdateRequest;
use App\Models\Locality;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $localities = Locality::paginate(10);

        return view('admin.locality.index', ['localities' => $localities]);
    }

    public function create() {

        $districts = District::paginate(10);

        return view('admin.locality.create', [
            'districts' => $districts
        ]);
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        $locality = Locality::create($data);

        return redirect()->route('admin.localities.index');
    }

    public function edit(Locality $locality)
    {

        $districts = District::all();

        return view('admin.locality.edit', [
            'locality' => $locality,
            'districts' => $districts
        ]);
    }

    public function update(UpdateRequest $request, Locality $locality) {

        $data = $request->validated();

        $locality->update($data);

        return redirect()->route('admin.localities.index');
    }

    public function destroy(Locality $locality) {

        $locality->delete();

        return redirect()->route('admin.localities.index');
    }
}
