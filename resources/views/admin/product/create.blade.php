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
                      <select class="form-control" name="operator">
                        @foreach($operators as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="client_phone" placeholder="Телефон клиента">
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="audio_url" placeholder="URL-адрес аудио">
                    </div>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="requestt" placeholder="Запрос">
                    </div>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="response" placeholder="Ответ">
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control" name="comment" placeholder="Комментарий">
                    </div>
                    <div class="col-sm-1">
                        <input type="date" class="form-control" name="date" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="col-sm-1">
                        <input type="number" class="form-control" name="script" placeholder="Скрипт" min="0" max="10">
                    </div>
                    <div class="col-sm-1">
                        <input type="number" class="form-control" name="product" placeholder="Продукт" min="0" max="10">
                    </div>
                    <!-- <button type="button" class="btn btn-success float-right ml-2 mt-2" id="appendValues">+</button> -->
                  </div>
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
@endsection
