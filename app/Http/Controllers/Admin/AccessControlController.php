<?php

namespace App\Http\Controllers\Admin;

use App\AuditLog;
use App\Http\Controllers\Controller;
use App\Permission;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccessControlController extends Controller
{
    public function index()
    {
        // Manage permissions only for non-operator users (admins/managers/etc).
        $users = User::where('role', '!=', 'operator')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $permissions = Permission::orderBy('category')->orderBy('sort_order')->get();

        $grants = DB::table('user_permissions')
            ->select('user_id', 'permission_key')
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(function ($items) {
                return $items->pluck('permission_key')->all();
            })
            ->all();

        return view('admin.access_control.index', [
            'users'       => $users,
            'permissions' => $permissions->groupBy('category'),
            'grants'      => $grants,
        ]);
    }

    public function update(Request $request)
    {
        $payload = $request->input('grants', []); // {user_id => [perm_key, ...]}
        if (!is_array($payload)) {
            return redirect()->back()->with('error', 'Invalid payload');
        }

        $allowedUserIds = User::where('role', '!=', 'operator')->pluck('id')->all();
        $allowedKeys    = Permission::pluck('permission_key')->all();

        DB::transaction(function () use ($payload, $allowedUserIds, $allowedKeys) {
            $now = now();
            $by  = Auth::id();

            // 1. clear existing grants for non-operator users (one shot)
            DB::table('user_permissions')->whereIn('user_id', $allowedUserIds)->delete();

            // 2. insert fresh grants
            $rows = [];
            foreach ($payload as $userId => $keys) {
                $userId = (int) $userId;
                if (!in_array($userId, $allowedUserIds, true)) continue;
                if (!is_array($keys)) continue;
                foreach ($keys as $k) {
                    if (!in_array($k, $allowedKeys, true)) continue;
                    $rows[] = [
                        'user_id'        => $userId,
                        'permission_key' => $k,
                        'granted_by'     => $by,
                        'granted_at'     => $now,
                    ];
                }
            }
            if (!empty($rows)) {
                DB::table('user_permissions')->insert($rows);
            }
        });

        AuditLog::record('permissions_updated', [
            'message' => 'Изменены доступы пользователей',
            'payload' => ['users' => array_keys($payload)],
        ]);

        return redirect()->route('admin.access-control')->with('success', 'Доступы сохранены');
    }
}
