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
                        <div class="col-sm-3">
                            <label for="">Тип оценки</label>
                            <select id="key" class="form-control" name="key" required>
                                @foreach($keys as $id => $name)
                                <option @if($id == $score->key_text) selected @endif value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label for="">Значение</label>
                            <input type="number" class="form-control" required name="value" value="{{$score->value}}">
                        </div>
                        <div class="col-sm-3">
                            <label for="">Под</label>
                            <input type="number" class="form-control" name="under" value="{{$score->under}}">
                        </div>
                        <div class="col-sm-3">
                            <label for="">После (значение)</label>
                            <input type="number" class="form-control" name="after_value" value="{{$score->after_value}}">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Сохранять</button>
                  <a href="{{ route('scores.index') }}" class="btn btn-default float-right">Отмена</a>
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <script src="{{ asset('assets/plugins/jquery/jquery.min.js')}}"></script>
  <!-- <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js')}}"></script> -->
@endsection