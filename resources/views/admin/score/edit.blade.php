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
              <form class="form-horizontal" action="{{ route('scores.update', ['score'=>$score->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                  <div class="form-group row">
                    <div class="col-sm-2">
                      <label for="">Тип оценки</label>
                      <select id="key" class="form-control" name="key" required disabled>
                          @foreach($keys as $id => $name)
                          <option @if($id == $score->key_text) selected @endif value="{{ $id }}">{{ $name }}</option>
                          @endforeach
                      </select>
                    </div>
                    <div class="col-sm-2">
                      <label for="">Дата начала</label>
                      <input class="form-control" type="date" required name="from" value="<?= $score->from_date == "0000-00-00 00:00:00" ? "" : date_format(date_create($score->from_date), 'Y-m-d');?>">
                    </div>
                    <div class="col-sm-2">
                      <label for="">Дата окончания</label>
                      <input class="form-control" type="date" required name="to" value="<?= $score->to_date == "0000-00-00 00:00:00" ? "" : date_format(date_create($score->to_date), 'Y-m-d');?>">
                    </div>
                    @if(!isset($score->value['value']))
                    <div class="row col-sm-6 forValues">
                      @foreach($score->value as $key => $val)
                      <div class="col-sm-4">
                        <label for="">От</label>
                        <input type="number" step="any" class="form-control" name="data[{{$key}}][from]" value="{{$val->from}}">
                      </div>
                      <div class="col-sm-4">
                        <label for="">До</label>
                        <input type="number" step="any" class="form-control" name="data[{{$key}}][to]" value="{{$val->to}}">
                      </div>
                      <div class="col-sm-4">
                        <label for="">Значение</label>
                        <input type="number" step="any" class="form-control" name="data[{{$key}}][value]" value="{{$val->value}}">
                      </div>
                      @endforeach
                    </div>
                    <button type="button" class="btn btn-success float-right ml-2 mt-2" id="appendValues">+</button>
                    @else
                    <div class="row col-sm-6 forOthers">
                      <label for="">Значение</label>
                      <input type="number" step="any" class="form-control" name="value" value="{{$score->value['value'] ?? ''}}">
                    </div>
                    @endif
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Сохранять</button>
                  <a href="{{ route('scores.index') }}" class="btn btn-default float-right">Отмена</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js')}}"></script>
<script>
var n = <?if(!isset($score->value['value'])){ echo count($score->value)-1;}else{echo 0;} ?>;
$("#appendValues").click(function(){
  n++;
  $(".forValues").append(`
    <div class="col-sm-4">
      <label for="">От</label>
      <input type="number" step="any" class="form-control" name="data[`+n+`][from]" value="">
    </div>
    <div class="col-sm-4">
      <label for="">До</label>
      <input type="number" step="any" class="form-control" name="data[`+n+`][to]" value="">
    </div>
    <div class="col-sm-4">
      <label for="">Значение</label>
      <input type="number" step="any" class="form-control" name="data[`+n+`][value]" value="">
    </div>
  `);
});

$("#key").change(function(){
  if (['missed', 'workly'].includes(this.value)) {
    $(".forValues").show();
    $(".forOthers").hide();
    $("#appendValues").show();
  }else{
    $(".forValues").hide();
    $(".forOthers").show();
    $("#appendValues").hide();
  }      
});
</script>
@endsection