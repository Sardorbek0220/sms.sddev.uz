@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Все отзывы')}}</h1>
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
                <form action="{{ route('feedback.all') }}" method="get" enctype="multipart/form-data">
                  @csrf
                  <div class="row">
                    <div class="col-12 col-md-7 form-group">
                    </div>
                    <!-- <div class="col-12 col-md-2 form-group">
                      <label for="type">Типы</label>
                      <select class="form-control" name="type" id="type">
                        <option value="1111">Все</option>
                        @foreach($status as $id => $s)
                        <option <?//if(isset($_GET['type']) && $_GET['type'] == $id)echo "selected";?> value="{{ $id }}">{{ $s }}</option>
                        @endforeach
                      </select>
                    </div> -->
                    <div class="col-12 col-md-2 form-group">
                      <label for="from_date">От</label>
                      <input type="date" class="form-control" id="from_date" name="from_date" value="{{$from_date}}">
                    </div>
                    <div class="col-12 col-md-2 form-group">
                      <label for="to_date">До</label>
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
                <table class="table table-bordered" id="feedbacks">
                  <thead>
                    <tr>
                      <th style="width: 2%">#</th>
                      <th>{{__('Оператор')}}</th>
                      <th>{{__('Клиент')}}</th>
                      <th>{{__('Комментарий')}}</th>
                      <th>{{__('Вопрос 1')}}</th>
                      <th>{{__('Вопрос 2')}}</th>
                      <th>{{__('Вопрос 3')}}</th>
                      <th>{{__('Вопрос 4')}}</th>
                      <th>{{__('ID')}}</th>
                      <th>{{__('Дата')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($allFeedback as $data)
                  	<tr>
                      <td>{{$data->id}}</td>
                      <td>{{$data->call->operator->name}}</td>
                      <td>{{$data->call->client_telephone}}</td>
                      <td>{{$data->complaint}}</td>
                      <td> 
                        @if($data->q1 == '0') <span></span>
                        @elseif($data->q1 == '1') <span style="color: green">Да</span>
                        @else <span style="color: red">Нет</span> 
                        @endif
                      </td>
                      <td> 
                        @if($data->q2 == '0') <span></span>
                        @elseif($data->q2 == '1') <span style="color: green">Да</span>
                        @else <span style="color: red">Нет</span> 
                        @endif
                      </td>
                      <td> 
                        @if($data->q3 == '0') <span></span>
                        @elseif($data->q3 == '1') <span style="color: green">Да</span>
                        @else <span style="color: red">Нет</span> 
                        @endif
                      </td>
                      <td> 
                        @if($data->q4 == '0') <span></span>
                        @elseif($data->q4 == '1') <span style="color: green">Да</span>
                        @else <span style="color: red">Нет</span> 
                        @endif
                      </td>
                      <td>id_{{ $data->call->id}}</td>
                      <td>{{$data->created_at}}</td>
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