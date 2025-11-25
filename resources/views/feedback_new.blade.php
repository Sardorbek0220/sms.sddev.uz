<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/logo.png')}}">
  <title>
    @if($call->gateway == '712075995')
      Sales Doctor 
    @elseif($call->gateway == '781138585')
      Ibox
    @else
      Ido'kon
    @endif
  </title>

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
                <h4 class="alert-heading">Yuborildi !</h4>
                <p>Taklif hamda murojaatingiz uchun tashakkur.</p>
                <hr>
                <p class="mb-0">@if($call->gateway == '712075995') Sales Doctor @elseif($call->gateway == '781138585') Ibox @else Ido'kon @endif</p>
            </div>
        </div>
    </div>
  </div>
  <div class="container afterSave">
    <div class="row">
      <div class="col-md-12 mt-5">
        <div class="card">
          <div class="card-header beforeCheck">
            <h5 class="text-center">@if($call->gateway == '712075995') Sales Doctor @elseif($call->gateway == '781138585') Ibox @else Ido'kon @endif kompaniyasi {{$call->gateway}} raqam orqali bo'lgan oxirgi suhbatingizni baholashni so'raydi.</h5>
          </div>
          <div class="card-header afterCheck" style="display: none;">
            <h3 class="card-title">Izoh qoldirish</h3>
          </div>
          
            <div class="card-body">
                <div class="card shadow-sm p-4 beforeCheck" style="max-width: 450px; margin: auto;">
                    <div class="mb-3">
                        <label class="font-weight-bold">1. Xodim xushmuomala edimi?</label>
                        <div class="btn-group-toggle row p-2" data-toggle="buttons">
                            <label class="btn btn-outline-success flex-fill mr-1">
                                <input type="radio" name="q1" value="1"> Ha
                            </label>
                            <label class="btn btn-outline-danger flex-fill">
                                <input type="radio" name="q1" value="-1"> Yo'q
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">2. Dastur va jarayon bo'yicha mutaxassismi?</label>
                        <div class="btn-group-toggle row p-2" data-toggle="buttons">
                            <label class="btn btn-outline-success flex-fill mr-1">
                                <input type="radio" name="q2" value="1"> Ha
                            </label>
                            <label class="btn btn-outline-danger flex-fill">
                                <input type="radio" name="q2" value="-1"> Yo'q
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">3. Muammoingizga yechim berdimi?</label>
                        <div class="btn-group-toggle row p-2" data-toggle="buttons">
                            <label class="btn btn-outline-success flex-fill mr-1">
                                <input type="radio" name="q3" value="1"> Ha
                            </label>
                            <label class="btn btn-outline-danger flex-fill">
                                <input type="radio" name="q3" value="-1"> Yo'q
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">4. Qo'shimcha taklif / yordam berdimi?</label>
                        <div class="btn-group-toggle row p-2" data-toggle="buttons">
                            <label class="btn btn-outline-success flex-fill mr-1">
                                <input type="radio" name="q4" value="1"> Ha
                            </label>
                            <label class="btn btn-outline-danger flex-fill">
                                <input type="radio" name="q4" value="-1"> Yo'q
                            </label>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block" onclick="send()" id="sendBtn">Fikr-mulohazani yuborish</button>
                    <button style="display: none;" class="btn btn-primary btn-block" id="loadingBtn" disabled>
                        <span class="spinner-border spinner-border-sm"></span>
                        yuborilmoqda..
                    </button>
                </div>

                <div class="form-group mt-3 afterCheck" style="display: none;">
                    <textarea type="text" class="form-control" name="complaint" id="complaint" placeholder="Izoh"></textarea>
                </div>
                <div class="afterCheck" style="display: none;">
                    <button type="button" class="btn btn-primary pl-5 pr-5" style="font-size: 18px" onclick="saveFeedback()">Yuborish</button>
                </div>

                <input type="text" hidden id="call_id" name="call_id" value="{{$call_id}}">
                <input type="text" hidden id="message_id" name="message_id" value="">
            </div>
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
    function send() {

        $("#sendBtn").css("display", "none");
        $("#loadingBtn").css("display", "block");
        
        let formData = {
            call_id: $('#call_id').val(),
            q1: $("[name='q1']:checked").val() ?? 0,
            q2: $("[name='q2']:checked").val() ?? 0,
            q3: $("[name='q3']:checked").val() ?? 0,
            q4: $("[name='q4']:checked").val() ?? 0,
        };        
        
        $.ajax({
            type:'post',
            url:'{{route("feedback.newStore")}}',
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
                    alert("Siz allaqachon baholagansiz !");
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
            url:'{{route("feedback.afterNewStore")}}',
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
                    alert("Xatolik !");
                }
            }
        });
        
    }
  
</script>
