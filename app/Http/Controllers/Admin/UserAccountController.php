<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Operator;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAccountController extends Controller
{
    public function index()
    {
        $users = User::with('operator')
            ->orderByRaw("FIELD(role, 'admin', 'operator')")
            ->orderBy('name')
            ->get();

        $operators = Operator::where('active', 'Y')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users', 'operators'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'operator_id' => $data['role'] === 'operator' ? (int) $data['operator_id'] : null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь создан.');
    }

    public function edit(User $user)
    {
        $operators = Operator::where('active', 'Y')
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'operators'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validatePayload($request, $user->id, false);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'operator_id' => $data['role'] === 'operator' ? (int) $data['operator_id'] : null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь обновлён.');
    }

    public function destroy(User $user)
    {
        if ((int) auth()->id() === (int) $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['delete' => 'Нельзя удалить текущего пользователя.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Пользователь удалён.');
    }

    protected function validatePayload(Request $request, ?int $ignoreUserId = null, bool $requirePassword = true): array
    {
        $passwordRules = $requirePassword
            ? ['required', 'string', 'min:6', 'confirmed']
            : ['nullable', 'string', 'min:6', 'confirmed'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreUserId)],
            'role' => ['required', Rule::in(['admin', 'operator', 'manager', 'supervisor', 'viewer'])],
            'operator_id' => [
                'nullable',
                'integer',
                'exists:operators,id',
                Rule::unique('users', 'operator_id')->ignore($ignoreUserId),
            ],
            'password' => $passwordRules,
        ]);

        if ($data['role'] === 'operator' && empty($data['operator_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'operator_id' => 'Для оператора нужно выбрать сотрудника.',
            ]);
        }

        return $data;
    }
}
