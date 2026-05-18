@extends('layouts.admin')

@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Basic Layout -->

            <!-- Multi Column with Form Separator -->
            <div class="card mb-6">
                <h5 class="card-header">Список образовательных модулей - создание</h5>
                <form class="card-body" action="{{route('admin.educationModules.store')}}" method="post"
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
                                        data-style="btn-default" name="school_class_type_id">
                                    @foreach($schoolClassTypes as $class)
                                        <option value="{{$class->id}}">{{$class->name}}</option>
                                    @endforeach
                                </select>
                                <label for="selectpickerBasic">Для какого класса</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="formtabs-first-name" class="form-control" placeholder="Легко, сложно и т.п" name="complexity">
                                <label for="selectpickerBasic">Сложность</label>
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

