@extends('layouts.admin-master')

@section('header')
<link rel="stylesheet" type="text/css" href="{{ asset('css/admin/bootstrap/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" media="screen" />

@section('content')
    <section class="content-header">
        <h1>
            {{trans('adminlabels.contest_management')}}
            <small>{{trans('adminlabels.create_contest')}}</small>
        </h1>     
    </section>

    <section class="content">
        <div class="row">
            <!-- right column -->
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo (isset($contest) && !empty($contest)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.contest_label')}}</h3>
                    </div>
                    <form class="form-horizontal" enctype="multipart/form-data" id="addContest" method="POST" action="{{ url('admin/save-contest') }}">
                        {{ csrf_field() }}
                        <div class="box-body">
                            
                            <?php $contest_id = (isset($contest) && !empty($contest)) ? $contest->id : '0' ?>
                            <?php $game_id = (old('game_id') ? old('game_id') : (isset($contest) ? $contest->game_id : '')); ?>
                            <input type="hidden" name="id" value="{{$contest_id}}">
                            <input type="hidden" name="hidden_banner" value="<?php echo (isset($contest) && !empty($contest)) ? $contest->banner : ''; ?>">
                            <input type="hidden" name="hidden_video" value="<?php echo (isset($contest) && !empty($contest)) ? $contest->sponsored_video_link : ''; ?>">
                            <input type="hidden" name="hidden_image" value="<?php echo (isset($contest) && !empty($contest)) ? $contest->sponsored_image : ''; ?>">
                            <div class="form-group">
                                <label for="game_id" class="col-md-2 control-label"> {{ trans('adminlabels.game_label') }} </label>
                                <div class="col-md-6">
                                    <select class="form-control" id="game_id" name="game_id" @if($contest_id == 0 || empty($contest_id)) @else disabled="disabled"  @endif >
                                        <option value="">{{ trans('adminlabels.select_game') }}</option>
                                        <?php foreach ($games as $game) { ?>
                                            <option value="{{$game->id}}" <?php if($game_id == $game->id) echo 'selected'; ?> >{{$game->name}}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <?php $contest_name = (old('contest_name') ? old('contest_name') : (isset($contest) ? $contest->contest_name : '')); ?>
                            <div class="form-group">
                                <label for="contest_name" class="col-md-2 control-label"> {{ trans('adminlabels.contest_name') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_name" type="text" class="form-control" name="contest_name" value="{{ $contest_name }}" autofocus>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="banner" class="col-md-2 control-label">{{ trans('adminlabels.banner')}}</label>
                                <div class="col-md-6">
                                    <input type="file" class="form-control" id="banner" name="banner">
                                    <?php
                                    if (isset($contest) && $contest->id != '0') {
                                        if (File::exists(public_path($contestThumbPath . $contest->banner)) && $contest->banner != '') {
                                            ?>
                                            <img src="{{ url($contestThumbPath.$contest->banner) }}"  height="70" width="70">
                                        <?php }else if(File::exists(public_path($contestThumbPath . $contest->video_thumb)) && $contest->video_thumb != ''){
                                            ?>
                                            <img src="{{ url($contestThumbPath.$contest->video_thumb) }}"  height="70" width="70">
                                        <?php } else { ?>
                                            <img src="{{ asset('/images/default.png')}}" class="user-image" alt="Default Image" height="70" width="70">
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <?php $contest_level = (old('level_id') ? old('level_id') : (isset($contest) ? $contest->level_id : '')); ?>
                            <div class="form-group">
                                <label for="level_id" class="col-md-2 control-label"> {{ trans('adminlabels.contest_level') }} </label>
                                <div class="col-md-6">
                                    <select class="form-control" id="level_id" name="level_id" @if($contest_id == 0 || empty($contest_id)) @else disabled="disabled"  @endif>
                                        <option value="">{{ trans('adminlabels.select_contest_level') }}</option>
                                        <?php foreach ($contestLevels as $contestLevel) { ?>
                                            <option value="{{$contestLevel->id}}" <?php if($contest_level == $contestLevel->id) echo 'selected'; ?> >{{$contestLevel->name}}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <?php $contest_type_id = (old('contest_type_id') ? old('contest_type_id') : (isset($contest) ? $contest->contest_type_id : '')); ?>
                            <div class="form-group">
                                <label for="contest_type_id" class="col-md-2 control-label"> {{ trans('adminlabels.contest_type') }} </label>
                                <div class="col-md-6">
                                    <select class="form-control" id="contest_type_id" name="contest_type_id" @if($contest_id == 0 || empty($contest_id)) @else disabled="disabled"  @endif>
                                        <option value="">{{ trans('adminlabels.select_contest_type') }}</option>
                                        <?php foreach ($contestTypes as $contestType) { ?>
                                            <option value="{{$contestType->id}}" <?php if($contest_type_id == $contestType->id) echo 'selected'; ?> >{{$contestType->type}}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            
                            <?php $contest_fees = (old('contest_fees') ? old('contest_fees') : (isset($contest) ? $contest->contest_fees : '')); ?>
                            <div class="form-group">
                                <label for="contest_fees" class="col-md-2 control-label"> {{ trans('adminlabels.contest_fees') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_fees" type="text" class="form-control" name="contest_fees" value="{{ $contest_fees }}" autofocus @if($contest_id == 0 || empty($contest_id)) @else disabled="disabled"  @endif>
                                </div>
                            </div>
                            <?php $contest_start_time = (old('contest_start_time') ? old('contest_start_time') : (isset($contest) ? $contest->contest_start_time : '')); ?>
                            <div class="form-group">
                                <label for="contest_start_time" class="col-md-2 control-label"> {{ trans('adminlabels.contest_start_time') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_start_time" type="text" class="form-control datetimepicker" name="contest_start_time" value="{{ $contest_start_time }}" >
                                </div>
                            </div>
                            <?php $contest_end_time = (old('contest_end_time') ? old('contest_end_time') : (isset($contest) ? $contest->contest_end_time : '')); ?>
                            <div class="form-group">
                                <label for="contest_end_time" class="col-md-2 control-label"> {{ trans('adminlabels.contest_end_time') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_end_time" type="text" class="form-control datetimepicker" name="contest_end_time" value="{{ $contest_end_time }}" >
                                </div>
                            </div>
                            <?php $contest_min_participants = (old('contest_min_participants') ? old('contest_min_participants') : (isset($contest) ? $contest->contest_min_participants : '')); ?>
                            <div class="form-group">
                                <label for="contest_min_participants" class="col-md-2 control-label"> {{ trans('adminlabels.contest_min_participants') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_min_participants" type="text" class="form-control" name="contest_min_participants" value="{{ $contest_min_participants }}" autofocus>
                                </div>
                            </div>
                            <?php $contest_max_participants = (old('contest_max_participants') ? old('contest_max_participants') : (isset($contest) ? $contest->contest_max_participants : '')); ?>
                            <div class="form-group">
                                <label for="contest_max_participants" class="col-md-2 control-label"> {{ trans('adminlabels.contest_max_participants') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_max_participants" type="text" class="form-control" name="contest_max_participants" value="{{ $contest_max_participants }}" autofocus>
                                </div>
                            </div>
                            <?php $prize_distribution_plan_id = (old('prize_distribution_plan_id') ? old('prize_distribution_plan_id') : (isset($contest) ? $contest->prize_distribution_plan_id : '')); ?>
                            <div class="form-group">
                                <label for="prize_distribution_plan_id" class="col-md-2 control-label"> {{ trans('adminlabels.prize_distribution_plan') }} </label>
                                <div class="col-md-6">
                                    <select class="form-control" id="prize_distribution_plan_id" name="prize_distribution_plan_id">
                                        <option value="">{{ trans('adminlabels.select_prize_distribution_plan') }}</option>
                                        <?php foreach ($prizeDistributionPlan as $_prizeDistributionPlan) { ?>
                                            <option value="{{$_prizeDistributionPlan->id}}" <?php if($prize_distribution_plan_id == $_prizeDistributionPlan->id) echo 'selected'; ?> >{{$_prizeDistributionPlan->name}}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <?php $contest_video_link = (old('contest_video_link') ? old('contest_video_link') : (isset($contest) ? $contest->contest_video_link : '')); ?>
                            <div class="form-group">
                                <label for="contest_video_link" class="col-md-2 control-label"> {{ trans('adminlabels.contest_video_link') }} </label>
                                <div class="col-md-6">
                                    <input id="contest_video_link" type="text" class="form-control" name="contest_video_link" value="{{ $contest_video_link }}" autofocus>
                                </div>
                            </div>
                            <?php $sponsored_by = (old('sponsored_by') ? old('sponsored_by') : (isset($contest) ? $contest->sponsored_by : '')); ?>
                            <div class="form-group">
                                <label for="sponsored_by" class="col-md-2 control-label"> {{ trans('adminlabels.sponsored_by') }} </label>
                                <div class="col-md-6">
                                    <input id="sponsored_by" type="text" class="form-control" name="sponsored_by" value="{{ $sponsored_by }}" autofocus>
                                </div>
                            </div>
                            <?php $sponsored_prize = (old('sponsored_prize') ? old('sponsored_prize') : (isset($contest) ? $contest->sponsored_prize : '')); ?>
                            <div class="form-group">
                                <label for="sponsored_prize" class="col-md-2 control-label"> {{ trans('adminlabels.sponsored_prize') }} </label>
                                <div class="col-md-6">
                                    <input id="sponsored_prize" type="text" class="form-control" name="sponsored_prize" value="{{ $sponsored_prize }}" autofocus>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sponsored_video_link" class="col-md-2 control-label">{{ trans('adminlabels.sponsored_video_link')}}</label>
                                <div class="col-md-6">
                                    <input type="file" class="form-control" id="sponsored_video_link" name="sponsored_video_link">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sponsored_image" class="col-md-2 control-label">{{ trans('adminlabels.sponsored_image')}}</label>
                                <div class="col-md-6">
                                    <input type="file" class="form-control" id="sponsored_image" name="sponsored_image">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sponsored_link" class="col-md-2 control-label"> {{ trans('adminlabels.sponsored_link') }} </label>
                                <div class="col-md-6">
                                    <input id="sponsored_link" type="text" class="form-control" name="sponsored_link" autofocus>
                                </div>
                            </div>
                            <?php $is_teamwise = (old('is_teamwise') ? old('is_teamwise') : (isset($contest) ? $contest->is_teamwise : 0)); ?>
                            <div class="form-group">
                                <label for="is_teanwise" class="col-md-2 control-label"> {{ trans('adminlabels.team_wise') }} </label>
                                <div class="col-md-6">
                                    <div class="squaredThree">
                                        <input type="checkbox" id="is_teanwise" name="is_teamwise" value="1" <?php if($is_teamwise == 1) echo "checked"; ?>/>
                                        <label for="squaredThree">Yes</label>
                                    </div> 
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="form-group">
                                <div class="col-md-1 col-md-offset-2">
                                    <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{url('admin/contests')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/additional-methods.js"></script>
<script type="text/javascript" src="{{ asset('js/admin/moment-with-locales.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/admin/bootstrap-datetimepicker.min.js') }}"></script>
<script>
    jQuery(document).ready(function () {
        var startDateTime = $('#contest_start_time').val();
        $('#contest_start_time').datetimepicker({
            'format': 'YYYY-MM-DD HH:mm:00',
        });
        $('#contest_fees,#sponsored_prize').on('keyup', function(){
             this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        $('#contest_max_participants').on('keyup', function(){
             this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        if(startDateTime) {
            $('#contest_start_time').data("DateTimePicker").minDate(startDateTime);
        } else {
            $('#contest_start_time').data("DateTimePicker").minDate(new Date());
        }
        
        $('#contest_end_time').datetimepicker({
            'format': 'YYYY-MM-DD HH:mm:00'
        });
        $("#contest_start_time").on("dp.change",function (e) {
            $('#contest_end_time').data("DateTimePicker").minDate(e.date);
        });
        
        $('.datetimepicker').keydown(function(e) {
            e.preventDefault();
            return false;
        });

        $("#addContest").validate({
            ignore: ":hidden:not(select)",
            rules: {
                game_id: {
                    required: true
                },
                contest_name: {
                    required: true,
                    maxlength: 40
                },
                banner:{
                    // required: true,
                    extension: "webm|mkv|flv|vob|ogv|ogg|wmv|asf|amv|mp4|m4p|m4v|m4v|3gp|3g2|f4v|f4p|f4a|f4b|mpeg|mpg|m2v|mpv|mpe|png|jpeg|jpg|bmp"
                },
                level_id:{
                    required: true
                },
                contest_type_id: {
                    required: true
                },
                contest_fees: {
                    required: true,
                    number: true
                },
                contest_start_time: {
                    required: true
                },
                contest_end_time: {
                    required: true
                },
                contest_min_participants:{
                    required: true
                },
                contest_max_participants:{
                    required: true
                },
                prize_distribution_plan_id:{
                    required: true
                },
                contest_video_link: {
                    //required: true,
                    maxlength: 255
                },
                sponsored_by: {
                    required: false,
                    maxlength: 255
                },
                sponsored_prize: {
                    number: true
                }
            }
        });
    });
</script>
@endsection