@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Обучение')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('trainings.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                      <div class="col-sm-3">
                        <select class="form-control" name="operator" required>
                            @foreach($operators as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                      </div>
                      <div class="col-sm-3">
                        <input type="number" class="form-control" name="training" required id="training" placeholder="Обучение">
                      </div>
                      <div class="col-sm-4">
                        <input type="text" class="form-control" name="comment" id="comment" placeholder="Комментарий">
                      </div>
                      <div class="col-sm-2">
                        <input type="date" class="form-control" name="date" required id="date" value="<?=date('Y-m-d')?>">
                      </div>
                    </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Сохранять</button>
                  <a href="{{ route('trainings.index') }}" class="btn btn-default float-right">Отмена</a>
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
