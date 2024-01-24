@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Operators')}}</h1>
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
                <h3 class="card-title"><a href="{{ route('operators.create') }}" class="btn btn-primary">{{__('Create')}}</a></h3>
              </div>
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 2%">#</th>
                      <th>{{__('Name')}}</th>
                      <th>{{__('Phone')}}</th>
                      <th>{{__('Condition')}}</th>
                      <th style="width: 10%;" class="text-center">{{__('Action')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($operators as $data)
                  	<tr>
                      <td>{{$data->id}}</td>
                      <td>{{$data->name}}</td>
                      <td>{{$data->phone}}</td>
                      <td>@if($data->active == 'Y') active @else inactive @endif</td>
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
              <!-- /.card-body -->
              <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                  <!-- <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">&raquo;</a></li> -->
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

@endsection