<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width" name="viewport">
        <link rel="canonical" href="{{Request::url()}}" />
        <title>{{trans('adminlabels.title')}}</title>
        <meta name="keywords" content="{{trans('label.keywords')}}" />
        <meta name="description" content="{{trans('label.description')}}" />
        <link rel="stylesheet" href="{{asset('css/admin/bootstrap/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
        <link rel="stylesheet" href="{{asset('css/admin/dist/css/AdminLTE.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/plugins/iCheck/square/blue.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/custom.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/chosen.css')}}">
        @yield('header')
    </head>
    <body class="hold-transition login-page">
      <div class="login-box login_blog">
        
        <div>
          @if (count($errors) > 0)
          <div class="alert alert-danger danger">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
              <ul>
                  @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
          @endif
          @if($message = Session::get('error'))
          <div class="alert alert-danger danger">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
              <ul>
                  <li>{{ $message }}</li>
              </ul>
          </div>
          @endif
          @if($message = Session::get('success'))
          <div class="alert alert-success success">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
              <ul>
                  <li>{{ $message }}</li>
              </ul>
          </div>
          @endif
        </div>
        @yield('content')
        
        @yield('footer')
        <script src="{{asset('css/admin/plugins/jQuery/jquery-2.2.3.min.js')}}"></script>
        <script src="{{asset('css/admin/bootstrap/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('js/admin/jquery.validate.min.js')}}"></script>
        <script src="{{asset('js/admin/chosen.jquery.js')}}"></script>
        <!-- <script src="{{asset('css/admin/plugins/iCheck/icheck.min.js')}}"></script> -->
        @yield('script')
        <!-- <script>
          $(function () {
            $('input').iCheck({
              checkboxClass: 'icheckbox_square-blue',
              radioClass: 'iradio_square-blue',
              increaseArea: '20%'
            });
          });
        </script> -->
        <script type="text/javascript">
          setTimeout(function(){$('.alert-danger').fadeOut(500);},5000);
          setTimeout(function(){$('.alert-success').fadeOut(500);},5000);
        </script>
    </body>
</html>