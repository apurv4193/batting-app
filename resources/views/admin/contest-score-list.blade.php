@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.contest_score')}}
        <small>{{trans('adminlabels.contest')}}</small>
        <!-- <div class="pull-right">
            <a href="{{ url('admin/add-contest') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div> -->       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.contest_score_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listContest" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.contest_name')}}</th>
                                <th>{{trans('adminlabels.score')}}</th>
                                <th>{{trans('adminlabels.contest_list_start_time_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_end_time_label')}}</th>
                                <!-- <th>{{trans('adminlabels.contest_list_image_label')}}</th> -->
                                <th>{{trans('adminlabels.contest_list_action_label')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>

        </div>
        <!--/.col (right) -->
    </div>
    
</section>
@endsection

@section('script')
<script>
    //process for listing
    var getContestList = function(ajaxParams) {
        $("#listContest").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-contest-score-ajax') }}",
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
                { "data": "score_sum" },
                { "data": "contest_start_time" },
                { "data": "contest_end_time"},
                // { "data": "image"},
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
    //for cancel contest
    $(document).ready(function () {
        var ajaxParams = {};
        getContestList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-cancel-contest', function(e){
            e.preventDefault();
            var contestId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Cancel this Contest ?';
            var ctitle = 'Cancel Contest';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'cancel';
            ajaxParams.id = [contestId];

            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getContestList(ajaxParams);
                        }
                    },
                    No: {
                        label: 'No',
                        className: 'btn btn-default'
                    }
                }
            });
        });
        //complete the contest
        var ajaxParams = {};
        getContestList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-complete-contest', function(e){
            e.preventDefault();
            var contestId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Complete this Contest ?';
            var ctitle = 'Complete Contest';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'complete';
            ajaxParams.id = contestId;

            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getContestList(ajaxParams);
                        }
                    },
                    No: {
                        label: 'No',
                        className: 'btn btn-default'
                    }
                }
            });
        });
        //requet for Re-upload images
        var ajaxParams = {};
        getContestList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-send-request', function(e){
            e.preventDefault();
            var createdBy = $(this).attr('data-id');

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'sendEmail';
            ajaxParams.id = createdBy;

            bootbox.prompt({
                title: "Request For Re-upload Image",
                inputType: 'textarea',
                callback: function (result) {
                    // console.log(result);
                    if (result != null) {
                        ajaxParams.requestData = result;
                        console.log(ajaxParams);
                        success(ajaxParams);
                    }
                }
            });
        });
        function success(ajaxParams) 
        {
            console.log(ajaxParams);
            getContestList(ajaxParams);
        }
        //View contest images
        var ajaxParams = {};
        getContestList(ajaxParams);
        $(document).on('click', '.btn-contest-view', function(e){
            e.preventDefault();
            var contestId = $(this).attr('data-id');
            $.ajax({
            type: "GET",
            url: "{{ url('admin/get-contest-images') }}",
            data: {'contestId': contestId},
            success: function(data){
                var data = JSON.parse(data);
                console.log(data['data']);
                if( data['data'].length > 0 ){
                    $("#imageModal").modal('show');
                    $("#imageAdd").html(' ');
                    for (var i = 0; i < data['data'].length; i++) {
                        var buttonText = (data['data'][i]['status'] == '2')?'Rejected':'Reject';
                        var disabled = (data['data'][i]['status'] == '2')?'disabled':'';
                        $("#imageAdd").append('<div class="img_main"><a class="thumbnail_cst"><img src=" '+ data.originalPath + '/' + data['data'][i]['contest_image'] +'"></a><button id="delete_button_'+data['data'][i]['id']+'" type="button" data-id="' + data['data'][i]['id'] +'" role="button" class="btn btn-danger delete_img" '+disabled+'>'+buttonText+'</button></div>');
                        }
                    }
                }
        });
        // for reject(delete) images
        $('#imageAdd').off('click').on('click', '.delete_img', function(e) 
        {
            var imgId = $(this).attr('data-id');
            console.log(imgId);
            var cmessage = 'Are you sure you want to Reject this Image ?';
            var ctitle = 'Reject Image';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'delete';
            ajaxParams.id = imgId;
            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getContestList(ajaxParams);
                            console.log('delete_button_'+imgId);
                            $('#delete_button_'+imgId).attr('disabled','disabled');
                            $('#delete_button_'+imgId).html('Rejected');
                            $('#delete_button_'+imgId).trigger("change");
                            //$('#imageModal').modal('hide');
                            //$(this).parent('.img_main').find('.thumbnail_cst img').attr('src','../../images/no_image_available.jpeg');
                        }

                    },
                    No: {
                        label: 'No',
                        className: 'btn btn-default'
                    }
                }
            });

            // $(this).parent('.img_main').find('.thumbnail_cst img').remove();
            // $(this).parent('.img_main').find('.thumbnail_cst').append('<img src="../../images/no_image_available.jpeg"/>');
            /*$(this).remove();*/
        });

        $(function()
        {
            var thumbnail =  $('.thumbnail_cst');
            var container = $('.viewer-body');
            var close = $('.close');
            var next = $('.cst_next');
            var prev = $('.prev');
            var delimg = $('.delete_img');

            $(document).on('click', '.thumbnail_cst', function(e) 
            {
                var content = $(this).html();
                thumbnail.removeClass('open');
                $(this).addClass('open');

                $('body').addClass('view-open');
                container.html(content);
            });

            $(document).on('click', '.cst_next', function(e) 
            {
                var total = $('.thumbnail_cst').length;
                
                if ($('.open').index() === total - 1)
                {
                    $('.thumbnail:last-child').addClass('open');
                }
                else
                {
                    $('.open').removeClass('open').parent().next().find(".thumbnail_cst").toggleClass('open');
                }

                var content = $('.open').html();
                container.html(content);
            });
            $(document).on('click', '.prev', function(e) 
            {
                if ($('.open').index() == 0)
                {
                    $('.thumbnail_cst:first-child').addClass('open');
                }
                else
                {

                    $('.open').removeClass('open').parent().prev().find(".thumbnail_cst").toggleClass('open');

                }
                var content = $('.open').html();
                container.html(content);
            });
            close.click(function() {$('body').removeClass('view-open');
        });

        
        });
    });

     
});
</script>
@endsection