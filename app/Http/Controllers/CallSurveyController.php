<?php

namespace App\Http\Controllers;

use App\Call;
use App\Services\BitrixSurveyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallSurveyController extends Controller
{
    public function create(Request $request, Call $call, BitrixSurveyService $bitrixSurveyService)
    {
        $this->authorizeCall($call);
        $isPopupWindow = $this->isPopupWindowRequest($request);

        if ($bitrixSurveyService->findSurveyForCall($call)) {
            if ($isPopupWindow) {
                return response()->view('surveys.popup-saved', [
                    'message' => 'Анкета для этого звонка уже существует.',
                ]);
            }

            return redirect()->to($this->resolveBackUrl($request->query('back')))
                ->with('info', 'Анкета для этого звонка уже существует.');
        }

        return view($isPopupWindow ? 'surveys.popup' : 'surveys.create', [
            'call' => $call->load('operator'),
            'formConfig' => $bitrixSurveyService->getFormConfig(),
            'backUrl' => $this->resolveBackUrl($request->query('back')),
            'popupWindow' => $isPopupWindow,
            'storeRoute' => Auth::user()->isOperator()
                ? route('operator.call-surveys.store', $call)
                : route('admin.call-surveys.store', $call),
        ]);
    }

    public function store(Request $request, Call $call, BitrixSurveyService $bitrixSurveyService)
    {
        $this->authorizeCall($call);
        $isPopupWindow = $this->isPopupWindowRequest($request);

        if ($bitrixSurveyService->findSurveyForCall($call)) {
            if ($isPopupWindow) {
                return response()->view('surveys.popup-saved', [
                    'message' => 'Анкета для этого звонка уже была сохранена.',
                ]);
            }

            return redirect()->to($this->resolveBackUrl($request->input('back')))
                ->with('info', 'Анкета для этого звонка уже существует.');
        }

        $payload = $request->validate([
            'reason_key' => ['required', 'string', 'max:64'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['nullable', 'string', 'max:255'],
            'status_label' => ['required', 'string', 'max:255'],
            'comment_text' => ['nullable', 'string'],
            'back' => ['nullable', 'string', 'max:2000'],
            'popup_window' => ['nullable'],
        ]);

        $bitrixSurveyService->createManualSurveyForCall($call, $payload, Auth::user());

        if ($isPopupWindow) {
            return response()->view('surveys.popup-saved', [
                'message' => 'Анкета сохранена и окно можно закрыть.',
            ]);
        }

        return redirect()->to($this->resolveBackUrl($request->input('back')))
            ->with('success', 'Анкета сохранена и отправлена в общий отчёт.');
    }

    protected function authorizeCall(Call $call): void
    {
        $user = Auth::user();

        abort_if(!$user, 403);

        if ($user->isOperator() && (int) $user->operator_id !== (int) $call->operator_id) {
            abort(404);
        }
    }

    protected function resolveBackUrl(?string $backUrl): string
    {
        if (is_string($backUrl) && $backUrl !== '') {
            if (strpos($backUrl, '/') === 0) {
                return $backUrl;
            }

            $target = parse_url($backUrl);
            $app = parse_url((string) config('app.url'));

            if (!empty($target['host']) && !empty($app['host']) && $target['host'] === $app['host']) {
                return $backUrl;
            }
        }

        return Auth::user() && Auth::user()->isOperator()
            ? route('operator.report.calls')
            : route('admin.report.calls');
    }

    protected function isPopupWindowRequest(Request $request): bool
    {
        return (string) $request->query('popup', '') === '1'
            || (string) $request->input('popup_window', '') === '1';
    }
}
