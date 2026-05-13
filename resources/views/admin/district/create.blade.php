@extends('layouts.admin')

@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Basic Layout -->

            <!-- Multi Column with Form Separator -->
            <div class="card mb-6">
                <h5 class="card-header">Районы - создание</h5>
                <form class="card-body" action="{{route('admin.districts.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('post')
                    <div class="row g-6">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="multicol-username" class="form-control" placeholder="Например: Ученик" name="name">
                                <label for="multicol-username">Название</label>
                            </div>


                            <div class="col-md-6 mb-6 mt-4">
                                <div class="form-floating form-floating-outline">
                                    <select id="selectpickerBasic" class="selectpicker w-100" data-style="btn-default" name="region_id">
                                        @foreach($regions as $region)
                                            <option value="{{$region->id}}">{{$region->name}}</option>
                                        @endforeach
                                    </select>
                                    <label for="selectpickerBasic">Регион</label>
                                </div>
                            </div>

                            <div class="form-floating form-floating-outline mt-4">
                                <select
                                    id="selectpickerLiveSearch"
                                    class="selectpicker w-100"
                                    data-style="btn-default"
                                    data-live-search="true"
                                    name="manager_id">


                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                                <label for="selectpickerLiveSearch">Ответсвенный</label>
                            </div>
                        </div>

                        <div class="">
                            <button type="reset" class="btn btn-outline-secondary waves-effect">Отмена</button>
                            <button type="submit" class="btn btn-primary me-4 waves-effect waves-light">Создать</button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
        <!-- / Content -->

        <div class="content-backdrop fade"></div>
    </div>
    <!-- Content wrapper -->
@endsection
