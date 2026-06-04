@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Редактировать пользователя</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
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
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $user->name }}</h3>
                        </div>
                        <form action="{{ route('admin.users.update', $user) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Имя</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Логин / Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="role">Роль</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Оператор</option>
                                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Админ</option>
                                        <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Менеджер</option>
                                        <option value="supervisor" {{ old('role', $user->role) === 'supervisor' ? 'selected' : '' }}>Супервизор</option>
                                        <option value="viewer" {{ old('role', $user->role) === 'viewer' ? 'selected' : '' }}>Просмотр</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="operator_id">Оператор</label>
                                    <select class="form-control" id="operator_id" name="operator_id">
                                        <option value="">Не выбран</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" {{ (string) old('operator_id', $user->operator_id) === (string) $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }} ({{ $operator->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="password">Новый пароль</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Оставь пустым, если менять не нужно">
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">Повтор нового пароля</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-default">Назад</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
