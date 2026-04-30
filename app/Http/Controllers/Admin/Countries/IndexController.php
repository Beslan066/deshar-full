<?php

namespace App\Http\Controllers\Admin\Countries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Country\StoreRequest;
use App\Http\Requests\Admin\Country\UpdateRequest;
use App\Models\Country;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $countries = Country::paginate(10);

        return view('admin.country.index', ['countries' => $countries]);
    }

    public function create() {

        return view('admin.country.create');
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        $country = Country::create($data);

        return redirect()->route('admin.countries.index');
    }

    public function edit(Country $country)
    {

        return view('admin.country.edit', compact('country'));
    }

    public function update(UpdateRequest $request, Country $country) {
        $data = $request->validated();

        $country->update($data);

        return redirect()->route('admin.countries.index');
    }

    public function destroy(Country $country) {

        $country->delete();

        return redirect()->route('admin.countries.index');
    }
}
