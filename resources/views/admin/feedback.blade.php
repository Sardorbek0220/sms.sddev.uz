@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{__('All feedback')}}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 2%">#</th>
                      <th>{{__('Operator')}}</th>
                      <th>{{__('Client')}}</th>
                      <th>{{__('Complaint')}}</th>
                      <th>{{__('Response')}}</th>
                      <th>{{__('Date')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($allFeedback as $data)
                  	<tr>
                      <td>{{$data->id}}</td>
                      <td>{{$data->call->operator->name}}</td>
                      <td>{{$data->call->client_telephone}}</td>
                      <td>{{$data->complaint}}</td>
                      <td> 
                        @if($data->solved == 0) <span style="color: red">Жавоб олмадим</span>
                        @elseif($data->solved == 1) <span style="color: yellow">Етарли жавоб олмадим</span>
                        @elseif($data->solved == 2) <span style="color: blue">Жавоб кутяпман</span>
                        @elseif($data->solved == 3) <span style="color: green">Жавоб олдим</span> 
                        @endif
                      </td>
                      <td>{{$data->created_at}}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                {!! $allFeedback->links() !!}
                  <!-- <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">&raquo;</a></li> -->
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

@endsection