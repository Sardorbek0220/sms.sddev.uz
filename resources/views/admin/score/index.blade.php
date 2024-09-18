@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>{{__('Оценка')}}</h1>
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
                <h3 class="card-title mt-2"><a href="{{ route('scores.create') }}" class="btn btn-primary">{{__('Создать')}}</a></h3>
            </div>
            <div class="card-body">
              <table id="example1" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                  <tr>
                    <th style="width: 2%">#</th>
                    <th>{{__('Тип оценки')}}</th>
                    <th>{{__('Значение')}}</th>
                    <th>{{__('Под')}}</th>
                    <th>{{__('После (значение)')}}</th>
                    <th>{{__('Дата')}}</th>
                    <th style="width: 10%;" class="text-center">{{__('Действие')}}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($scores as $data)
                  <tr>
                    <td>{{$data->id}}</td>
                    <td>{{$keys[$data->key_text]}}</td>
                    <td>{{$data->value}}</td>
                    <td>{{$data->under}}</td>
                    <td>{{$data->after_value}}</td>
                    <td>{{date_format(date_create($data->created_at), 'd.m.Y')}}</td>
                    <td style="text-align: center;">
                      <a class="d-inline-block mr-2" href="{{ route('scores.edit', ['score'=>$data->id]) }}" title="Изменить" class="btn btn-outline-primary">
                        <i class="fa fa-edit"></i>
                      </a>
                      <form class="d-inline-block" action="{{ route('scores.destroy', ['score'=>$data->id]) }}" method="post">
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
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection