<?php

namespace App\Http\Controllers\Admin\Cities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cities\StoreRequest;
use App\Http\Requests\Admin\Cities\UpdateRequest;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $cities = City::paginate(10);

        return view('admin.city.index', ['cities' => $cities]);
    }

    public function create() {

        $countries = Country::all();

        return view('admin.city.create', [
            'countries' => $countries
        ]);
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        $city = City::create($data);

        return redirect()->route('admin.cities.index');
    }

    public function edit(City $city)
    {

        return view('admin.city.edit', compact('city'));
    }

    public function update(UpdateRequest $request, City $city) {

        $data = $request->validated();

        $city->update($data);

        return redirect()->route('admin.cities.index');
    }

    public function destroy(City $city) {

        $city->delete();

        return redirect()->route('admin.cities.index');
    }
}
