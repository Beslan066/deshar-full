@extends('layouts.admin')

@section('content')

    <!-- Вывод flash сообщений -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="card-header flex-column flex-md-row border-bottom">
                            <div class="head-label text-center">
                                <h5 class="card-title mb-0">Список пользователей</h5>
                            </div>
                            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                                <div>
                                    <div>
                                        <button type="button"
                                                class="btn btn-secondary dropdown-toggle waves-effect waves-light"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            Фильтр
                                        </button>
                                        <a href="{{ route('admin.users.create') }}"
                                           class="btn btn-secondary create-new btn-primary waves-effect waves-light">
                                                <span><i class="ri-add-line ri-16px me-sm-2"></i>
                                                <span class="d-none d-sm-inline-block">Добавить</span></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Форма фильтрации -->
                        <div class="row mb-3  align-items-center">
                            <div class="col-md-3">
                                <select class="form-select" id="role_filter" onchange="filterUsers()">
                                    <option value="">Все роли</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="user_type_filter" onchange="filterUsers()">
                                    <option value="">Все типы</option>
                                    <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Ученик</option>
                                    <option value="teacher" {{ request('user_type') == 'teacher' ? 'selected' : '' }}>Учитель</option>
                                    <option value="parent" {{ request('user_type') == 'parent' ? 'selected' : '' }}>Родитель</option>
                                    <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Админ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="dataTables_filter">
                                    <label>
                                        <input type="search" class="form-control" id="search_input"
                                               placeholder="Поиск по имени или email..."
                                               value="{{ request('search') }}"
                                               onkeyup="if(event.keyCode==13) filterUsers()">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <table class="table">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Аватар</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Тип</th>
                                <th>Роль</th>
                                <th>Школа/Класс</th>
                                <th>Баллы</th>
                                <th>Дата рождения</th>
                                <th>Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td><span class="fw-medium">{{ $user->id }}</span></td>
                                    <td>
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                                 alt="avatar" class="rounded-circle"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="avatar avatar-sm rounded-circle bg-label-primary">
                                                <span class="avatar-initial">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                            <span class="badge rounded-pill bg-label-{{
                                                $user->user_type == 'admin' ? 'danger' :
                                                ($user->user_type == 'teacher' ? 'info' :
                                                ($user->user_type == 'parent' ? 'warning' : 'primary'))
                                            }}">
                                                {{ __('user_types.' . $user->user_type) }}
                                            </span>
                                    </td>
                                    <td>{{ $user->role?->name ?? '-' }}</td>
                                    <td>
                                        @if($user->school)
                                            {{ $user->school->name }}
                                            @if($user->schoolClass)
                                                <br><small class="text-muted">{{ $user->schoolClass->name }}</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="badge bg-label-success">{{ $user->points }}</span></td>
                                    <td>{{ $user->birth_date ? date('d.m.Y', strtotime($user->birth_date)) : '-' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item waves-effect"
                                                   href="{{ route('admin.users.edit', $user->id) }}">
                                                    <i class="ri-pencil-line me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.users.destroy', $user->id) }}"
                                                      method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item waves-effect delete-btn">
                                                        <i class="ri-delete-bin-7-line me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">Пользователи не найдены</div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3 px-3">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterUsers() {
            const role = document.getElementById('role_filter').value;
            const userType = document.getElementById('user_type_filter').value;
            const search = document.getElementById('search_input').value;

            let url = "{{ route('admin.users.index') }}?";
            if (role) url += `role_id=${role}&`;
            if (userType) url += `user_type=${userType}&`;
            if (search) url += `search=${search}&`;

            window.location.href = url;
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
