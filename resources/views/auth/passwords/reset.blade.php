@extends('layouts.login-app')
@section('title', '| Login')
@section('content')
<div class="login-conainer p-4">
    <div class="container">
        @include('logo')
        <div class="row g-0">
            <div class="col-lg-6 order-2">
                <div class="card login-card border-0 h-100">
                    <div class="card-body">
                        <div class="login-title mb-4">
                            <h3 class="text-primary">Reset Password</h3>
                            <p class="f-20px mb-0">Enter your email to reset your password</p>
                        </div>

                        <form method="POST" id='custom_reset' class="login-form" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-4">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" placeholder="{{ __('Email Address') }}" type="email"  class="form-control form-control-lg rounded-30 @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror

                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <div class="position-relative">
                                    <input id="password" type="password" placeholder="Enter password" minlength="8" class="form-control form-control-lg required rounded-30 @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    <a href="javascript:;" class="pass_showhide text-center position-absolute" toggle="#password">
                                        <img src="{{url('/')}}/assets/img/icons/eye.png" class="position-absolute start-50 top-50 translate-middle opacity-0" alt="password"/>
                                        <img src="{{url('/')}}/assets/img/icons/eye-slash.png" class="position-absolute start-50 top-50 translate-middle opacity-1" alt="password"/>
                                    </a>
                                </div>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>                                
                                <div class="position-relative">
                                    <input id="password-confirm" placeholder="Enter confirm password" type="password" minlength="8" class="form-control form-control-lg rounded-30 required" name="password_confirmation" required autocomplete="new-password">
                                    <a href="javascript:;" class="pass_showhide text-center position-absolute" toggle="#password-confirm">
                                        <img src="{{url('/')}}/assets/img/icons/eye.png" class="position-absolute start-50 top-50 translate-middle opacity-0" alt="password"/>
                                        <img src="{{url('/')}}/assets/img/icons/eye-slash.png" class="position-absolute start-50 top-50 translate-middle opacity-1" alt="password"/>
                                    </a>
                                </div>
                            </div>
                            <button type="submit" id='idLoginReset' class="btn btn-primary login-primary-btn btn-lg-60 w-100">
                                {{ __('Reset Password') }}
                            </button>

                        </form>

                        <div class="f-22px font-l-500 mt-4">Already have account? <a href="{{route('login')}}" class="font-l-500">Sign In</a></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1">
                <div class="card h-100 border-0 login-bg-card reset-bg"></div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('page-scripts')
<script>
    $(document).ready(function () {
        jQuery.validator.addMethod("emailCheck", function (value, element, param) {
            result = this.optional(element) || /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/.test(value);
            return result;
        });
        $('#custom_reset').validate({
            rules: {
                password_confirmation: {
                    equalTo: "#password"
                },
                email: {
                    email: true,
                    emailCheck: true
                }
            },
            messages: {
                email: {
                    required: 'Please enter email address.',
                    email: "Please enter a valid email address.",
                    emailCheck: "Please enter a valid email address.",
                },
                password: {
                    required: 'Enter password.'
                },
                password_confirmation: {
                    required: 'Enter confirm password.',
                    equalTo: "The password confirmation does not match."
                }
            }
        });
        $("#custom_reset").submit(function () {
            if ($("#custom_reset").valid()) {
                buttonDisabled('#idLoginReset')
            }
        });

    });

</script>
@endsection
