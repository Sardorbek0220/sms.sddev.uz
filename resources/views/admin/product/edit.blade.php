@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Edit product')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('products.update', ['product'=>$product->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-sm-2">
                            <label for="">Name</label>
                            <input type="text" readonly class="form-control" name="operator" value="<?=$operators[$product->operator]?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Comment</label>
                            <input type="text" class="form-control" name="comment" placeholder="Comment" value="<?=$product->comment?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Client phone</label>
                            <input type="text" class="form-control" name="client_phone" placeholder="Client phone" value="<?=$product->client_phone?>"> 
                        </div>
                        <div class="col-sm-1">
                            <label for="">Audio url</label>
                            <input type="text" class="form-control" name="audio_url" placeholder="Audio url" value="<?=$product->audio_url?>">
                        </div>
                        <div class="col-sm-2">
                            <label for="">Request</label>
                            <input type="text" class="form-control" name="requestt" placeholder="Request" value="<?=$product->request?>">
                        </div>
                        <div class="col-sm-2">
                            <label for="">Response</label>
                            <input type="text" class="form-control" name="response" placeholder="Response" value="<?=$product->response?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Date</label>
                            <input type="date" class="form-control" name="date" value="<?=date_format(date_create($product->date), 'Y-m-d')?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Script</label>
                            <input type="number" class="form-control" name="script" placeholder="Script" min="0" max="10" value="<?=$product->script?>">
                        </div>
                        <div class="col-sm-1">
                            <label for="">Product</label>
                            <input type="number" class="form-control" name="product" placeholder="Product" min="0" max="10" value="<?=$product->product?>">
                        </div>
                    </div>
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