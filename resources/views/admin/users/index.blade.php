@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Пользователи</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Новый пользователь</h3>
                        </div>
                        <form action="{{ route('admin.users.store') }}" method="post">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Имя</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Логин / Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="role">Роль</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="operator" {{ old('role', 'operator') === 'operator' ? 'selected' : '' }}>Оператор</option>
                                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Админ</option>
                                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Менеджер</option>
                                        <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>Супервизор</option>
                                        <option value="viewer" {{ old('role') === 'viewer' ? 'selected' : '' }}>Просмотр</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="operator_id">Оператор</label>
                                    <select class="form-control" id="operator_id" name="operator_id">
                                        <option value="">Не выбран</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" {{ (string) old('operator_id') === (string) $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }} ({{ $operator->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="password">Пароль</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">Повтор пароля</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Создать</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Список пользователей</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Имя</th>
                                        <th>Логин</th>
                                        <th>Роль</th>
                                        <th>Оператор</th>
                                        <th style="width: 160px;">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->role ?: 'not-set' }}</td>
                                            <td>{{ optional($user->operator)->name ?: '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Изменить</a>
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="post" style="display:inline-block;" onsubmit="return confirm('Удалить пользователя?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Пользователей пока нет</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
