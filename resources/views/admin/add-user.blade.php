@extends('layouts.admin-master')
@section('header')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.css') }}" rel="stylesheet" media="screen" />
@stop    
@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.user_management')}}
        <small><?php echo (isset($user) && !empty($user)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.user') }}</small>
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($user) && !empty($user)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.user') }}</h3>
                </div>

                <form class="form-horizontal" id="registerForm" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-user') }}">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <input type="hidden" name="id" value="<?php echo (isset($user) && !empty($user)) ? $user->id : '0' ?>">
                        <input type="hidden" name="hidden_profile" value="<?php echo (isset($user) && !empty($user)) ? $user->user_pic : '' ?>">                    

                        <?php $name = (old('name') ? old('name') : (isset($user) ? $user->name : '')); ?>
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label"> {{ trans('adminlabels.name') }} </label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $name }}" autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="user_pic" class="col-md-2 control-label"> {{ trans('adminlabels.photo') }} </label>
                            <div class="col-md-6">
                                <input type="file" id="user_pic" name="user_pic">   
                                <?php if (isset($user->id) && $user->id != '0') {
                                    if (File::exists(public_path($uploadUserThumbPath . $user->user_pic)) && $user->user_pic != '') {
                                        ?>
                                        <img src="{{ url($uploadUserThumbPath.$user->user_pic) }}" alt="{{$user->user_pic}}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" alt="Default Image" height="70" width="70">
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php $username = (old('username') ? old('username') : (isset($user) ? $user->username : '')); ?>
                        <div class="form-group">
                            <label for="username" class="col-md-2 control-label"> {{ trans('adminlabels.username') }} </label>

                            <div class="col-md-6">
                                <input id="username" type="text" class="form-control" name="username" value="{{ $username }}">
                            </div>
                        </div>
                        <?php $email = (old('email') ? old('email') : (isset($user) ? $user->email : '')); ?>
                        <div class="form-group">
                            <label for="email" class="col-md-2 control-label"> {{ trans('adminlabels.email') }} </label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control" name="email" value="{{ $email }}">
                            </div>
                        </div>
                        <?php $phone = (old('phone') ? old('phone') : (isset($user) ? $user->phone : '')); ?>
                        <div class="form-group">
                            <label for="phone" class="col-md-2 control-label"> {{ trans('adminlabels.phone') }} </label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control" name="phone" value="{{ $phone }}">
                            </div>
                        </div>
                        <?php $dob = (old('dob') ? old('dob') : (isset($user) ? date("Y-m-d", strtotime($user->dob)) : '')); ?>
                        <div class="form-group">
                            <label for="dob" class="col-md-2 control-label"> {{ trans('adminlabels.dob') }} </label>

                            <div class="col-md-6">
                                <input id="dob" type="text" class="form-control datepicker" name="dob" value="{{ $dob }}" data-date-format="yyyy-mm-dd" autofocus>
                            </div>
                        </div>
                        <?php $gender = (old('gender') ? old('gender') : (isset($user) ? $user->gender : '')); ?>
                        <div class="form-group">
                            <label for="gender" class="col-md-2 control-label"> {{ trans('adminlabels.gender') }} </label>

                            <div class="col-md-6">
                                <input type="radio" name="gender" value="1" <?php if ($gender == 1) { ?> checked <?php } ?>> {{ trans('adminlabels.male') }}
                                <input type="radio" name="gender" value="2" <?php if ($gender == 2) { ?> checked <?php } ?>> {{ trans('adminlabels.female') }}
                                <input type="radio" name="gender" value="3" <?php if ($gender == 3) { ?> checked <?php } ?>> {{ trans('adminlabels.other') }} 
                            </div>
                        </div>
                        <?php if (empty($user)) { ?>
                            <div class="form-group">
                                <label for="password" class="col-md-2 control-label"> {{ trans('adminlabels.password') }} </label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password">
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary"> {{ trans('adminlabels.submit') }} </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/users')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
        <!--/.col (right) -->
    </div>
    <!-- /.row -->
</section>
<!-- /.content -->
<!-- /.content-wrapper -->
@endsection
@section('script')
<script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script>
jQuery(document).ready(function () {

    var nowTemp = new Date();
    var todayDate = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    $('.datepicker').datepicker({
        'format': 'yyyy-mm-dd',
        'autoclose': true,
        'endDate': todayDate
    });

    var registerRules = {
        name: {
            required: false
        },
        username: {
            required: true
        },
        email: {
            required: true,
            email: true
        },
        phone: {
            required: true
        },
        dob: {
            required: true,
            date: true
        },
        gender: {
            required: true
        }
    };
    $("#registerForm").validate({
        rules: registerRules,
        messages: {
            email: {
                email: 'Please enter valid email address.'
            },
            dob: {
                date: 'Please enter valid date.'
            }
        }
    });
});
</script>
@endsection