<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/logo.png')}}">
  <title>Sales Doctor</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css')}}">
</head>
<body class="hold-transition">

<section class="content">
  <div class="container">
    <div class="row">
      <div class="col-md-12 mt-5">
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title">Send feedback</h3>
          </div>
          <form action="{{route('feedback.store')}}" method="post">
            @csrf
            <div class="card-body">
              <div class="form-group">
                <label for="complaint">Complaint</label>
                <input type="text" class="form-control" name="complaint" id="complaint" placeholder="Write your complaint">
              </div>
              <div class="form-group">
                <label for="advice">Advice</label>
                <input type="text" class="form-control" name="advice" id="advice" placeholder="Give your advice">
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="not_solved" id="not_solved">
                <label class="form-check-label" for="not_solved">Your problem is not solved ?</label>
              </div>
              <input type="text" hidden name="call_id" value="{{$call_id}}">
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-success">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/dist/js/adminlte.min.js')}}"></script>
</body>
</html>
