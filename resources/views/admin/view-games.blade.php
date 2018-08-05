@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.players_management')}}
        <small>{{trans('adminlabels.players_game')}}</small>
        <div class="pull-right">
            <div class="col-md-6">
                <a href="{{ url('admin/add-players-games/'.$id) }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
            </div>
            <div class="col-md-6">
                <a href="{{ url('/admin/players') }}" class="btn btn-block btn-danger add-btn-primary pull-right">{{trans('adminlabels.back')}}</a>
            </div>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.players_games')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listGames" class="table table-bordered table-striped">
                        <thead>
                            <tr>                               
                                <th>{{trans('adminlabels.players_game')}}</th>                                
                                <th>{{trans('adminlabels.players_cap_amount')}}</th>                                
                                <th>{{trans('adminlabels.players_win')}}</th>                                
                                <th>{{trans('adminlabels.players_loss')}}</th>                                
                                <th>{{trans('adminlabels.players_list_action_label')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>

        </div>
        <!--/.col (right) -->
    </div>
    <!-- /.row -->
</section>
@endsection
@section('script')

<script>
    var player_id = "<?php echo $id ?>";
    ajaxParams = {"player_id":player_id};
    var getPlayersGamesList = function(ajaxParams) {
        
        $("#listGames").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-games-ajax') }}",
                "dataType": "json",
                "type": "POST",
                headers: { 
                    'X-CSRF-TOKEN': "{{ csrf_token() }} "
                },
                "data" : function(data) {
                    if (ajaxParams) {
                        $.each(ajaxParams, function(key, value) {
                            data[key] = value;
                        });
                        ajaxParams = {"player_id":player_id};
                    }
                }
            },
            "columns": [                
                { "data": "game_name"},                
                { "data": "cap_amount"},                
                { "data": "win"},                
                { "data": "loss"},               
                { "data": "action", "orderable": false }
            ], 
            "initComplete": function(settings, json) {
                if(typeof(json.customMessage) != "undefined" && json.customMessage !== '') {
                    $('.customMessage').removeClass('hidden');
                    $('#customMessage').html(json.customMessage);
                }
            }
        });
    };
    $(document).ready(function () {
        //var player_id = "<?php echo $id ?>";
/*
        var ajaxParams = {};
        ajaxParams.player_id = player_id;*/
        ajaxParams = {"player_id":player_id};
         console.log(ajaxParams);
        getPlayersGamesList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-players', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Players Name ?';
            var ctitle = 'Delete Players';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'delete';
            ajaxParams.id = [userId];


            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getPlayersGamesList(ajaxParams);
                        }
                    },
                    No: {
                        label: 'No',
                        className: 'btn btn-default'
                    }
                }
            });
        });
    });
</script>
@endsection