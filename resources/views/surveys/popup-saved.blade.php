<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Анкета сохранена</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.18), transparent 28%),
                linear-gradient(180deg, #eefdf5 0%, #f8fafc 100%);
            font-family: Arial, sans-serif;
            color: #0f172a;
        }
        .saved-card {
            width: min(460px, calc(100vw - 32px));
            padding: 28px 24px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.10);
            text-align: center;
        }
        .saved-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 16px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #dcfce7;
            color: #15803d;
            font-size: 34px;
            font-weight: 700;
        }
        .saved-title {
            margin: 0 0 8px;
            font-size: 30px;
            font-weight: 800;
        }
        .saved-text {
            margin: 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="saved-card">
        <div class="saved-icon">+</div>
        <h1 class="saved-title">Готово</h1>
        <p class="saved-text">{{ $message ?? 'Анкета сохранена.' }}</p>
        <p class="saved-text" style="margin-top: 12px;">Окно закроется автоматически.</p>
    </div>

    <script>
        (function () {
            try {
                if (window.opener && !window.opener.closed) {
                    if (window.opener.OperatorLiveSurveyWidget && typeof window.opener.OperatorLiveSurveyWidget.refresh === 'function') {
                        window.opener.OperatorLiveSurveyWidget.refresh();
                    }

                    if (typeof window.opener.dispatchEvent === 'function') {
                        window.opener.dispatchEvent(new CustomEvent('operator-workspace:update-request'));
                    }
                }
            } catch (error) {
                console.error(error);
            }

            setTimeout(function () {
                window.close();
            }, 900);
        })();
    </script>
</body>
</html>
