@extends('layouts.app')
@php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Sat, 01 Jan 1990 00:00:00 GMT");
@endphp

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <h2 class="mb-4 text-center">🔒 Admin Login</h2>

                {{-- 显示成功或失败信息 --}}
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

                {{-- 登录表单 --}}
                <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf

                    {{-- 邮箱输入 --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">📧 Email Address</label>
                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required
                            autofocus>

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 密码输入 --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">🔑 Password</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" required>

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 提交按钮 --}}
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login as Admin</button>
                    </div>
                </form>

                {{-- 返回首页链接 --}}
                <div class="text-center mt-4">
                    <a href="{{ url('/') }}">← Back to Home</a>
                </div>

            </div>
        </div>
    </div>
@endsection