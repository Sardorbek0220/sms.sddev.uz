@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <form action="{{ route('admin.report') }}" method="get" enctype="multipart/form-data">
                @csrf
                <div class="row">
                  <div class="col-12 col-md-7 form-group">
                  </div>
                  <div class="col-12 col-md-2 form-group">
                    <label for="from_date">From</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="{{$from_date}}">
                  </div>
                  <div class="col-12 col-md-2 form-group">
                    <label for="to_date">To</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" value="{{$to_date}}">
                  </div>
                  <div class="col-12 col-md-1 form-group">
                    <label for="filter">&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary" id="filter" style="width: 100%;">Filter</button>
                  </div>
                </div>
              </form>
            </div>
            <div class="card-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Operator</th>
                    <th>mark 0 <br> (Жавоб олмадим)</th>
                    <th>mark 1 <br> (Етарли жавоб олмадим)</th>
                    <th>mark 2 <br> (Жавоб кутяпман)</th>
                    <th>mark 3 <br> (Жавоб олдим)</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                @foreach( $reports as $data )
                  <tr>
                    <td>{{$data->name}}</td>
                    <td>{{$data->mark0}}</td>
                    <td>{{$data->mark1}}</td>
                    <td>{{$data->mark2}}</td>
                    <td>{{$data->mark3}}</td>
                    <td>{{$data->total}}</td>
                  </tr>
                @endforeach
                </tbody>
                <tfoot>
                  @foreach( $footReports as $data )
                  <tr>
                    <th>{{$data->name}}</th>
                    <th>{{$data->mark0}}</th>
                    <th>{{$data->mark1}}</th>
                    <th>{{$data->mark2}}</th>
                    <th>{{$data->mark3}}</th>
                    <th>{{$data->total}}</th>
                  </tr>
                  @endforeach
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection