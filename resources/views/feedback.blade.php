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
  <div class="container successSave" style="display: none;">
    <div class="row">
        <div class="col-md-12 mt-5">
            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">Юборилди !</h4>
                <p>Таклиф хамда мурожатингиз учун ташаккур.</p>
                <hr>
                <p class="mb-0">Sales Doctor</p>
            </div>
        </div>
    </div>
  </div>
  <div class="container afterSave">
    <div class="row">
      <div class="col-md-12 mt-5">
        <div class="card">
          <div class="card-header beforeCheck">
            <h3 class="card-title">Sales Doctor компанияси сизнинг 712079559 номер орқали сўнгги мурожаатингизга кўрсатилган хизматни баҳолашингизни сўрайди.</h3>
          </div>
          <div class="card-header afterCheck" style="display: none;">
            <h3 class="card-title">Изох колдириш</h3>
          </div>
          <!-- <form action="" method="post"> -->
            <!-- @csrf -->
            <div class="card-body">
              <div class="card text-white mb-5 beforeCheck" style="width: 100%; background-color: #f25d6b; cursor:pointer" onclick="send('0')">
                <div class="card-header">
                  <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-emoji-frown" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M4.285 12.433a.5.5 0 0 0 .683-.183A3.5 3.5 0 0 1 8 10.5c1.295 0 2.426.703 3.032 1.75a.5.5 0 0 0 .866-.5A4.5 4.5 0 0 0 8 9.5a4.5 4.5 0 0 0-3.898 2.25.5.5 0 0 0 .183.683M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5m4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5"/>
                  </svg> &nbsp&nbsp
                  <span style="font-size: 24px">Жавоб олмадим</span>
                </div>
              </div>
              <div class="card text-white mb-5 beforeCheck" style="width: 100%; background-color: #f5c948; cursor:pointer" onclick="send('1')">
                <div class="card-header">       
                  <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-emoji-neutral" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M4 10.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5m3-4C7 5.672 6.552 5 6 5s-1 .672-1 1.5S5.448 8 6 8s1-.672 1-1.5m4 0c0-.828-.448-1.5-1-1.5s-1 .672-1 1.5S9.448 8 10 8s1-.672 1-1.5"/>
                  </svg>&nbsp&nbsp
                  <span style="font-size: 24px">Етарли жавоб олмадим</span>
                </div>
              </div>
              <div class="card text-white mb-5 beforeCheck" style="width: 100%; background-color: #5e86db; cursor:pointer" onclick="send('2')">
                <div class="card-header">       
                  <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-emoji-neutral" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M4 10.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5m3-4C7 5.672 6.552 5 6 5s-1 .672-1 1.5S5.448 8 6 8s1-.672 1-1.5m4 0c0-.828-.448-1.5-1-1.5s-1 .672-1 1.5S9.448 8 10 8s1-.672 1-1.5"/>
                  </svg>&nbsp&nbsp
                  <span style="font-size: 24px">Жавоб кутяпман</span>
                </div>
              </div>
              <div class="card text-white mb-5 beforeCheck" style="width: 100%; background-color: #67d680; cursor:pointer" onclick="send('3')">
                <div class="card-header">
                  <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-emoji-smile" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.5 3.5 0 0 0 8 11.5a3.5 3.5 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5m4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5"/>
                  </svg>&nbsp&nbsp
                  <span style="font-size: 24px">Жавоб олдим</span>
                </div>
              </div>

              <div class="form-group mt-3 afterCheck" style="display: none;">
                <textarea type="text" class="form-control" name="complaint" id="complaint" placeholder="Изох"></textarea>
              </div>
              <div class="afterCheck" style="display: none;">
                <button type="button" class="btn btn-success pl-5 pr-5" style="font-size: 18px" onclick="saveFeedback()">Юбориш</button>
              </div>

              <input type="text" hidden id="call_id" name="call_id" value="{{$call_id}}">
              <input type="text" hidden id="message_id" name="message_id" value="">
            </div>



            <!-- <div class="card-footer">
              <button type="submit" class="btn btn-success">Юбориш</button>
            </div> -->
          <!-- </form> -->
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
<script>
  function send(status) {
    
    let formData = {
      call_id: $('#call_id').val(),
      solved: status
    };
    
    $.ajax({
      type:'post',
      url:'{{route("feedback.store")}}',
      data: formData,
      success:function(data) {
        if (data.data) {
          document.querySelectorAll('.beforeCheck').forEach(function(el) {
            el.style.display = 'none';
          });
          document.querySelectorAll('.afterCheck').forEach(function(el) {
            el.style.display = 'block';
          });
          document.getElementById("message_id").value = data.message_id;
        }else{
          alert("You have already sent a feedback !");
        }
        
      }
    });
    
  }

  function saveFeedback() {
   
    let formData = {
      message_id: $('#message_id').val(),
      call_id: $('#call_id').val(),
      complaint: $('#complaint').val()
    };
    
    $.ajax({
      type:'post',
      url:'{{route("feedback.afterStore")}}',
      data: formData,
      success:function(data) {
        if (data.call_id) {
          document.querySelectorAll('.afterCheck').forEach(function(el) {
            el.style.display = 'none';
          });
          document.querySelectorAll('.afterSave').forEach(function(el) {
            el.style.display = 'none';
          });
          document.querySelectorAll('.successSave').forEach(function(el) {
            el.style.display = 'block';
          });
        }else{
          alert("Something went wrong !");
        }
        
      }
    });
    
  }
  
</script>
