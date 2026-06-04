@extends('admin.layouts.index')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('Exceptions')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <form class="form-horizontal" action="{{ route('exceptions.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label for="from">From</label>
                            <input type="time" required class="form-control" name="from" id="from">
                        </div>
                        <div class="col-sm-4">
                            <label for="to">To</label>
                            <input type="time" required class="form-control" name="to" id="to">
                        </div>
                        <div class="col-sm-4">
                            <label for="day">Day</label>
                            <input type="date" required class="form-control" name="day" id="day">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-success float-right ml-2">Save</button>
                  <a href="{{ route('exceptions.index') }}" class="btn btn-default float-right">Cancel</a>
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
@endsection