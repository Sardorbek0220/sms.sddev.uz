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
              <form class="form-horizontal" action="{{ route('likes.update', ['like'=>$like->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                  <div class="form-group row">
                    <div class="col-sm-2">
                      <input type="text" readonly class="form-control" name="operator" id="operator" value="<?=$operators[$like->operator]?>">
                    </div>
                    <div class="col-sm-5">
                      <input type="text" class="form-control" name="comment" id="comment" placeholder="Comment" value="<?=$like->comment?>">
                    </div>
                    <div class="col-sm-2">
                      <input type="date" class="form-control" name="date" id="date" value="<?=date_format(date_create($like->date), 'Y-m-d')?>">
                    </div>
                    <div class="col-sm-3">
                      <div class="btn-group-toggle row" data-toggle="buttons">
                        <label class="btn btn-outline-success col mr-2">
                          <input type="radio" name="punish" value="0"><i class="fa fa-fw fa-thumbs-up"></i>
                        </label>
                        <label class="btn btn-outline-danger col ml-2">
                          <input type="radio" name="punish" value="1"><i class="fa fa-fw fa-thumbs-down"></i>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Сохранять</button>
                  <a href="{{ route('likes.index') }}" class="btn btn-default float-right">Отмена</a>
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
    <?if($like->punishment == '0'){?>
      $( ".btn-outline-success" ).trigger( "click" );
    <?}else{?>
      $( ".btn-outline-danger" ).trigger( "click" );
    <?}?>
   </script>
@endsection