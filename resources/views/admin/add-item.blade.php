@extends('layouts.admin-master')
@section('header')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.css') }}" rel="stylesheet" media="screen" />
@stop    
@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.item_management')}}
        <small><?php echo (isset($item) && !empty($item)) ? trans('adminlabels.edit') : '' ?> {{ trans('adminlabels.item') }}</small>
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($item) && !empty($item)) ? trans('adminlabels.edit') : '' ?> {{ trans('adminlabels.item') }}</h3>
                </div>

                <form class="form-horizontal" id="itemForm" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-item') }}">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <?php $id = (isset($item) && !empty($item)) ? $item->id : '0' ?>
                        <input type="hidden" name="id" value="{{$id}}">
                        <input type="hidden" name="hidden_item_image" value="<?php echo (isset($item) && !empty($item)) ? $item->item_image : '' ?>">                    

                        <!-- Item Name -->
                        <?php $name = (old('name') ? old('name') : (isset($item) ? $item->name : '')); ?>
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label"> {{ trans('adminlabels.name') }} </label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $name }}" readonly="">
                            </div>
                        </div>

                        <!-- Item Image -->
                        <div class="form-group">
                            <label for="item_image" class="col-md-2 control-label"> {{ trans('adminlabels.image') }} </label>
                            <div class="col-md-6">
                                <input type="file" id="item_image" name="item_image">   
                                <?php if (isset($item->id) && $item->id != '0') {
                                    if (File::exists(public_path($itemThumbPath . $item->item_image)) && $item->item_image != '') {
                                        ?>
                                        <img src="{{ url($itemThumbPath.$item->item_image) }}" alt="{{$item->item_image}}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" alt="Default Image" height="70" width="70">
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Points -->
                        <?php $points = (old('points') ? old('points') : (isset($item) ? $item->points : '')); ?>
                        <div class="form-group">
                            <label for="points" class="col-md-2 control-label"> {{ trans('adminlabels.points') }} </label>

                            <div class="col-md-6">
                                <input id="points" type="text" class="form-control" name="points" value="{{ $points }}">
                            </div>
                        </div>

                        <!-- Description -->
                        <?php $description = (old('description') ? old('description') : (isset($item) ? $item->description : '')); ?>
                        <div class="form-group">
                            <label for="description" class="col-md-2 control-label"> {{ trans('adminlabels.items_description') }} </label>

                            <div class="col-md-6">
                                <textarea id="description" class="form-control" name="description"  rows="4" cols="85" >{{ $description }}</textarea>
                                <!-- <input id="description" type="text" class="form-control" name="description" value="{{ $description }}"> -->
                            </div>
                        </div>
                        
                        <!-- Pre contest substitution -->
                        <?php $pre_contest_substitution = (old('pre_contest_substitution') ? old('pre_contest_substitution') : (isset($item) ? $item->pre_contest_substitution : '')); ?>
                        <div class="form-group">
                            <label for="pre_contest_substitution" class="col-md-2 control-label"> {{ trans('adminlabels.pre_contest_substitution') }} </label>

                            <div class="col-sm-6">
                                <select class="form-control" id="pre_contest_substitution" name="pre_contest_substitution" disabled="disabled">
                                    <option value="0" selected>{{trans('adminlabels.no')}}</option>
                                    <option value="1" <?php if ($pre_contest_substitution == 1) echo 'selected'; ?> >{{trans('adminlabels.yes')}}</option>
                                </select>
                            </div>
                        </div>
                        <?php $contest_substitution = (old('contest_substitution') ? old('contest_substitution') : (isset($item) ? $item->contest_substitution : '')); ?>
                        <div class="form-group">
                            <label for="contest_substitution" class="col-md-2 control-label"> {{ trans('adminlabels.contest_substitution') }} </label>

                            <div class="col-sm-6">
                                <select class="form-control" id="contest_substitution" name="contest_substitution" disabled="disabled">
                                    <option value="0" selected>{{trans('adminlabels.no')}}</option>
                                    <option value="1" <?php if ($contest_substitution == 1) echo 'selected'; ?> >{{trans('adminlabels.yes')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary"> {{ trans('adminlabels.submit') }} </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/items')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
<script>
jQuery(document).ready(function () {

    var itemRules = {
        name: {
            required: true,
            maxlength: 100
        }
    };
    $("#itemForm").validate({
        rules: itemRules
    });
});
</script>
@endsection