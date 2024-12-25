@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Скрипт / Продукт')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('products.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                  <div class="form-group row">
                    <div class="col-sm-2">
                      <select class="form-control" name="data[0][operator]">
                        @foreach($operators as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="data[0][client_phone]" placeholder="Телефон клиента">
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="data[0][audio_url]" placeholder="URL-адрес аудио">
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="data[0][requestt]" placeholder="Запрос">
                    </div>
                    <div class="col-sm-1">
                      <select class="form-control" name="data[0][request_type_id]">
                        <option value="">Типы запросов</option>
                        @foreach($request_types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="data[0][response]" placeholder="Ответ">
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="data[0][comment]" placeholder="Комментарий">
                    </div>
                    <div class="col-sm-1">
                        <input type="date" class="form-control" name="data[0][date]" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="col-sm-1">
                        <input type="number" class="form-control" name="data[0][script]" placeholder="Скрипт" min="0" max="10">
                    </div>
                    <div class="col-sm-1">
                        <input type="number" class="form-control" name="data[0][product]" placeholder="Продукт" min="0" max="10">
                    </div>
                    <div class="col-sm-1">
                        <input type="number" class="form-control" name="data[0][solution]" placeholder="Оценка решения" min="0" max="10">
                    </div>
                  </div>
                  <button type="button" class="btn btn-success float-left mt-2" id="appendValues">+</button>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Сохранять</button>
                  <a href="{{ route('products.index') }}" class="btn btn-default float-right">Отмена</a>
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
<script>
var operators = <?=json_encode($operators)?>;
var n = 0;
$("#appendValues").click(function(){
  n++;
  $(".form-group").append(`
    <div class="col-sm-2 mt-2">
      <select class="form-control" name="data[`+n+`][operator]">
        <?foreach($operators as $id => $name){?>
        <option value="<?=$id?>"><?=$name?></option>
        <?}?>
      </select>
    </div>
    <div class="col-sm-1 mt-2">
        <input type="text" class="form-control" name="data[`+n+`][client_phone]" placeholder="Телефон клиента">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="text" class="form-control" name="data[`+n+`][audio_url]" placeholder="URL-адрес аудио">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="text" class="form-control" name="data[`+n+`][requestt]" placeholder="Запрос">
    </div>
    <div class="col-sm-1 mt-2">
      <select class="form-control" name="data[`+n+`][request_type_id]">
        <option value="">Типы запросов</option>
        <?foreach($request_types as $type){?>
        <option value="<?=$type->id?>"><?=$type->name?></option>
        <?}?>
      </select>
    </div>
    <div class="col-sm-1 mt-2">
        <input type="text" class="form-control" name="data[`+n+`][response]" placeholder="Ответ">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="text" class="form-control" name="data[`+n+`][comment]" placeholder="Комментарий">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="date" class="form-control" name="data[`+n+`][date]" value="<?=date('Y-m-d')?>">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="number" class="form-control" name="data[`+n+`][script]" placeholder="Скрипт" min="0" max="10">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="number" class="form-control" name="data[`+n+`][product]" placeholder="Продукт" min="0" max="10">
    </div>
    <div class="col-sm-1 mt-2">
        <input type="number" class="form-control" name="data[`+n+`][solution]" placeholder="Оценка решения" min="0" max="10">
    </div>
  `);
});
</script>
@endsection
