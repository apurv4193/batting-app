@extends('layouts.admin-master')

@section('content')
    <section class="content-header">
        <h1>
            {{trans('adminlabels.roster_management')}}
            <small>{{trans('adminlabels.create_roster')}}</small>
        </h1>     
    </section>

    <section class="content">
        <div class="row">
            <!-- right column -->
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo (isset($roster) && !empty($roster)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.roster_label')}}</h3>
                    </div>
                    <form class="form-horizontal" id="addRoster" method="POST" action="{{ url('admin/save-roster') }}">
                        {{ csrf_field() }}
                        <div class="box-body">
                            <input type="hidden" name="id" value="<?php echo (isset($roster) && !empty($roster)) ? $roster->id : '0' ?>">
                            <?php $contest_id = (old('contest_id') ? old('contest_id') : (isset($roster) ? $roster->contest_id : '')); ?>
                            <div class="form-group{{ $errors->has('contest_id') ? ' has-error' : '' }}">
                                <label for="contest_id" class="col-md-2 control-label"> {{ trans('adminlabels.contest_id') }} </label>
                                <div class="col-md-6">
                                    <select class="form-control" id="contest_id" name="contest_id">
                                        <option value="">{{ trans('adminlabels.select_label') }}</option>
                                        <?php foreach ($contests as $contest) { ?>
                                            <option value="{{$contest->id}}" <?php if($contest_id == $contest->id) echo 'selected'; ?> >{{$contest->contest_name}}</option>
                                        <?php } ?>
                                    </select>
                                    @if ($errors->has('contest_id'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('contest_id') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <?php $rosters = (old('roster') ? old('roster') : (isset($roster) ? $roster->roster : '')); ?>
                            <div class="form-group{{ $errors->has('roster') ? ' has-error' : '' }}">
                                <label for="roster" class="col-md-2 control-label"> {{ trans('adminlabels.roster') }} </label>
                                <div class="col-md-6">
                                    <input id="roster" type="text" class="form-control" name="roster" value="{{ $rosters }}" autofocus>
                                    @if ($errors->has('roster'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('roster') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <?php $roster_cap_amount = (old('roster_cap_amount') ? old('roster_cap_amount') : (isset($roster) ? $roster->roster_cap_amount : '')); ?>
                            <div class="form-group{{ $errors->has('roster_cap_amount') ? ' has-error' : '' }}">
                                <label for="roster_cap_amount" class="col-md-2 control-label"> {{ trans('adminlabels.roster_cap_amount') }} </label>
                                <div class="col-md-6">
                                    <input id="roster_cap_amount" type="text" class="form-control" name="roster_cap_amount" value="{{ $roster_cap_amount }}" autofocus>
                                    @if ($errors->has('roster_cap_amount'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('roster_cap_amount') }}</strong>
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
                                    <a href="{{url('admin/rosters')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
        var addRosterRules = {
            contest_id: {
                required: true
            },
            roster: {
                required: true
            },
            roster_cap_amount: {
                required: true
            }
        };
        $("#addRoster").validate({
            rules: addRosterRules,
        });
    });
</script>
@endsection