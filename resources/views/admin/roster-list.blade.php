@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.roster_management')}}
        <small>{{trans('adminlabels.roster')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-roster') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.roster_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listRoster" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.roster_list_contest_label')}}</th>
                                <th>{{trans('adminlabels.roster_list_roster_label')}}</th>
                                <th>{{trans('adminlabels.roster_list_roster_cap_amount_label')}}</th>
                                <th>{{trans('adminlabels.roster_list_action_label')}}</th>
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
<div class="modal fade" id="addRosterPlayer" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{trans('adminlabels.roster_player_list_title_label')}}</h4>
            </div>
            <div class="modal-body" id="addRosterPlayerContet">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection
@section('script')

<script>
    var getRostersList = function(ajaxParams) {
        $("#listRoster").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-roster-ajax') }}",
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
                    }
                }
            },
            "columns": [
                { "data": "contest_name" },
                { "data": "roster" }, 
                { "data": "roster_cap_amount" }, 
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
        getRostersList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-roster', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Roster ?';
            var ctitle = 'Delete Roster';

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
                            getRostersList(ajaxParams);
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
    function addPlayer(rosterId) {
            $.ajax({
                type: "GET",
                url: "{{ url('admin/roster-playes') }}",
                //contentType: "application/json; charset=utf-8",
                data: { "rosterId": rosterId },
                datatype: "json",
                success: function (data) {
                    alert(data);
                    // $('#myModalContent').html(data);
                    // $('#myModal').modal('show');
                },
                error: function () {
                    alert("Error: Dynamic content load failed.");
                }
            });
        }
</script>
@endsection