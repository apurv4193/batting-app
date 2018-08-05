<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width" name="viewport">
        <link rel="canonical" href="{{Request::url()}}" />
        <title>{{trans('adminlabels.title')}}</title>
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="{{asset('css/admin/bootstrap/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{asset('css/admin/plugins/datatables/dataTables.bootstrap.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/dist/css/AdminLTE.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/plugins/iCheck/square/blue.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/dist/css/skins/_all-skins.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/bootstrap-multiselect.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/chosen.css')}}">
        <link rel="stylesheet" href="{{asset('css/admin/custom.css')}}">
        @yield('header')
    </head>
    <body class="hold-transition skin-yellow sidebar-mini">
        <div class="wrapper">
            @include('layouts/admin-header')

            @include('layouts/admin-left-navigation')
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @if ($message = Session::get('success'))
                <div class="row success-msg">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                                <h4><i class="icon fa fa-check"></i> {{trans('validation.successlbl')}}</h4>
                                {{ $message }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if ($message = Session::get('error'))
                <div class="row error-msg">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="alert alert-error alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                                <h4><i class="icon fa fa-check"></i> {{trans('validation.errorlbl')}}</h4>
                                {{ $message }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if (count($errors) > 0)
                <div class="row error-msg">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="alert alert-danger danger">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                                <h4><i class="icon fa fa-check"></i> {{trans('validation.errorlbl')}}</h4>
                                @foreach ($errors->all() as $error)
                                {{ $error }}<br/>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="alert alert-success success customMessage hidden">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                                <h4><i class="icon fa fa-check"></i> {{trans('validation.successlbl')}}</h4>
                                <span id="customMessage"></span>
                            </div>
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>

            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 1.0.1
                </div>
                <strong>Copyright</strong>
            </footer>

            @include('layouts/admin-right-sidebar')

            <div class="control-sidebar-bg"></div>
        </div>
        <script src="{{asset('css/admin/plugins/jQuery/jquery-2.2.3.min.js')}}"></script>
        <script src="{{asset('css/admin/bootstrap/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('js/admin/bootstrap-multiselect.js')}}"></script>
        <!-- image gammery -->
        <!-- <script src="{{asset('js/admin/dropdown.js')}}"></script> -->
        <script src="{{asset('js/admin/imggallery.js')}}"></script>
        <!-- end -->
        <!-- <script src="{{asset('css/admin/plugins/iCheck/icheck.min.js')}}"></script> -->
        <script src="{{asset('css/admin/plugins/fastclick/fastclick.js')}}"></script>
        <script src="{{asset('css/admin/dist/js/app.min.js')}}"></script>
        <script src="{{asset('css/admin/plugins/datatables/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('css/admin/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
        <script src="{{asset('css/admin/plugins/slimScroll/jquery.slimscroll.min.js')}}"></script>
        <script src="{{asset('css/admin/plugins/chartjs/Chart.min.js')}}"></script>
        <script src="{{asset('css/admin/dist/js/demo.js')}}"></script>
        <script src="{{asset('js/admin/jquery.validate.min.js')}}"></script>
        <script src="{{ asset('plugins/bootbox/bootbox.min.js') }}" type="text/javascript"></script>
        <script src="{{asset('js/admin/chosen.jquery.js')}}"></script>
        @yield('script')
        <script>
            $(".is_number").on('keyup', function () {
                this.value = this.value.replace(/[^0-9]/gi, '');
            });
        </script>
    </body>
</html>

