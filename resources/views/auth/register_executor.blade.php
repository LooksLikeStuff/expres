@extends('layouts.auth')
@section('content')
    <div class="container-auth">
        <div class="auth__body flex center">
            <div class="auth__form">
                <h1>Регистрация исполнителя</h1>
                <p class="auth__title_sub">Мы рады видеть вас! <br>
                   <strong>Пройдите</strong> регистрацию для работы в системе</p>
                <form action="{{ route('register.executor.post') }}" method="POST">
                    @csrf
                    <label for="name">
                        <input type="text" name="name" id="name" placeholder="Имя и фамилия" class="form-control" value="{{ old('name') }}" maxlength="50" required>
                        @error('name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </label>
                    <label for="phone">
                        <input type="phone" name="phone" id="phone" class="form-control maskphone" placeholder="Введите телефон" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </label>
                    <label for="password">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Пароль" maxlength="50" required>
                        @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </label>
                    <label for="password_confirmation">
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Подтвердите пароль" maxlength="50" class="form-control" required>
                    </label>
                    <label for="role">
                        <select name="role" id="role" class="form-control" required>
                            <option value="" disabled selected>Выберите вашу роль</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </label>
                    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                    <ul class="auth__form__link">
                        <li class="else__auth">---------- или ----------</li>
                        <li><a href="{{ route('login.code') }}">Войти по коду</a></li>
                         <li><a href="{{ route('login.password') }}">Войти по паролю</a></li>
                        <li class="politic__info" style="text-align: center">Нажимая на "Зарегистрироваться" вы соглашаетесь с<a href=""> политикой конфиденциальности</a></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
@endsection
