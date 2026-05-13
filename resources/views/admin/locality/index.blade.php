@extends('layouts.admin')

@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="card-header flex-column flex-md-row border-bottom">
                            <div class="head-label text-center"><h5 class="card-title mb-0">Населенные пункты</h5></div>
                            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                                <div>
                                    <div class="btn-group">
                                        <button
                                            class="btn btn-secondary buttons-collection dropdown-toggle btn-label-primary me-4 waves-effect waves-light"
                                            tabindex="0" aria-controls="DataTables_Table_0" type="button"
                                            aria-haspopup="dialog" aria-expanded="false"><span><i
                                                    class="ri-external-link-line me-sm-1"></i> <span
                                                    class="d-none d-sm-inline-block">Export</span></span></button>
                                    </div>
                                    <a href="{{route('admin.localities.create')}}"
                                       class="btn btn-secondary create-new btn-primary waves-effect waves-light"
                                       tabindex="0" aria-controls="DataTables_Table_0"><span><i
                                                class="ri-add-line ri-16px me-sm-2"></i> <span
                                                class="d-none d-sm-inline-block">Добавить</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mt-5 mt-md-0">

                            </div>
                            <div class="col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end">
                                <div id="DataTables_Table_0_filter" class="dataTables_filter"><label><input
                                            type="search" class="form-control form-control-sm" placeholder="Поиск:"
                                            aria-controls="DataTables_Table_0"></label></div>
                            </div>
                        </div>
                        <table class="table">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Заголовок</th>
                                <th>Район</th>
                                <th>Создан</th>
                                <th>Действие</th>
                            </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                            @foreach($localities as $locality)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{$locality->id}}</span>
                                    </td>
                                    <td>{{$locality->name}}</td>
                                    <td>
                                        {{$locality->district->name}}
                                    </td>

                                    <td><span class="badge rounded-pill bg-label-primary me-1">{{$locality->created_at}}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item waves-effect" href="javascript:void(0);"><i class="ri-pencil-line me-1"></i> Edit</a>
                                                <a class="dropdown-item waves-effect" href="javascript:void(0);"><i class="ri-delete-bin-7-line me-1"></i> Delete</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="mt-2">
                            {{$localities->links()}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- / Content -->

    </div>
    <!-- Content wrapper -->
@endsection
