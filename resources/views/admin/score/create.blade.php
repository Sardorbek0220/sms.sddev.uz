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
            <form class="form-horizontal" action="{{ route('scores.store')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="card-body">
                <div class="form-group row">
                  <div class="col-sm-2">
                    <label for="">Тип оценки</label>
                    <select id="key" class="form-control" name="key">
                      @foreach($keys as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-2">
                    <label for="">Дата начала</label>
                    <input class="form-control" required type="date" name="from" value="<?php echo date("Y-m-d");?>">
                  </div>
                  <div class="col-sm-2">
                    <label for="">Дата окончания</label>
                    <input class="form-control" required type="date" name="to" value="<?php echo date("Y-m-d");?>">
                  </div>
                  <div class="row col-sm-6 forValues">
                    <div class="col-sm-4">
                      <label for="">От</label>
                      <input type="number" step="any" class="form-control" name="data[0][from]" value="">
                    </div>
                    <div class="col-sm-4">
                      <label for="">До</label>
                      <input type="number" step="any" class="form-control" name="data[0][to]" value="">
                    </div>
                    <div class="col-sm-4">
                      <label for="">Значение</label>
                      <input type="number" step="any" class="form-control setPlace" name="data[0][value]" value="" placeholder="1 Workly (вовремя) = Значение">
                    </div>
                  </div>
                  <div class="row col-sm-6 forOthers" style="display: none">
                    <label for="">Значение</label>
                    <input type="number" class="form-control setPlace" name="value" value="" step="any" placeholder="1 Workly = Значение">
                  </div>
                  <button type="button" class="btn btn-success float-right ml-2 mt-2" id="appendValues">+</button>
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
var n = 0;
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
  if (this.value == 'online_time') {
    $(".setPlace").attr("placeholder", "1 Час = Значение");
  }else{
    $(".setPlace").attr("placeholder", "1 "+$("#key option:selected").text()+" = Значение");
  }
  
  if (['missed', 'workly_ontime', 'workly_late'].includes(this.value)) {
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
