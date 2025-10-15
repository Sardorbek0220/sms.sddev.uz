@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Редактировать')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('products.update', ['product'=>$product->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-sm-2">
                            <label for="">Имя</label>
                            <input type="text" readonly class="form-control" name="operator" value="<?=$operators[$product->operator]?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Тел. кл.</label>
                            <input type="text" class="form-control" name="client_phone" placeholder="Client phone" value="<?=$product->client_phone?>"> 
                        </div>
                        <div class="col-sm-1">
                            <label for="">URL ауд.</label>
                            <input type="text" class="form-control" name="audio_url" placeholder="Audio url" value="<?=$product->audio_url?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Запрос</label>
                            <input type="text" class="form-control" name="requestt" placeholder="Request" value="<?=$product->request?>">
                        </div>
                        <div class="col-sm-1">
                          <label for="">Типы запр.</label>
                          <select class="form-control" name="request_type_id">
                            <option value="">Типы запросов</option>
                              @foreach($request_types as $type)
                              <option @if($type->id == $product->request_type_id) selected @endif value="{{ $type->id }}">{{ $type->name }}</option>
                              @endforeach
                          </select>
                        </div>
                        <div class="col-sm-2">
                            <label for="">Ответ</label>
                            <input type="text" class="form-control" name="response" placeholder="Response" value="<?=$product->response?>">
                        </div>
                        <div class="col-sm-2">
                            <label for="">Коммент.</label>
                            <input type="text" class="form-control" name="comment" placeholder="Comment" value="<?=$product->comment?>">
                        </div>
                        <div class="col-sm-2">
                            <label for="">Дата</label>
                            <input type="date" class="form-control" name="date" value="<?=date_format(date_create($product->date), 'Y-m-d')?>">
                        </div>
                        <div class="col-sm-3">
                            <label for="">Скрипт</label>
                            <input type="number" class="form-control" name="script" placeholder="Script" min="0" max="10" value="<?=$product->script?>">
                        </div>
                        <div class="col-sm-3">
                            <label for="">Продукт</label>
                            <input type="number" class="form-control" name="product" placeholder="Product" min="0" max="10" value="<?=$product->product?>">
                        </div>
                        <div class="col-sm-2">
                            <label for="">Решения</label>
                            <input type="number" class="form-control" name="solution" placeholder="Solution" min="0" max="10" value="<?=$product->solution?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Принцип 1</label>
                            <input type="number" class="form-control" name="principle_1" placeholder="Принцип 1" min="0" max="10" value="<?=$product->principle_1?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Принцип 2</label>
                            <input type="number" class="form-control" name="principle_2" placeholder="Принцип 2" min="0" max="10" value="<?=$product->principle_2?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Принцип 3</label>
                            <input type="number" class="form-control" name="principle_3" placeholder="Принцип 3" min="0" max="10" value="<?=$product->principle_3?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Принцип 4</label>
                            <input type="number" class="form-control" name="principle_4" placeholder="Принцип 4" min="0" max="10" value="<?=$product->principle_4?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Сохранить</button>
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