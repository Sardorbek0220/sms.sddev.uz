<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="icon" href="{{ asset('assets/logo.png') }}">
    <title>
        @if($call->gateway == '712075995') Sales Doctor — fikr-mulohaza
        @elseif($call->gateway == '781138585') iBox — fikr-mulohaza
        @else iDokon — fikr-mulohaza
        @endif
    </title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">

    <style>
        :root {
            --c-primary: #2563eb;
            --c-success: #16a34a;
            --c-success-soft: #dcfce7;
            --c-danger: #dc2626;
            --c-danger-soft: #fee2e2;
            --c-text: #0f172a;
            --c-muted: #64748b;
            --c-bg: #f1f5f9;
            --c-card: #fff;
            --c-border: #e2e8f0;
        }
        @if($call->gateway == '781138585')
            :root { --c-primary: #16a34a; --c-primary-soft: #dcfce7; }
        @elseif($call->gateway == '781136022')
            :root { --c-primary: #f59e0b; --c-primary-soft: #fef3c7; }
        @else
            :root { --c-primary: #2563eb; --c-primary-soft: #dbeafe; }
        @endif

        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; }
        body {
            font-family: 'Source Sans Pro', system-ui, sans-serif;
            background: linear-gradient(180deg, var(--c-primary-soft) 0%, var(--c-bg) 240px) no-repeat;
            color: var(--c-text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .fb-shell { max-width: 520px; margin: 0 auto; padding: 24px 16px 40px; }

        .fb-brand { text-align: center; margin-bottom: 18px; }
        .fb-brand img { width: 56px; height: 56px; border-radius: 14px; box-shadow: 0 6px 20px rgba(15,23,42,.12); background: #fff; padding: 8px; }
        .fb-brand .fb-co { font-size: 1.4rem; font-weight: 700; color: var(--c-text); margin-top: 12px; }
        .fb-brand .fb-sub { font-size: .92rem; color: var(--c-muted); margin-top: 4px; }

        .fb-card {
            background: var(--c-card);
            border-radius: 18px;
            padding: 22px 18px;
            box-shadow: 0 14px 35px rgba(15,23,42,.08);
            margin-bottom: 14px;
        }
        .fb-card h2 { font-size: 1.05rem; font-weight: 700; margin: 0 0 6px; }
        .fb-card p.fb-intro { color: var(--c-muted); font-size: .92rem; line-height: 1.4; margin: 0; }

        .fb-progress { display: flex; gap: 6px; margin: 14px 0 22px; }
        .fb-progress span { flex: 1; height: 4px; border-radius: 2px; background: var(--c-border); }
        .fb-progress.s1 span:nth-child(1),
        .fb-progress.s2 span { background: var(--c-primary); }
        .fb-progress.s2 span:nth-child(2) { background: var(--c-primary); }

        .fb-q { margin-bottom: 18px; }
        .fb-q-num { font-size: .78rem; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .fb-q-text { font-size: 1.02rem; font-weight: 600; margin-top: 4px; margin-bottom: 12px; line-height: 1.35; }

        .fb-choices { display: flex; gap: 10px; }
        .fb-choice {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 64px;
            padding: 10px;
            border: 2px solid var(--c-border);
            border-radius: 14px;
            font-weight: 600;
            font-size: 1rem;
            background: #fff;
            color: var(--c-text);
            cursor: pointer;
            transition: all .15s ease;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .fb-choice input { display: none; }
        .fb-choice .fb-ico {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .9rem;
        }
        .fb-choice.is-yes .fb-ico { background: var(--c-success-soft); color: var(--c-success); }
        .fb-choice.is-no  .fb-ico { background: var(--c-danger-soft);  color: var(--c-danger);  }

        .fb-choice:hover { transform: translateY(-1px); }
        .fb-choice.is-yes.checked { background: var(--c-success); border-color: var(--c-success); color: #fff; }
        .fb-choice.is-no.checked  { background: var(--c-danger);  border-color: var(--c-danger);  color: #fff; }
        .fb-choice.is-yes.checked .fb-ico,
        .fb-choice.is-no.checked  .fb-ico { background: rgba(255,255,255,.25); color: #fff; }

        .fb-submit {
            width: 100%;
            min-height: 56px;
            background: var(--c-primary);
            color: #fff;
            border: 0;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: filter .15s ease, transform .1s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
        }
        .fb-submit:hover { filter: brightness(1.05); }
        .fb-submit:active { transform: translateY(1px); }
        .fb-submit:disabled { opacity: .65; cursor: not-allowed; }
        .fb-submit.is-secondary { background: #fff; color: var(--c-primary); border: 2px solid var(--c-primary); }

        .fb-textarea {
            width: 100%; min-height: 120px;
            border: 2px solid var(--c-border);
            border-radius: 14px;
            padding: 14px;
            font-size: 1rem; font-family: inherit;
            resize: vertical;
        }
        .fb-textarea:focus { outline: none; border-color: var(--c-primary); }

        .fb-success-icon {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: var(--c-success-soft);
            color: var(--c-success);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 16px;
        }
        .fb-success { text-align: center; padding: 30px 12px; }
        .fb-success h2 { font-size: 1.4rem; font-weight: 700; }
        .fb-success p { color: var(--c-muted); }

        .fb-foot { text-align: center; color: var(--c-muted); font-size: .8rem; margin-top: 24px; }

        @media (min-width: 540px) {
            .fb-card { padding: 28px 26px; }
        }
    </style>
</head>
<body>

<div class="fb-shell">

    <div class="fb-brand">
        <img src="{{ asset('assets/logo.png') }}" alt="logo">
        <div class="fb-co">
            @if($call->gateway == '712075995') Sales Doctor
            @elseif($call->gateway == '781138585') iBox
            @else iDokon
            @endif
        </div>
        <div class="fb-sub">{{ $call->gateway }} raqami orqali so'nggi suhbat</div>
    </div>

    {{-- Step 1: 4 yes/no --}}
    <div class="beforeCheck">
        <div class="fb-card">
            <h2>Suhbat sifati</h2>
            <p class="fb-intro">Iltimos, oxirgi murojaatingizni 4 ta savol bilan baholang. Bu 30 soniya oladi.</p>
            <div class="fb-progress s1"><span></span><span></span></div>

            <div class="fb-q">
                <div class="fb-q-num">1-savol</div>
                <div class="fb-q-text">Xodim xushmuomala edimi?</div>
                <div class="fb-choices" data-q="q1">
                    <label class="fb-choice is-yes"><input type="radio" name="q1" value="1"><span class="fb-ico"><i class="fas fa-check"></i></span> Ha</label>
                    <label class="fb-choice is-no"><input type="radio" name="q1" value="-1"><span class="fb-ico"><i class="fas fa-times"></i></span> Yo'q</label>
                </div>
            </div>

            <div class="fb-q">
                <div class="fb-q-num">2-savol</div>
                <div class="fb-q-text">Dastur va jarayon bo'yicha mutaxassismi?</div>
                <div class="fb-choices" data-q="q2">
                    <label class="fb-choice is-yes"><input type="radio" name="q2" value="1"><span class="fb-ico"><i class="fas fa-check"></i></span> Ha</label>
                    <label class="fb-choice is-no"><input type="radio" name="q2" value="-1"><span class="fb-ico"><i class="fas fa-times"></i></span> Yo'q</label>
                </div>
            </div>

            <div class="fb-q">
                <div class="fb-q-num">3-savol</div>
                <div class="fb-q-text">Muammoingizga yechim berdimi?</div>
                <div class="fb-choices" data-q="q3">
                    <label class="fb-choice is-yes"><input type="radio" name="q3" value="1"><span class="fb-ico"><i class="fas fa-check"></i></span> Ha</label>
                    <label class="fb-choice is-no"><input type="radio" name="q3" value="-1"><span class="fb-ico"><i class="fas fa-times"></i></span> Yo'q</label>
                </div>
            </div>

            <div class="fb-q">
                <div class="fb-q-num">4-savol</div>
                <div class="fb-q-text">Qo'shimcha taklif / yordam berdimi?</div>
                <div class="fb-choices" data-q="q4">
                    <label class="fb-choice is-yes"><input type="radio" name="q4" value="1"><span class="fb-ico"><i class="fas fa-check"></i></span> Ha</label>
                    <label class="fb-choice is-no"><input type="radio" name="q4" value="-1"><span class="fb-ico"><i class="fas fa-times"></i></span> Yo'q</label>
                </div>
            </div>

            <button class="fb-submit" onclick="send()" id="sendBtn"><i class="fas fa-paper-plane"></i> Yuborish</button>
            <button class="fb-submit" id="loadingBtn" disabled style="display:none;">
                <span class="spinner-border spinner-border-sm"></span> Yuborilmoqda...
            </button>
        </div>
    </div>

    {{-- Step 2: comment --}}
    <div class="afterCheck" style="display:none;">
        <div class="fb-card">
            <h2>Izoh qoldirish</h2>
            <p class="fb-intro">Agar xohlasangiz, fikringizni yozib qoldiring. Ixtiyoriy.</p>
            <div class="fb-progress s2"><span></span><span></span></div>
            <div class="fb-q">
                <textarea class="fb-textarea" name="complaint" id="complaint" placeholder="Sizning izohingiz..."></textarea>
            </div>
            <button type="button" class="fb-submit" onclick="saveFeedback()"><i class="fas fa-paper-plane"></i> Yuborish</button>
        </div>
    </div>

    {{-- Step 3: success --}}
    <div class="successSave afterCheck" style="display:none;">
        <div class="fb-card">
            <div class="fb-success">
                <div class="fb-success-icon"><i class="fas fa-check"></i></div>
                <h2>Rahmat!</h2>
                <p>Taklif hamda murojaatingiz uchun tashakkur.</p>
            </div>
        </div>
    </div>

    <input type="hidden" id="call_id" name="call_id" value="{{ $call_id }}">
    <input type="hidden" id="message_id" name="message_id" value="">

    <div class="fb-foot">
        @if($call->gateway == '712075995') Sales Doctor
        @elseif($call->gateway == '781138585') iBox
        @else iDokon
        @endif
        · {{ \Carbon\Carbon::parse($call->created_at)->isoFormat('D MMMM YYYY') }}
    </div>
</div>

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script>
    // Visual: highlight choice on radio change
    document.querySelectorAll('.fb-choice input').forEach(function (input) {
        input.addEventListener('change', function () {
            var siblings = input.closest('.fb-choices').querySelectorAll('.fb-choice');
            siblings.forEach(function (el) { el.classList.remove('checked'); });
            input.closest('.fb-choice').classList.add('checked');
        });
    });

    function send() {
        $("#sendBtn").css("display", "none");
        $("#loadingBtn").css("display", "flex");

        var formData = {
            call_id: $('#call_id').val(),
            q1: $("[name='q1']:checked").val() ?? 0,
            q2: $("[name='q2']:checked").val() ?? 0,
            q3: $("[name='q3']:checked").val() ?? 0,
            q4: $("[name='q4']:checked").val() ?? 0,
        };

        $.ajax({
            type: 'post',
            url: '{{ route("feedback.newStore") }}',
            data: formData,
            success: function (data) {
                if (data.data) {
                    document.querySelectorAll('.beforeCheck').forEach(function (el) { el.style.display = 'none'; });
                    document.querySelectorAll('.afterCheck').forEach(function (el) { el.style.display = 'block'; });
                    document.getElementById("message_id").value = data.message_id;
                } else {
                    alert("Siz allaqachon baholagansiz!");
                }
                $("#sendBtn").css("display", "");
                $("#loadingBtn").css("display", "none");
            },
            error: function () {
                $("#sendBtn").css("display", "");
                $("#loadingBtn").css("display", "none");
                alert("Xatolik yuz berdi. Qaytadan urinib ko'ring.");
            }
        });
    }

    function saveFeedback() {
        var formData = {
            message_id: $('#message_id').val(),
            call_id: $('#call_id').val(),
            complaint: $('#complaint').val()
        };

        $.ajax({
            type: 'post',
            url: '{{ route("feedback.afterNewStore") }}',
            data: formData,
            success: function (data) {
                if (data.call_id) {
                    document.querySelectorAll('.afterCheck').forEach(function (el) { el.style.display = 'none'; });
                    document.querySelectorAll('.successSave').forEach(function (el) { el.style.display = 'block'; });
                } else {
                    alert("Xatolik!");
                }
            }
        });
    }
</script>

</body>
</html>
