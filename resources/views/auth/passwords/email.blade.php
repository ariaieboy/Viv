@extends('layouts.app')
@section('title') Reset password @endsection

@section('hero')
<div class="jumbotron">
    <div class="container">
        <h2>Reset password</h2>
    </div>
</div>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <form method="POST" action="{{ route('password.email') }}"" class="login-form">
                @csrf

                <img class="login-logo" src="{{ asset('img/logo.png') }}" />

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <label for="email">E-mail address</label>
                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" placeholder="E-mail address" required>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @enderror

                <button type="submit" class="btn btn-primary">
                    <i class="fad fa-fw fa-paper-plane"></i> Send password reset link
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
