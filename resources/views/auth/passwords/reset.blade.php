
<!-- CSS -->
<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="{{asset('css/style.css')}}">


<body class="bg_main">
        <!-- Top content -->
        @if(isset($token) && !empty($token))
        <div class="top-content">           
            <div class="inner-bg">
                <div class="container">                     
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 form-box">
                            <div class="top_logo">
                                <img src="{{asset('images/LOGO.png')}}">
                            </div>
                            <div class="form-top">
                                <div class="form-top-left">
                                    <h2>Reset Password</h2>                                 
                                </div>
                                
                            </div>
                            <div class="form-bottom contact-form">
                                <form role="form" action="{{ route('password.request') }}" method="post">
                                    {{ csrf_field() }}

                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label class="sr-only" for="pwd">Password</label>
                                        <input type="password" name="password" placeholder="Password" class="form-control" id="password" required>

                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif

                                    </div>
                                    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                        <label class="sr-only" for="cpwd">Confirm Password</label>
                                        <input type="password" name="password_confirmation" placeholder="Confirm Password" class="form-control" id="password-confirm" required>

                                        @if ($errors->has('password_confirmation'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            </span>
                                        @endif
                                        
                                    </div>                                  
                                    <button type="submit" class="btn">Reset Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        @else
        <div class="top-content">           
            <div class="inner-bg">
                <div class="container">                     
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 form-box">
                            <div class="top_logo">
                                <img src="{{asset('images/LOGO.png')}}">
                            </div>
                            
                                <div class="thankyou_content">
                                    <h1>Sorry</h1>                       
                                    <h3>Token Expired</h3>       
                                                                    
                                </div>                                
                            </div>                            
                        
                    </div>
                </div>
            </div>
            
        </div>
        @endif
        <!-- Javascript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <!-- Latest compiled JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
       
    </body>

