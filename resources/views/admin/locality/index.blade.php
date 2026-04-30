@extends('layouts.admin')

@section('content')
    <div class="layout-page">

        <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                    <i class="ri-menu-fill ri-22px"></i>
                </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                <!-- Search -->
                <div class="navbar-nav align-items-center">
                    <div class="nav-item navbar-search-wrapper mb-0">
                        <a class="nav-item nav-link search-toggler fw-normal px-0" href="javascript:void(0);">
                            <i class="ri-search-line ri-22px scaleX-n1-rtl me-3"></i>
                            <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                        </a>
                    </div>
                </div>
                <!-- /Search -->

                <ul class="navbar-nav flex-row align-items-center ms-auto">

                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                           data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img src="../../assets/img/avatars/1.png" alt="" class="rounded-circle">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item waves-effect" href="pages-account-settings-account.html">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar avatar-online">
                                                <img src="../../assets/img/avatars/1.png" alt="" class="rounded-circle">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-medium d-block small">John Doe</span>
                                            <small class="text-muted">Admin</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="pages-profile-user.html">
                                    <i class="ri-user-3-line ri-22px me-3"></i><span
                                        class="align-middle">My Profile</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="pages-account-settings-account.html">
                                    <i class="ri-settings-4-line ri-22px me-3"></i><span
                                        class="align-middle">Settings</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="pages-account-settings-billing.html">
                        <span class="d-flex align-items-center align-middle">
                          <i class="flex-shrink-0 ri-file-text-line ri-22px me-3"></i>
                          <span class="flex-grow-1 align-middle">Billing</span>
                          <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger">4</span>
                        </span>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="pages-pricing.html">
                                    <i class="ri-money-dollar-circle-line ri-22px me-3"></i><span class="align-middle">Pricing</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="pages-faq.html">
                                    <i class="ri-question-line ri-22px me-3"></i><span class="align-middle">FAQ</span>
                                </a>
                            </li>
                            <li>
                                <div class="d-grid px-4 pt-2 pb-1">
                                    <a class="btn btn-sm btn-danger d-flex waves-effect waves-light"
                                       href="auth-login-cover.html" target="_blank">
                                        <small class="align-middle">Logout</small>
                                        <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <!--/ User -->
                </ul>
            </div>

            <!-- Search Small Screens -->
            <div class="navbar-search-wrapper search-input-wrapper d-none">
                <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..."
                       aria-label="Search...">
                <i class="ri-close-fill search-toggler cursor-pointer"></i>
            </div>
        </nav>
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
    </div>
@endsection
