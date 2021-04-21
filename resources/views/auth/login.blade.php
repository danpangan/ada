@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-md-9 col-lg-6 col-xl-4">
            <div class="card" style="margin-top: 50%;">
                <div class="card-body login-card-body">
                    <div class="login-logo">
                        <img src="/images/logo.png" alt="" width="300px">
                    </div>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="input-group mb-3">
                            {{-- <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email" autofocus> --}}
                            <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" required autocomplete="email" placeholder="Username" autofocus>

                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">

                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">Log In</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
