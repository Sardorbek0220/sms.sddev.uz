@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Звонки')}}</h1>
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
                    <form action="{{ route('admin.report.calls') }}" method="get" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-7 form-group">
                            </div>
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
                    <table class="table table-bordered" id="operator_list">
                        <thead>
                            <tr>
                                <th style="width: 2%">#</th>
                                <th>{{__('Клиент')}}</th>
                                <th>{{__('Оператор')}}</th>
                                <th>{{__('Аудио')}}</th>
                                <th>{{__('Вход')}}</th>
                                <th>{{__('Дата')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $datum)
                            <tr>
                                <td>{{$datum->id}}</td>
                                <td>{{$datum->client_telephone}}</td>
                                <td>{{$datum->operator->name}}</td>
                                <td><?if(!empty($datum->telegram_audio_url)){?> <a href="{{$datum->telegram_audio_url}}" target="_blank">{{$datum->id}}</a> <?}?></td>
                                <td>{{$datum->gateway}}</td>
                                <td>{{$datum->created_at}}</td>
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