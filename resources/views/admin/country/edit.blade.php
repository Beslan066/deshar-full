@extends('layouts.admin')

@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Basic Layout -->

            <!-- Multi Column with Form Separator -->
            <div class="card mb-6">
                <h5 class="card-header">Страны - изменение</h5>
                <form class="card-body" action="{{route('admin.countries.update', $country->id)}}" method="post">
                    @csrf
                    @method('PATCH')
                    <div class="row g-6">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="multicol-username" class="form-control" placeholder="Например: Ученик" name="name" value="{{$country->name}}">
                                <label for="multicol-username">Роль</label>
                            </div>
                        </div>

                        <div class="">
                            <button type="reset" class="btn btn-outline-secondary waves-effect">Отмена</button>
                            <button type="submit" class="btn btn-primary me-4 waves-effect waves-light">Обновить</button>
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
