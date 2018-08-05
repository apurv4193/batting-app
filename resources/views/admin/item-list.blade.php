@extends('layouts.admin-master')

@section('content')

<section class="content-header">
    <h1>
        {{trans('adminlabels.item_management')}}
        <small>{{trans('adminlabels.item')}}</small>
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.item_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listItem" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.item_list_name_label')}}</th>
                                <th>{{trans('adminlabels.item_list_image_label')}}</th>
                                <th>{{trans('adminlabels.item_list_points_label')}}</th>
                                <th>{{trans('adminlabels.item_list_pre_contest_substitution_label')}}</th>
                                <th>{{trans('adminlabels.item_list_contest_substitution_label')}}</th>
                                <th>{{trans('adminlabels.item_list_action_label')}}</th>
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
    var getItemList = function(ajaxParams) {
        $('#listItem').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                    "url": "{{ url('admin/list-item-ajax') }}",
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
                { "data": "item_image", "orderable": false },
                { "data": "points" },
                { "data": "pre_contest_substitution" },
                { "data": "contest_substitution" },
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
        getItemList(ajaxParams);
    });        
</script>
@endsection