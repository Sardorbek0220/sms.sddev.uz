@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Редактировать оператор')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('operators.update', ['operator'=>$operator->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                  <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Имя</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" name="name" id="name" value="{{$operator->name}}">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="phone" class="col-sm-2 col-form-label">Телефон</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" name="phone" id="phone" value="{{$operator->phone}}">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="workly_id" class="col-sm-2 col-form-label">Workly ID</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" name="workly_id" id="workly_id" value="{{$operator->workly_id}}">
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="active" id="active" @if($operator->active == 'Y') checked @endif>
                        <label class="form-check-label" for="active">Активный</label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="field" id="field" @if($operator->field == '1') checked @endif>
                        <label class="form-check-label" for="field">Не оператор</label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                      <div>
                        <input type="color" name="color" id="color" value="{{$operator->color}}" style="height: 25px;width: 25px;">
                        <label class="form-check-label" for="color">Цвет</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success">Сохранить</button>
                  <a href="{{ route('operators.index') }}" class="btn btn-default float-right">Отмена</a>
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <script src="{{ asset('assets/plugins/jquery/jquery.min.js')}}"></script>
  <!-- <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js')}}"></script> -->
@endsection