@extends('layouts.admin')

@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Basic Layout -->

            <!-- Multi Column with Form Separator -->
            <div class="card mb-6">
                <h5 class="card-header">Список школ - создание</h5>
                <form class="card-body" action="{{route('admin.schools.store')}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    @method('post')
                    <div class="row g-6">

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="formtabs-first-name" class="form-control" placeholder="СОШ №1" name="name">
                                <label for="selectpickerBasic">Название</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select id="selectpickerBasic" class="selectpicker w-100"
                                        data-style="btn-default" name="district_id">
                                    @foreach($districts as $district)
                                        <option value="{{$district->id}}">{{$district->name}}</option>
                                    @endforeach
                                </select>
                                <label for="selectpickerBasic">Район</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select id="selectpickerBasic" class="selectpicker w-100"
                                        data-style="btn-default" name="locality_id">
                                    @foreach($localities as $locality)
                                        <option value="{{$locality->id}}">{{$locality->name}}</option>
                                    @endforeach
                                </select>
                                <label for="selectpickerBasic">Населенные пункты</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select id="selectpickerBasic" class="selectpicker w-100"
                                        data-style="btn-default" name="manager_id">
                                    @foreach($managers as $manager)
                                        <option value="{{$manager->id}}">{{$manager->name}}</option>
                                    @endforeach
                                </select>
                                <label for="selectpickerBasic">Ответственный по школе</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-6 mt-4">

                            <div class="form-floating form-floating-outline">
                                <select id="selectpickerBasic" class="selectpicker w-100"
                                        data-style="btn-default" name="director_id">
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{$supervisor->id}}">{{$supervisor->name}}</option>
                                    @endforeach
                                </select>
                                <label for="selectpickerBasic">Директор</label>
                            </div>
                        </div>

                        <div class="">
                            <button type="reset" class="btn btn-outline-secondary waves-effect">Отмена</button>
                            <button type="submit" class="btn btn-primary me-4 waves-effect waves-light">Создать
                            </button>
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

