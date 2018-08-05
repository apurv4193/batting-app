@extends('layouts.admin-master-basic')
@section('content')

<div class="login-box-body">
      <p class="login-box-msg">Please login to start session</p>
      <form class="form-horizontal" id="loginForm" method="POST" action="{{ route('login') }}">
      {{ csrf_field() }}
        <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
            <input type="text" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}" maxlength="100" autofocus>
          <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
          @if ($errors->has('email'))
              <span class="help-block">
                  <strong>{{ $errors->first('email') }}</strong>
              </span>
          @endif
        </div>
        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
          <input type="password" class="form-control" placeholder="Password" name="password" maxlength="20">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          @if ($errors->has('password'))
              <span class="help-block">
                  <strong>{{ $errors->first('password') }}</strong>
              </span>
          @endif
        </div>
        
        <div class="form-group">  
          <div class="col-md-6">
            <button type="submit" class="btn btn-primary btn-block btn-flat" id="login_submit">Sign In</button>
          </div>
        </div>
      </form>
      
    </div>
@endsection
@section('script')
<script>
    jQuery(document).ready(function() {
        var loginRules = {
            username: {
                required: true
               
            },
            password: {
                required: true
            }
        };
        $("#loginForm").validate({
            rules: loginRules,
            messages: {
                username: {
                    required: 'Enter username'
                },
                password: {
                    required: 'Enter password'
                }
            }
        });
    });
</script>
@endsection

