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
            <div class="card-header">
              <h3 class="card-title mt-2"><a href="{{ route('products.create') }}" class="btn btn-primary">{{__('Создать')}}</a></h3>
              <form action="{{ route('products.index') }}" method="get" enctype="multipart/form-data">
                @csrf
                <div class="row">
                  <div class="col-12 col-md-7 form-group">
                  </div>
                  <div class="col-12 col-md-2 form-group">
                    <label for="from_date">От</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="{{$from_date}}">
                  </div>
                  <div class="col-12 col-md-2 form-group">
                    <label for="to_date">К</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" value="{{$to_date}}">
                  </div>
                  <div class="col-12 col-md-1 form-group">
                    <label for="filter">&nbsp;</label><br>
                    <button type="submit" class="btn btn-success" id="filter" style="width: 100%;">Фильтр</button>
                  </div>
                </div>
              </form>
            </div>
            <div class="card-body">
              <table id="example_product" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                  <tr>
                    <th style="width: 2%">#</th>
                    <th>{{__('Имя')}}</th>
                    <th>{{__('Телефон клиента')}}</th>
                    <th>{{__('URL-адрес аудио')}}</th>
                    <th>{{__('Запрос')}}</th>
                    <th>{{__('Ответ')}}</th>
                    <th>{{__('Комментарий')}}</th>
                    <th>{{__('Дата')}}</th>
                    <th style="width: 2%">{{__('Скрипт')}}</th>
                    <th style="width: 2%">{{__('Продукт')}}</th>
                    <th style="width: 10%;" class="text-center">{{__('Действие')}}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($products as $data)
                  <tr>
                    <td>{{$data->id}}</td>
                    <td>{{$operators[$data->operator]}}</td>
                    <td>{{$data->client_phone}}</td>
                    <td>{{$data->audio_url}}</td>
                    <td>{{$data->request}}</td>
                    <td>{{$data->response}}</td>
                    <td>{{$data->comment}}</td>
                    <td>{{date_format(date_create($data->date), 'd.m.Y')}}</td>
                    <td>{{$data->script}}</td>
                    <td>{{$data->product}}</td>
                    <td style="text-align: center;">
                      <a class="d-inline-block mr-2" href="{{ route('products.edit', ['product'=>$data->id]) }}" title="Изменить" class="btn btn-outline-primary">
                        <i class="fa fa-edit"></i>
                      </a>
                      <form class="d-inline-block" action="{{ route('products.destroy', ['product'=>$data->id]) }}" method="post">
                        @csrf
                        @method('DELETE')   
                          <button class="btn btn-outline-danger" title="удалить" type="submit" onclick="return confirm('Подтвердите удаление')"><i class="fa fa-trash"></i></button>   
                      </form>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="card-footer clearfix">
              <ul class="pagination pagination m-0 float-right">
              {!! $products->appends(request()->query())->links() !!}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection