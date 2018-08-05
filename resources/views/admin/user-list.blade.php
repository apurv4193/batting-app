@extends('layouts.admin-master')

@section('content')

<section class="content-header">
    <h1>
        {{trans('adminlabels.user_management')}}
        <small>{{trans('adminlabels.users')}}</small>
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.user_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listUser" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.user_list_name_label')}}</th>
                                <th>{{trans('adminlabels.user_list_email_label')}}</th>
                                <th>{{trans('adminlabels.user_list_phone_label')}}</th>
                                <th>{{trans('adminlabels.user_list_photo_label')}}</th>
                                <th>{{trans('adminlabels.user_list_action_label')}}</th>
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
    var getUserList = function(ajaxParams) {
        $('#listUser').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                    "url": "{{ url('admin/list-user-ajax') }}",
                    "dataType": "json",
                    "type": "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function(data) {
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
                { "data": "email" },
                { "data": "phone" },
                { "data": "user_pic", "orderable": false },
                { "data": "action", "orderable": false }
            ],
            "initComplete": function(settings, json) {
                if(typeof(json.customMessage) != "undefined" && json.customMessage !== '') {
                    $('.success-msg').addClass('hidden');
                    $('.error-msg').addClass('hidden');
                    $('.customMessage').removeClass('hidden');
                    $('#customMessage').html(json.customMessage);
                }
            }
        });
    };
    $(document).ready(function () {
        var ajaxParams = {};
        getUserList(ajaxParams);

        // Remove user
        $(document).on('click', '.btn-delete-user', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this User ?';
            var ctitle = 'Delete User';

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
                            getUserList(ajaxParams);
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