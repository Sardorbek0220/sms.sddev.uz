@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Exceptions')}}</h1>
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
                <h3 class="card-title"><a href="{{ route('exceptions.create') }}" class="btn btn-primary">{{__('Create')}}</a></h3>
              </div>
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 2%">#</th>
                      <th>{{__('From')}}</th>
                      <th>{{__('To')}}</th>
                      <th>{{__('Day')}}</th>
                      <th style="width: 10%;" class="text-center">{{__('Action')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($exceptions as $data)
                  	<tr>
                      <td>{{$data->id}}</td>
                      <td>{{$data->from_exc}}</td>
                      <td>{{$data->to_exc}}</td>
                      <td>{{$data->day}}</td>
                      <td style="text-align: center;">
                        <a class="d-inline-block mr-2" href="{{ route('exceptions.edit', ['exception'=>$data->id]) }}" title="Изменить" class="btn btn-outline-primary">
                          <i class="fa fa-edit"></i>
                        </a>
                        <form class="d-inline-block" action="{{ route('exceptions.destroy', ['exception'=>$data->id]) }}" method="post">
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
                    {!! $exceptions->links() !!}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

@endsection