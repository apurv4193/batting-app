@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.roster_player_management')}}
        <small>{{trans('adminlabels.roster_player')}}</small>
    </h1>
</section>

<section class="content" style="min-height: auto;padding: 0 15px;">
    <div class="row">        
        <div class="col-md-12">
            <div class="box" style="margin:15px 0 0 0;">
                <div class="box-body">
                    <form class="form-horizontal" id="addPlayers" method="POST" action="{{ url('admin/save-roster-player') }}" style="padding-top: 20px;">
                        {{ csrf_field() }}
                        <input type="hidden" name="roster_id" value="{{ $rosterId }}">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.players_name') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control" id="player_id" name="player_id">
                                    <option value="">{{ trans('adminlabels.select_label') }}</option>
                                    <?php foreach ($players as $player) { ?>
                                        <option value="{{$player->id}}">{{$player->name}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                            <button type="submit" class="btn btn-primary">{{ trans('adminlabels.add') }}</button>
                            </div>                                    
                        </div>
                        
                    </form> 
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.roster_player_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listRosterPlayer" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.roster_player_list_name_label')}}</th>
                                <th>{{trans('adminlabels.roster_player_list_action_label')}}</th>
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
    var getRosterPlayersList = function(ajaxParams) {
        $("#listRosterPlayer").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-roster-player-ajax') }}",
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
                        ajaxParams = {};
                        ajaxParams.rosterId = <?php echo $rosterId; ?>;
                    }
                }
            },
            "columns": [
                { "data": "name" }, 
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
        var ajaxParams = {};
        ajaxParams.rosterId = <?php echo $rosterId; ?>;
        getRosterPlayersList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-roster', function(e){
            e.preventDefault();
            var playerId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this player ?';
            var ctitle = 'Delete Player';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'delete';
            ajaxParams.playerId = [playerId];

            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getRosterPlayersList(ajaxParams);
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