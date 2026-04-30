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
                    <!-- Language -->
                    <li class="nav-item dropdown-language dropdown">
                        <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow waves-effect waves-light"
                           href="javascript:void(0);" data-bs-toggle="dropdown">
                            <i class="ri-translate-2 ri-22px"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item waves-effect active" href="javascript:void(0);"
                                   data-language="en" data-text-direction="ltr">
                                    <span class="align-middle">English</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="javascript:void(0);" data-language="fr"
                                   data-text-direction="ltr">
                                    <span class="align-middle">French</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="javascript:void(0);" data-language="ar"
                                   data-text-direction="rtl">
                                    <span class="align-middle">Arabic</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="javascript:void(0);" data-language="de"
                                   data-text-direction="ltr">
                                    <span class="align-middle">German</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!--/ Language -->

                    <!-- Style Switcher -->
                    <li class="nav-item dropdown-style-switcher dropdown me-1 me-xl-0">
                        <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow waves-effect waves-light"
                           href="javascript:void(0);" data-bs-toggle="dropdown">
                            <i class="ri-22px ri-sun-line"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                            <li>
                                <a class="dropdown-item waves-effect active" href="javascript:void(0);"
                                   data-theme="light">
                                    <span class="align-middle"><i class="ri-sun-line ri-22px me-3"></i>Light</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="javascript:void(0);" data-theme="dark">
                                    <span class="align-middle"><i
                                            class="ri-moon-clear-line ri-22px me-3"></i>Dark</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item waves-effect" href="javascript:void(0);" data-theme="system">
                                    <span class="align-middle"><i
                                            class="ri-computer-line ri-22px me-3"></i>System</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- / Style Switcher-->

                    <!-- Quick links  -->
                    <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-1 me-xl-0">
                        <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow waves-effect waves-light"
                           href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                           aria-expanded="false">
                            <i class="ri-star-smile-line ri-22px"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="dropdown-menu-header border-bottom py-50">
                                <div class="dropdown-header d-flex align-items-center py-2">
                                    <h6 class="mb-0 me-auto">Shortcuts</h6>
                                    <a href="javascript:void(0)"
                                       class="btn btn-text-secondary rounded-pill btn-icon dropdown-shortcuts-add text-heading waves-effect waves-light"
                                       data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Add shortcuts"
                                       data-bs-original-title="Add shortcuts"><i class="ri-add-line ri-24px"></i></a>
                                </div>
                            </div>
                            <div class="dropdown-shortcuts-list scrollable-container ps">
                                <div class="row row-bordered overflow-visible g-0">
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-calendar-line ri-26px text-heading"></i>
                          </span>
                                        <a href="app-calendar.html" class="stretched-link">Calendar</a>
                                        <small class="mb-0">Appointments</small>
                                    </div>
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-file-text-line ri-26px text-heading"></i>
                          </span>
                                        <a href="app-invoice-list.html" class="stretched-link">Invoice App</a>
                                        <small class="mb-0">Manage Accounts</small>
                                    </div>
                                </div>
                                <div class="row row-bordered overflow-visible g-0">
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-user-line ri-26px text-heading"></i>
                          </span>
                                        <a href="app-user-list.html" class="stretched-link">User App</a>
                                        <small class="mb-0">Manage Users</small>
                                    </div>
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-computer-line ri-26px text-heading"></i>
                          </span>
                                        <a href="app-access-roles.html" class="stretched-link">Role Management</a>
                                        <small class="mb-0">Permission</small>
                                    </div>
                                </div>
                                <div class="row row-bordered overflow-visible g-0">
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-pie-chart-2-line ri-26px text-heading"></i>
                          </span>
                                        <a href="index.html" class="stretched-link">Dashboard</a>
                                        <small class="mb-0">Analytics</small>
                                    </div>
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-settings-4-line ri-26px text-heading"></i>
                          </span>
                                        <a href="pages-account-settings-account.html" class="stretched-link">Setting</a>
                                        <small class="mb-0">Account Settings</small>
                                    </div>
                                </div>
                                <div class="row row-bordered overflow-visible g-0">
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-question-line ri-26px text-heading"></i>
                          </span>
                                        <a href="pages-faq.html" class="stretched-link">FAQs</a>
                                        <small class="mb-0">FAQs &amp; Articles</small>
                                    </div>
                                    <div class="dropdown-shortcuts-item col">
                          <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="ri-tv-2-line ri-26px text-heading"></i>
                          </span>
                                        <a href="modal-examples.html" class="stretched-link">Modals</a>
                                        <small class="mb-0">Useful Popups</small>
                                    </div>
                                </div>
                                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                                </div>
                                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- Quick links -->

                    <!-- Notification -->
                    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-4 me-xl-1">
                        <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow waves-effect waves-light"
                           href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                           aria-expanded="false">
                            <i class="ri-notification-2-line ri-22px"></i>
                            <span
                                class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end py-0">
                            <li class="dropdown-menu-header border-bottom py-50">
                                <div class="dropdown-header d-flex align-items-center py-2">
                                    <h6 class="mb-0 me-auto">Notification</h6>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-pill bg-label-primary fs-xsmall me-2">8 New</span>
                                        <a href="javascript:void(0)"
                                           class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all waves-effect waves-light"
                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                           aria-label="Mark all as read" data-bs-original-title="Mark all as read"><i
                                                class="ri-mail-open-line text-heading ri-20px"></i></a>
                                    </div>
                                </div>
                            </li>
                            <li class="dropdown-notifications-list scrollable-container ps">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <img src="../../assets/img/avatars/1.png" alt=""
                                                         class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="small mb-1">Congratulation Lettie 🎉</h6>
                                                <small class="mb-1 d-block text-body">Won the monthly best seller gold
                                                    badge</small>
                                                <small class="text-muted">1h ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span
                                                        class="avatar-initial rounded-circle bg-label-danger">CF</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">Charles Franklin</h6>
                                                <small class="mb-1 d-block text-body">Accepted your connection</small>
                                                <small class="text-muted">12hr ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <img src="../../assets/img/avatars/2.png" alt=""
                                                         class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">New Message ✉️</h6>
                                                <small class="mb-1 d-block text-body">You have new message from
                                                    Natalie</small>
                                                <small class="text-muted">1h ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded-circle bg-label-success"><i
                                                            class="ri-shopping-cart-2-line"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">Whoo! You have new order 🛒</h6>
                                                <small class="mb-1 d-block text-body">ACME Inc. made new order
                                                    $1,154</small>
                                                <small class="text-muted">1 day ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <img src="../../assets/img/avatars/9.png" alt=""
                                                         class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">Application has been approved 🚀</h6>
                                                <small class="mb-1 d-block text-body">Your ABC project application has
                                                    been approved.</small>
                                                <small class="text-muted">2 days ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded-circle bg-label-success"><i
                                                            class="ri-pie-chart-2-line"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">Monthly report is generated</h6>
                                                <small class="mb-1 d-block text-body">July monthly financial report is
                                                    generated </small>
                                                <small class="text-muted">3 days ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <img src="../../assets/img/avatars/5.png" alt=""
                                                         class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">Send connection request</h6>
                                                <small class="mb-1 d-block text-body">Peter sent you connection
                                                    request</small>
                                                <small class="text-muted">4 days ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <img src="../../assets/img/avatars/6.png" alt=""
                                                         class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">New message from Jane</h6>
                                                <small class="mb-1 d-block text-body">Your have new message from
                                                    Jane</small>
                                                <small class="text-muted">5 days ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read waves-effect">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded-circle bg-label-warning"><i
                                                            class="ri-error-warning-line"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 small">CPU is running high</h6>
                                                <small class="mb-1 d-block text-body">CPU Utilization Percent is
                                                    currently at 88.63%,</small>
                                                <small class="text-muted">5 days ago</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                                        class="badge badge-dot"></span></a>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-notifications-archive"><span
                                                        class="ri-close-line ri-20px"></span></a>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                                </div>
                                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                                </div>
                            </li>
                            <li class="border-top">
                                <div class="d-grid p-4">
                                    <a class="btn btn-primary btn-sm d-flex waves-effect waves-light"
                                       href="javascript:void(0);">
                                        <small class="align-middle">View all notifications</small>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <!--/ Notification -->

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
                                <div class="head-label text-center"><h5 class="card-title mb-0">Роли</h5></div>
                                <div class="dt-action-buttons text-end pt-3 pt-md-0">
                                    <div class="dt-buttons btn-group flex-wrap">
                                        <div class="btn-group">
                                            <button
                                                class="btn btn-secondary buttons-collection dropdown-toggle btn-label-primary me-4 waves-effect waves-light"
                                                tabindex="0" aria-controls="DataTables_Table_0" type="button"
                                                aria-haspopup="dialog" aria-expanded="false"><span><i
                                                        class="ri-external-link-line me-sm-1"></i> <span
                                                        class="d-none d-sm-inline-block">Export</span></span></button>
                                        </div>
                                        <button
                                            class="btn btn-secondary create-new btn-primary waves-effect waves-light"
                                            tabindex="0" aria-controls="DataTables_Table_0" type="button"><span><i
                                                    class="ri-add-line ri-16px me-sm-2"></i> <span
                                                    class="d-none d-sm-inline-block">Добавить</span></span>
                                        </button>
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
                                    <th>Project</th>
                                    <th>Client</th>
                                    <th>Users</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                <tr>
                                    <td>
                                        <i class="ri-suitcase-2-line ri-22px text-danger me-4"></i><span class="fw-medium">Tours Project</span>
                                    </td>
                                    <td>Albert Cook</td>
                                    <td>
                                        <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Lilian Fuller" data-bs-original-title="Lilian Fuller">
                                                <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson" data-bs-original-title="Sophia Wilkerson">
                                                <img src="../../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Christina Parker" data-bs-original-title="Christina Parker">
                                                <img src="../../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                        </ul>
                                    </td>
                                    <td><span class="badge rounded-pill bg-label-primary me-1">Active</span></td>
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
                                <tr>
                                    <td>
                                        <i class="ri-basketball-fill ri-22px text-info me-4"></i><span class="fw-medium">Sports Project</span>
                                    </td>
                                    <td>Barry Hunter</td>
                                    <td>
                                        <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Lilian Fuller" data-bs-original-title="Lilian Fuller">
                                                <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson" data-bs-original-title="Sophia Wilkerson">
                                                <img src="../../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Christina Parker" data-bs-original-title="Christina Parker">
                                                <img src="../../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                        </ul>
                                    </td>
                                    <td><span class="badge rounded-pill bg-label-success me-1">Completed</span></td>
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
                                <tr>
                                    <td>
                                        <i class="ri-leaf-fill ri-22px text-success me-4"></i><span class="fw-medium">Greenhouse Project</span>
                                    </td>
                                    <td>Trevor Baker</td>
                                    <td>
                                        <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Lilian Fuller" data-bs-original-title="Lilian Fuller">
                                                <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson" data-bs-original-title="Sophia Wilkerson">
                                                <img src="../../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Christina Parker" data-bs-original-title="Christina Parker">
                                                <img src="../../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                        </ul>
                                    </td>
                                    <td><span class="badge rounded-pill bg-label-info me-1">Scheduled</span></td>
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
                                <tr>
                                    <td>
                                        <i class="ri-bank-fill ri-22px text-primary me-4"></i><span class="fw-medium">Bank Project</span>
                                    </td>
                                    <td>Jerry Milton</td>
                                    <td>
                                        <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Lilian Fuller" data-bs-original-title="Lilian Fuller">
                                                <img src="../../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson" data-bs-original-title="Sophia Wilkerson">
                                                <img src="../../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="Christina Parker" data-bs-original-title="Christina Parker">
                                                <img src="../../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                        </ul>
                                    </td>
                                    <td><span class="badge rounded-pill bg-label-warning me-1">Pending</span></td>
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
                                </tbody>
                            </table>
                            <div class="mt-2">
                                {{$roles->links()}}
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
