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
              <form class="form-horizontal" action="{{ route('trainings.update', ['training'=>$training->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                  <div class="form-group row">
                    <div class="col-sm-3">
                        <select class="form-control" name="operator" required>
                            @foreach($operators as $id => $name)
                            <option @if($training->operator == $id) selected @endif value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                      <input type="number" class="form-control" name="training" id="training" value="<?=$training->training?>">
                    </div>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="comment" id="comment" placeholder="Comment" value="<?=$training->comment?>">
                    </div>
                    <div class="col-sm-2">
                      <input type="date" class="form-control" name="date" id="date" value="<?=date_format(date_create($training->date), 'Y-m-d')?>">
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
   <script>
    <?if($training->training == '0'){?>
      $( ".btn-outline-success" ).trigger( "click" );
    <?}else{?>
      $( ".btn-outline-danger" ).trigger( "click" );
    <?}?>
   </script>
@endsection