@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Script / Product')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('products.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                  <?foreach ($operators as $id => $oper) {?>
                    <div class="form-group row">
                        <div class="col-sm-2">
                            <input type="text" readonly class="form-control" name="operator[<?=$id?>]" value="<?=$oper?>">
                        </div>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="comment[<?=$id?>]" placeholder="Comment">
                        </div>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="client_phone[<?=$id?>]" placeholder="Client phone">
                        </div>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="audio_url[<?=$id?>]" placeholder="Audio url">
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="requestt[<?=$id?>]" placeholder="Request">
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="response[<?=$id?>]" placeholder="Response">
                        </div>
                        <div class="col-sm-1">
                            <input type="date" class="form-control" name="date[<?=$id?>]" value="<?=date('Y-m-d')?>">
                        </div>
                        <div class="col-sm-1">
                            <input type="number" class="form-control" name="script[<?=$id?>]" placeholder="Script" min="0" max="10">
                        </div>
                        <div class="col-sm-1">
                            <input type="number" class="form-control" name="product[<?=$id?>]" placeholder="Product" min="0" max="10">
                        </div>
                    </div>
                  <?}?>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Save</button>
                  <a href="{{ route('products.index') }}" class="btn btn-default float-right">Cancel</a>
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
