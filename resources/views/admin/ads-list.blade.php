@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.ads_management')}}
        <small>{{trans('adminlabels.ads')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-ads') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.ads_name_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listAd" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.ads_list_name')}}</th>
                                <th>{{trans('adminlabels.ads_list_media')}}</th>
                                <th>{{trans('adminlabels.ads_list_media_duration')}}</th>
                                <th>{{trans('adminlabels.game_list_action_label')}}</th>
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
    var getadsList = function(ajaxParams) {
        $("#listAd").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-ads-ajax') }}",
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
                { "data": "name" },
                { "data": "file", "orderable": false },
                { "data": "no_secs_display" },
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
        getadsList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-ads', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Ads ?';
            var ctitle = 'Delete Ads';

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
                            getadsList(ajaxParams);
                        }
                    },
                    No: {
                        label: 'No',
                        className: 'btn btn-default'
                    }
                }
            });
        });
        // For set Default ads
        $(document).on('click', '.btn-default-ads', function (e) {
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to set this Ad as default ?';
            var ctitle = 'Set Ads';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'default';
            ajaxParams.id = userId;

            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getadsList(ajaxParams);
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