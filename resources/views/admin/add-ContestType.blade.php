@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.contest_type')}}
        <small>{{trans('adminlabels.contest_type')}}</small>
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($contestType) && !empty($contestType)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.contest_type')}}</h3>
                </div>                
                <form class="form-horizontal" id="addContestType" method="POST" action="{{ url('admin/save-contest-type') }}">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <input type="hidden" name="id" value="<?php echo (isset($contestType) && !empty($contestType)) ? $contestType->id : '0'; ?>">
                        <?php $type = (old('type') ? old('type') : (isset($contestType) ? $contestType->type : '')); ?>
                        <div class="form-group">
                            <label for="type" class="col-md-2 control-label">{{ trans('adminlabels.type') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  readonly="readonly"  disabled="disabled" name="type" id="type" placeholder="{{ trans('adminlabels.type') }}" value="{{ $type }}"/>                        
                                @if ($errors->has('type'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('type') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <?php $capAmount = (old('contest_cap_amount') ? old('contest_cap_amount') : (isset($contestType) ? $contestType->contest_cap_amount : '')); ?>
                        <div class="form-group">
                            <label for="contest_cap_amount" class="col-md-2 control-label">{{ trans('adminlabels.contest_cap_amount')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="contest_cap_amount" id="contest_cap_amount" class="form-control" value="{{ $capAmount }}" placeholder="{{ trans('adminlabels.contest_cap_amount')}}"/>
                                @if($errors->has('type'))
                                <span class="help-block">
                                    <strong>{{$errors->first('contest_cap_amount')}}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/contest_type')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
    $(document).ready(function () {
        $('#contest_cap_amount').on('keyup', function () {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        var addAdsRules = {
            contest_cap_amount: {
                required: true
            }
        };
        $("#addContestType").validate({
            rules: addAdsRules,
        });
    });
</script>
@endsection