@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.players_management')}}
        <small>{{trans('adminlabels.players')}}</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($Playesr) && !empty($Playesr)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.players')}}</h3>
                </div>
                <form class="form-horizontal" id="addPlayers" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-players') }}">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <?php $id = ((isset($Playesr) && !empty($Playesr)) ? $Playesr->id : '0'); ?>
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="hidden_profile" value="<?php echo (isset($Playesr) && !empty($Playesr)) ? $Playesr->profile_image : ''; ?>">
                        <?php $name = (old('name')) ? old('name') : ((!empty($Playesr) && $Playesr->name) ? $Playesr->name : ''); ?>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.players_name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('adminlabels.players_name') }}" value="{{ $name }}">
                            </div>
                        </div>
                        <?php
                        /*$games_id = array();
                        if(isset($Games)){
                            foreach ($Games as $key => $value) {
                                $games_id[] = (old('game_id') ? old('game_id') : (isset($Games) ? $value->game_id : ''));
                            }
                        }*/
                        if(!isset($Playesr) && empty($Playesr))
                        {
                        ?>
                        <div class="form-group">
                            <label for="game_id" class="col-md-2 control-label"> {{ trans('adminlabels.game_list') }} </label>
                            <div class="col-md-6">
                                <select class="form-control chosen" id="game_id" name="game_id[]" multiple="multiple" data-placeholder="Select games...">
                                    <option value="">{{ trans('adminlabels.game_list') }}</option>
                                    <?php foreach ($gameList as $game_List) { ?>
                                        <option value="{{$game_List->id}}"  >{{$game_List->name}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php } ?>
                        <?php $description = (old('description') ? old('description') : (isset($Playesr) ? $Playesr->description : '')); ?>
                        <div class="form-group">
                            <label for="description" class="col-md-2 control-label"> {{ trans('adminlabels.players_description') }} </label>
                            <div class="col-md-6">
                                <textarea name="description" id="description" rows="4" cols="85">{{$description}}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="profile_image" class="col-md-2 control-label">{{ trans('adminlabels.players_image')}}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" id="profile_image" name="profile_image">
                                <?php
                                if (isset($Playesr) && $Playesr->id != '0') {
                                    if (File::exists(public_path($playersImage . $Playesr->profile_image)) && $Playesr->profile_image != '') {
                                        ?>
                                        <img src="{{ url($playersImage.$Playesr->profile_image) }}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" alt="Default Image" height="70" width="70">
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <!-- <?php $cap_amount = (old('cap_amount') ? old('cap_amount') : (isset($Playesr) ? $Playesr->cap_amount : '')); ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="cap_amount">{{trans('adminlabels.players_cap_amount')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="cap_amount" id="cap_amount" class="form-control" value="{{$cap_amount}}">
                            </div>
                        </div>
                        <?php $win = (old('win') ? old('win') : (isset($Playesr) ? $Playesr->win : '')); ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="win">{{trans('adminlabels.players_win')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="win" id="win" class="form-control" value="{{$win}}">
                            </div>
                        </div>
                        <?php $loss = (old('loss') ? old('loss') : (isset($Playesr) ? $Playesr->loss : '')); ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="loss">{{trans('adminlabels.players_loss')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="loss" id="loss" class="form-control" value="{{$loss}}">
                            </div>
                        </div> -->
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/players')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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

        $('.chosen').chosen();

        $('#cap_amount').on('keyup', function () {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        $('#win').on('keyup', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        $('#loss').on('keyup', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addPlayers").validate({
                rules: {
                    name: {
                        required: true
                    },
                    game_id: {
                        required: true
                    },
                    description: {
                        required: true,
                        maxlength: 240
                    },
                    /*cap_amount: {
                        required: true
                    },
                    win: {
                        required: true
                    },
                    loss: {
                        required: true
                    }*/
                }
            });
        } else {
            $("#addPlayers").validate({
                rules: {
                    name: {
                        required: true
                    },
                    game_id: {
                        required: true
                    },
                    description: {
                        required: true,
                        maxlength: 240
                    },
                    profile_image: {
                        required: true
                    }
                }
            });
        }
    });
</script>
@endsection
