@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Операторы')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
            @if(session()->exists('status')) 
              <div class="alert alert-danger" role="alert">
                {{ session()->get('status') }}
              </div> 
            @endif
              <div class="card-header">
                <h3 class="card-title"><a href="{{ route('operators.create') }}" class="btn btn-primary">{{__('Создать')}}</a></h3>
                <h3 class="card-title ml-1"><a href="{{ route('admin.profile', $operator_id) }}" class="btn btn-outline-success">{{__('Обновление для мониторинга')}}</a></h3>
              </div>
              <div class="card-body">
                <table id="operator_list" class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 2%">#</th>
                      <th>{{__('Имя')}}</th>
                      <th>{{__('Телефон')}}</th>
                      <th>{{__('Workly ID')}}</th>
                      <th>{{__('Активность')}}</th>
                      <th style="width: 10%;" class="text-center">{{__('Действие')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($operators as $data)
                  	<tr>
                      <td>{{$data->id}}</td>
                      <td style="color: {{$data->color}}">{{$data->name}}</td>
                      <td>{{$data->phone}}</td>
                      <td>{{$data->workly_id}}</td>
                      <td>@if($data->active == 'Y') Активный @else Неактивный @endif</td>
                      <td style="text-align: center;">
                        <a class="d-inline-block mr-2" href="{{ route('operators.edit', ['operator'=>$data->id]) }}" title="Изменить" class="btn btn-outline-primary">
                          <i class="fa fa-edit"></i>
                        </a>
                        <!-- <form class="d-inline-block" action="{{ route('operators.destroy', ['operator'=>$data->id]) }}" method="post">
                          @csrf
                          @method('DELETE')   
                            <button class="btn btn-outline-danger" title="удалить" type="submit" onclick="return confirm('Подтвердите удаление')"><i class="fa fa-trash"></i></button>   
                        </form> -->
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