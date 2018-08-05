@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.team_management')}}
        <small>{{trans('adminlabels.teams')}}</small>
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($team) && !empty($team)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.teams')}}</h3>
                </div>                
                <form class="form-horizontal" id="addTeam" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-team') }}">
                    {{ csrf_field() }}
                    <div class="box-body">     
                        <?php $id = ((isset($team) && !empty($team)) ? $team->id : '0'); ?>
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="hidden_team_image" value="<?php echo (isset($team) && !empty($team)) ? $team->team_image : ''; ?>">                    

                        <?php $name = (old('name')) ? old('name') : ((!empty($team) && $team->name) ? $team->name : ''); ?>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.teams_name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('adminlabels.teams_name') }}" value="{{ $name }}" required>
                            </div>                                    
                        </div>

                        <div class="form-group">
                            <label for="team_image" class="col-md-2 control-label">{{ trans('adminlabels.team_image')}}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" id="team_image" name="team_image">
                                <?php if (isset($team) && $team->id != '0') {
                                    if (File::exists(public_path($teamThumbImagePath . $team->team_image)) && $team->team_image != '') { ?>
                                        <img src="{{ url($teamThumbImagePath.$team->team_image) }}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" alt="Default Image" height="70" width="70">
                                        <?php }
                                } ?>
                            </div>
                        </div>

                        <?php $game_id = (old('game_id') ? old('game_id') : (isset($team) ? $team->game_id : '')); ?>
                        <div class="form-group">
                            <label for="game_id" class="col-md-2 control-label"> {{ trans('adminlabels.game_list') }} </label>
                            <div class="col-md-6">
                                <select class="form-control" id="game_id" name="game_id" data-placeholder="Select games..." required>
                                    <option value="">{{ trans('adminlabels.game_list') }}</option>
                                    <?php foreach ($gameList as $_gameList) { ?>
                                        <option value="{{$_gameList->id}}"  <?php if ($_gameList->id == $game_id){ ?> selected <?php } ?> > {{$_gameList->name}}</option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>
                        
                        <!-- end -->
                        <!-- Contest Type list -->
                        <?php $contest_type_id = (old('contest_type_id') ? old('contest_type_id') : (isset($team) ? $team->contest_type_id : '')); ?>
                        <div class="form-group">
                            <label for="contest_type_id" class="col-md-2 control-label"> {{ trans('adminlabels.contest_type') }} </label>
                            <div class="col-md-6">
                                <select class="form-control" id="contest_type_id" name="contest_type_id" data-placeholder="Select Contest Type..." required> 
                                    <option value="">{{ trans('adminlabels.contest_type') }}</option>
                                    <?php foreach ($contestTypesList as $_contestTypesList) { ?>
                                        <option value="{{$_contestTypesList->id}}" <?php if ($_contestTypesList->id == $contest_type_id){  ?> selected <?php } ?> >{{$_contestTypesList->type}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div id = "contest_cap_amount"></div>
                        </div>
                        <!-- end -->

                        @if($id > 0)
                        <!-- Team win-->
                        <?php $win = (old('win') ? old('win') : (isset($team) ? $team->win : 0)); ?>
                        <div class="form-group">
                            <label for="win" class="col-sm-2 control-label">{{ trans('adminlabels.win') }}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="win" name="win" placeholder="{{ trans('adminlabels.win') }}" value="{{ $win }}" required>
                            </div>                                    
                        </div>
                        <!-- end -->
                        <!-- Team loss-->
                        <?php $loss = (old('loss') ? old('loss') : (isset($team) ? $team->loss : 0)); ?>
                        <div class="form-group">
                            <label for="loss" class="col-sm-2 control-label">{{ trans('adminlabels.loss') }}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="loss" name="loss" placeholder="{{ trans('adminlabels.loss') }}" value="{{ $loss }}" required>
                            </div>                                    
                        </div>
                        <!-- end -->
                        @endif
                        
                        <!-- player list according to game -->
                        <div class="form-group">
                            <label for="player_id" class="col-md-2 control-label"> {{ trans('adminlabels.players_list') }} </label>
                            <div class="col-md-6">
                                <select class="form-control chosen" id="player" name="player[]" multiple="multiple" data-placeholder="Select players..." required>
                            <?php if(isset($team) && $team->id > 0) { ?>
                                    <?php foreach ($playerList as $_playerList) { ?>
                                        <option value="<?php echo $_playerList->id; ?>" <?php if(in_array($_playerList->id, $teamPlayer)) { ?> selected <?php } ?>>{{$_playerList->name}} - {{$_playerList->cap_amount}}</option>
                                    <?php } ?>
                            <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!-- end -->
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/team')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
   //Get contest type cap amount
   $("#contest_type_id").change(function () {
        contestId = this.value;
        $.ajax({
            type: "GET",
            url: "{{ url('admin/getcapamount') }}",
            data: {'contestId': contestId},
            success: function(data){
               var data = JSON.parse(data);
               console.log(data);
                $('#player option').prop('selected', false);
                $("#player").chosen('destroy');
                $("#contest_cap_amount").html(data.capAmount);
                $("#player").chosen({max_selected_options: data.contestMaxPlayer});
            }
        });
    });

    //Get player according to game
    $("#game_id").change(function () {
        gameId = this.value;
        $.ajax({
            type: "GET",
            url: "{{ url('admin/getplayerbygame') }}",
            data: {'gameId': gameId},
            success: function(data){
               var data = JSON.parse(data);
                $("#player").empty()
                for (var i = 0; i < data.length; i++) {
                    $("#player").append($("<option></option>").val(data[i].id).html(data[i].name + ' - ' + data[i].cap_amount))
                }
                $('.chosen').trigger("chosen:updated");

            }
        });
    });
    //end
    jQuery(document).ready(function () {
        $("#player").chosen();

        $('#win').on('keyup', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        $('#loss').on('keyup', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addTeam").validate({
                rules: {
                    name: {
                        required: true
                    },
                    game_id: {
                        required: true
                    },
                    contest_type_id: {
                        required: true
                    }
                }
            });
        } else {
            $("#addTeam").validate({
                rules: {
                    name: {
                        required: true
                    },
                    game_id: {
                        required: true
                    },
                    contest_type_id: {
                        required: true
                    }
                }
            });
        }
    });
</script>
@endsection