@extends('layouts.admin-master')

@section('content')

<section class="content-header">
    <h1>
        {{trans('adminlabels.ads_management')}}
        <small><?php echo (isset($editAds) && !empty($editAds)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.ads') }}</small>        
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($editAds) && !empty($editAds)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.ads') }}</h3>
                </div>
                <form class="form-horizontal" id="addads" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-ads') }}">
                    {{ csrf_field() }} 
                    <div class="box-body">
                        <input type="hidden" name="id" value="<?php echo (isset($editAds) && !empty($editAds)) ? $editAds->id : '0'; ?>">
                        <input type="hidden" name="hidden_image" value="<?php echo (isset($editAds) && !empty($editAds)) ? $editAds->file : ''; ?>">
                        <input type="hidden" name="hidden_video_url" value="<?php echo (isset($editAds) && !empty($editAds)) ? $editAds->video_url : ''; ?>">
                        <?php $adsName = (old('name') ? old('name') : (isset($editAds) ? $editAds->name : '')); ?>
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">{{ trans('adminlabels.ads_name') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control" name="name" id="name" placeholder="{{ trans('adminlabels.ads_name') }}" value="{{ $adsName }}"/>                        
                            </div>
                        </div>                        
                        <div class="form-group">
                            <label for="file" class="col-md-2 control-label">{{ trans('adminlabels.ads_file') }}</label>
                            <div class="col-md-6 ">
                                <input type="file" class="form-control" id="file" name="file"> 
                                <?php
                                if (isset($editAds->id) && $editAds->id != '0') {
                                    if (File::exists(public_path($adsUploadImage . $editAds->file)) && $editAds->file != '') { ?>
                                        <img src="{{ url($adsUploadImage.$editAds->file) }}" alt="{{$editAds->file}}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" class="user-image" alt="Default Image" height="70" width="70">
                                        <?php }
                                }
                                ?>
                            </div>
                        </div>
                         <?php $no_secs_display = (old('no_secs_display') ? old('no_secs_display') : (isset($editAds) ? $editAds->no_secs_display : '')); ?>
                        <div class="form-group">
                            <label for="no_secs_display" class="col-md-2 control-label">{{ trans('adminlabels.ads_duration') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="no_secs_display" id="no_secs_display" placeholder="{{ trans('adminlabels.ads_duration') }}" value="{{ $no_secs_display }}"/>
                                @if ($errors->has('name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('no_secs_display') }}</strong>
                                </span>
                                @endif
                            </div>
                            <label for="ads_seconds" class="col-md-3 control-label" style="text-align: left;">{{ trans('adminlabels.ads_seconds') }}</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/ads')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
<script type="text/javascript">
    $(document).ready(function () {
        $('#no_secs_display').on('keyup', function (){
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        
        var addAdsRules = {
            name: {
                required: true
            },
            no_secs_display: {
                required: true,
                range: [1, 120]
            },
            file: {
                // required: true,
                extension: "webm|mkv|flv|vob|ogv|ogg|wmv|asf|amv|mp4|m4p|m4v|m4v|3gp|3g2|f4v|f4p|f4a|f4b|mpeg|mpg|m2v|mpv|mpe|png|jpeg|jpg|bmp",
                // filesize: 1024,
            }
        };
        $("#addads").validate({
            rules: addAdsRules,
        });
    });
</script>
@endsection