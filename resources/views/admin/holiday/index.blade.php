@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Праздники')}}</h1>
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
                <h3 class="card-title"><a href="{{ route('holidays.create') }}" class="btn btn-primary">{{__('Создать')}}</a></h3>
              </div>
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 2%">#</th>
                      <th>{{__('Описание')}}</th>
                      <th>{{__('Дата')}}</th>
                      <th style="width: 10%;" class="text-center">{{__('Действие')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($holidays as $data)
                  	<tr>
                      <td>{{$data->id}}</td>
                      <td>{{$data->description}}</td>
                      <td>{{$data->date}}</td>
                      <td style="text-align: center;">
                        <a class="d-inline-block mr-2" href="{{ route('holidays.edit', ['holiday'=>$data->id]) }}" title="Изменить" class="btn btn-outline-primary">
                          <i class="fa fa-edit"></i>
                        </a>
                        <form class="d-inline-block" action="{{ route('holidays.destroy', ['holiday'=>$data->id]) }}" method="post">
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
                <ul class="pagination pagination-sm m-0 float-right">
                    {!! $holidays->links() !!}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

@endsection