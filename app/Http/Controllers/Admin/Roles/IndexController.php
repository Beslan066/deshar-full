<?php

namespace App\Http\Controllers\Admin\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\StoreRequest;
use App\Http\Requests\Admin\Role\UpdateRequest;
use App\Models\Country;
use App\Models\Role;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $roles = Role::paginate(10);
        return view('admin.role.index', [
            'roles' => $roles,
        ]);
    }

    public function create() {

        return view('admin.role.create');
    }

    public function store(StoreRequest $request) {

        $data = $request->validated();

        $role = Role::create($data);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {

        return view('admin.role.edit', compact('role'));
    }

    public function update(UpdateRequest $request, Role $role) {
        $data = $request->validated();

        $role->update($data);

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role) {

        $role->delete();

        return redirect()->route('admin.roles.index');
    }
}
