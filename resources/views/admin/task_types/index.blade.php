@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Типы заданий</h5>
                    <a href="{{ route('admin.taskTypes.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Создать тип
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Иконка</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Заданий</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($taskTypes as $type)
                                <tr>
                                    <td>{{ $type->id }}</td>
                                    <td>
                                        @if($type->icon)
                                            <i class="{{ $type->icon }}"></i>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $type->name }}</td>
                                    <td><code>{{ $type->slug }}</code></td>
                                    <td>
                                        <span class="badge bg-info">{{ $type->tasks_count ?? $type->tasks()->count() }}</span>
                                    </td>
                                    <td>
                                    <span class="badge bg-{{ $type->is_active ? 'success' : 'secondary' }}">
                                        {{ $type->is_active ? 'Активен' : 'Неактивен' }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="{{ route('admin.taskTypes.edit', $type) }}" class="dropdown-item">
                                                        <i class="bx bx-edit"></i> Редактировать
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.taskTypes.delete', $type) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Удалить тип задания?')">
                                                            <i class="bx bx-trash"></i> Удалить
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Нет типов заданий</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $taskTypes->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
