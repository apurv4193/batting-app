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
                    <h3 class="box-title"><?php echo (isset($Games) && !empty($Games)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.players_games')}}</h3>
                </div>                
                <form class="form-horizontal" id="addPlayersGames" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-players-games') }}">
                    {{ csrf_field() }}
                    <div class="box-body">     
                        <?php
                              $id = ((isset($Games) && !empty($Games)) ? $Games->id : '0');
                              $player_id = ((isset($Games) && !empty($Games)) ? $Games->player_id : $playerId);?>
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="player_id" value="{{ $player_id }}">
                        <?php 
                        /*$games_id = array();
                        if(isset($Games)){
                            foreach ($Games as $key => $value) {
                                $games_id[] = (old('game_id') ? old('game_id') : (isset($Games) ? $value->game_id : ''));
                            }
                        }*/
                        $game_id = (old('game_id') ? old('game_id') : (isset($Games) ? $Games->game_id : '')); 
                        ?>
                        <div class="form-group">
                            <label for="game_id" class="col-md-2 control-label"> {{ trans('adminlabels.game_list') }} </label>
                            <div class="col-md-6">
                                <select class="form-control" id="game_id" name="game_id" data-placeholder="Select games...">
                                    <option value="">{{ trans('adminlabels.game_list') }}</option>
                                    <?php foreach ($gameList as $game_List) { ?>
                                        <option value="{{$game_List->id}}" <?php if($game_id == $game_List->id) echo 'selected'; ?> >{{$game_List->name}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <?php $cap_amount = (old('cap_amount') ? old('cap_amount') : (isset($Games) ? $Games->cap_amount : '')); ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="cap_amount">{{trans('adminlabels.players_cap_amount')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="cap_amount" id="cap_amount" class="form-control" value="{{$cap_amount}}">
                            </div>
                        </div>
                        <?php $win = (old('win') ? old('win') : (isset($Games) ? $Games->win : '')); ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="win">{{trans('adminlabels.players_win')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="win" id="win" class="form-control" value="{{$win}}">
                            </div>
                        </div>
                        <?php $loss = (old('loss') ? old('loss') : (isset($Games) ? $Games->loss : '')); ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="loss">{{trans('adminlabels.players_loss')}}</label>
                            <div class="col-md-6">
                                <input type="text" name="loss" id="loss" class="form-control" value="{{$loss}}">
                            </div>
                        </div>                      
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <?php if(isset($Games) && !empty($Games)){
                                    $playerId = $Games->player_id;
                                }
                                ?>
                                <a href="{{url('admin/view-games/'.$playerId)}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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

        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addPlayersGames").validate({
                rules: {
                    game_id: {
                        required: true
                    },
                    cap_amount: {
                        required: true,
                        min:0,
                        max:999999
                    },
                    win: {
                        required: true,
                        min:0,
                        max:999999
                    },
                    loss: {
                        required: true,
                        min:0,
                        max:999999
                    }
                }
            });
        } else {
            $("#addPlayersGames").validate({
                rules: {
                    game_id: {
                        required: true
                    },
                    cap_amount: {
                        required: true,
                        min:0,
                        max:999999
                    },
                    win: {
                        required: true,
                        min:0,
                        max:999999
                    },
                    loss: {
                        required: true,
                        min:0,
                        max:999999
                    }
                }
            });
        }
    });
</script>
@endsection