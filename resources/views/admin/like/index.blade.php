@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>{{__('Нравится / Наказание')}}</h1>
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
              <h3 class="card-title mt-2"><a href="{{ route('likes.create') }}" class="btn btn-primary">{{__('Создать')}}</a></h3>
              <form action="{{ route('likes.index') }}" method="get" enctype="multipart/form-data">
                @csrf
                <div class="row">
                  <div class="col-12 col-md-5 form-group"></div>
                  <div class="col-12 col-md-2 form-group">
                    <label for=""></label>
                    <select class="form-control mt-2" name="operator">
                      <option value="">Все операторы</option>
                      @foreach($operators as $id => $name)
                      <option <?if(isset($_GET['operator']) && $_GET['operator'] == $id)echo "selected";?> value="{{ $id }}">{{ $name }}</option>
                      @endforeach
                    </select>
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
              <table id="example_like" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                  <tr>
                    <th style="width: 2%">#</th>
                    <th>{{__('Имя')}}</th>
                    <th>{{__('Комментарий')}}</th>
                    <th>{{__('Дата')}}</th>
                    <th>{{__('Нравится / Наказание')}}</th>
                    <th style="width: 10%;" class="text-center">{{__('Действие')}}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($likes as $data)
                  <tr>
                    <td>{{$data->id}}</td>
                    <td>{{$operators[$data->operator]}}</td>
                    <td>{{$data->comment}}</td>
                    <td>{{date_format(date_create($data->date), 'd.m.Y')}}</td>
                    <td>@if ($data->punishment == '0') <i class="btn-outline-success fa fa-fw fa-thumbs-up success"></i> @else <i class="btn-outline-danger fa fa-fw fa-thumbs-down danger"></i> @endif</td>
                    <td style="text-align: center;">
                      <a class="d-inline-block mr-2" href="{{ route('likes.edit', ['like'=>$data->id]) }}" title="Изменить" class="btn btn-outline-primary">
                        <i class="fa fa-edit"></i>
                      </a>
                      <form class="d-inline-block" action="{{ route('likes.destroy', ['like'=>$data->id]) }}" method="post">
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
              {!! $likes->appends(request()->query())->links() !!}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection