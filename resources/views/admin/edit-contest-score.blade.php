@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.contest_score')}}
        <small>{{trans('adminlabels.contest')}}</small>
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{trans('adminlabels.edit') }} {{trans('adminlabels.contest_score_list')}}</h3>
                </div>
                <div class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="box-body">

                        <div class="form-group">

                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.contest_name') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('adminlabels.name') }}" value="{{$contest_user->contest_name}}" disabled="">
                            </div>
                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.game_name') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="game_name" name="game_name" placeholder="{{ trans('adminlabels.game_name') }}" value="{{$contest_user->name}}" disabled="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.contest_type') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="contest_type" name="contest_type" placeholder="{{ trans('adminlabels.contest_type') }}" value="{{$contest_user->type}}" disabled="">
                            </div>

                            <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.contest_fees') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="contest_fees" name="contest_fees" placeholder="{{ trans('adminlabels.contest_fees') }}" value="{{$contest_user->contest_fees}}" disabled="">
                            </div>
                        </div> 
                        <div class="box-footer">
                            <div class="form-group">
                                <div class="col-md-4 col-md-offset-2">
                                    <a href="" data-id="{{$contest->id}}" class="btn btn-primary btn-contest-view" title='View Images'><span class='glyphicon glyphicon-picture'></span> {{ trans('adminlabels.contest_score_image') }}</a>
                                </div>
                                <div class="col-md-4">
                                    <a href="javascript:;" data-id="{{$contest->id}}" class="btn btn-primary btn-complete-contest"  title='Complete Contest'><span class='glyphicon glyphicon-ok'></span> {{ trans('adminlabels.complete_contest') }}</a>
                                </div>
                                <!-- <div class="col-md-3">
                                    <a href='' data-id = "{{$contest->id}}" class="btn btn-primary btn-send-request" title='Unapproved Image'><span class='glyphicon glyphicon-remove'></span> {{ trans('adminlabels.reupload') }}</a>
                                </div> -->
                            </div>
                        </div>                         
                    </div>

                </div>
            </div>
            <?php if (sizeof($contest_user_data) > 0) { ?>
                <div class="box box-info">
                    <div class="box-body">
                        <form class="form-horizontal" id="editScore" method="POST" action="{{ url('admin/save-contest-score') }}">
                            {{ csrf_field() }}
                            <?php foreach ($contest_user_data as $key => $value) { ?>
                                <div class="form-group">
                                    <input type="hidden" name="player_id[]" value="{{$value->player_id}}">
                                    <input type="hidden" name="contest_id" value="{{$contest->id}}" id="contest_id">
                                    <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.players_name') }}</label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="player_name" name="player_name[]" placeholder="{{ trans('adminlabels.players_name') }}" value="{{$value->name}}" disabled="">
                                    </div>
                                    <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.contest_score_label') }}</label>
                                    <div class="col-sm-2">
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="score" name="score[{{$value->player_id}}]" placeholder="{{ trans('adminlabels.contest_score_label') }}" value="{{$value->score}}">
                                    </div>
                                    <!-- <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.win') }}</label>
                                    <div class="col-sm-2">
                                        <input type="checkbox" id="is_win" name="is_win[{{$value->player_id}}]" value="1" />
                                        <label for="squaredThree">Yes</label>
                                    </div> -->
                                </div>
                            <?php } ?>

                            <div class="box-footer">
                                <div class="form-group">
                                    <div class="col-md-1 col-md-offset-2">
                                        <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{url('admin/contest_score')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
<?php } ?>
            <!-- /.box -->
        </div>
        <!--/.col (right) -->
    </div>
    <!-- /.row -->
    <div class="cst_modal">
        <div id="imageModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">

                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h5>Contest Score Images</h5>
                    </div>
                    <div class="modal-body" id="imageAdd">
                        <!-- image galleary -->

                        <!-- end -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary delete_img" disabled>Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="thumb-viewer">
        <a class="cst_prev"></a>
        <a class="cst_next"></a>
        <a class="close_modal">x</a>
        <div class="viewer-body"></div>
    </div>
</section>
<!-- /.content -->
<!-- /.content-wrapper -->
@endsection

@section('script')
<script>
    jQuery(document).ready(function () {

        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addGames").validate({
                ignore: ":hidden:not(select)",
                rules: {
                    "name[0]": {
                        required: true,
                        maxlength: 100
                    }
                }
            });
        } else {
            $("#addGames").validate({
                ignore: ":hidden:not(select)",
                rules: {
                    name: {
                        required: true,
                        maxlength: 100
                    }
                }
            });
        }

// Add game row
        $(document).on("click", ".add-game-row", function (e) {
            var counter = parseInt($("#counter").val()) + 1;
            var fieldHTML = "<div class='form-group'>" +
                    "<label for='title' class='col-md-2 control-label'>{{trans('adminlabels.name')}}</label>" +
                    "<div class='col-sm-6'>" +
                    "<input type='text' class='form-control' id='name[" + counter + "]' name='name[" + counter + "]' placeholder='{{trans('adminlabels.name')}}' value=''>" +
                    "</div>" +
                    "<input type='checkbox'  name='txt_check[]' />" +
                    "</div>";
            $("#div_game_row").append(fieldHTML);
            $('input[name="name[' + counter + ']"]').rules("add", {// <- apply rule to new field
                required: true,
                maxlength: 100
            });
            $("#counter").val(counter);
        });

// Delete game row
        $(document).on("click", ".delete-game-row", function (e) {
            $('DIV.form-group').each(function (index, item) {
                jQuery(':checkbox', this).each(function () {
                    if ($(this).is(':checked')) {
                        $(item).remove();
                    }
                });
            });
        });
    });

    //model open
    $(function ()
    {
        var thumbnail = $('.thumbnail_cst');
        var container = $('.viewer-body');
        var close = $('.close_modal');
        var next = $('.cst_next');
        var prev = $('.cst_prev');
        var delimg = $('.delete_img');

        $(document).on('click', '.thumbnail_cst', function (e)
        {
            window.scrollbars.visible = true;
            var content = $(this).html();
            thumbnail.removeClass('open');
            $(this).addClass('open');
            $('body').addClass('view-open');
            container.html(content);
            console.log("index",$('.open').data("index"))
        });

        $(document).on('click', '.cst_next', function (e)
        {
            var total = parseInt($('#imageAdd .img_main').length) - parseInt(1);
            if ($('.open').data("index") == parseInt(total)) {
                var index = parseInt(total);
                $(".thumbnail_cst[data-index=" + index + "]").addClass('open');
            } else {
                var index = parseInt($('.open').data("index"));
                $('.open').removeClass('open');
                $(".thumbnail_cst[data-index=" + index + "]").addClass('open');
                if(!$('.open').data("index")) {
                     $(".thumbnail_cst[data-index=" + total + "]").addClass('open');
                }
            }

            var content = $('.open').html();
            container.html(content);
        });
    $(document).on('click', '.cst_prev', function (e)
    {
        if ($('.open').data("index") == 0) {
            var index = parseInt($('.open').data("index"));
            $(".thumbnail_cst[data-index=0]").addClass('open');
        } else {
            var index = parseInt($('.open').data("index")) - 1;
            $('.open').removeClass('open');
            $(".thumbnail_cst[data-index=" + index + "]").addClass('open');
        }
        var content = $('.open').html();
        container.html(content);

    });

    close.click(function () {
        $('body').removeClass('view-open');
    });
    });
            //end
//process for listing
            var performAction = function (ajaxParams) {
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/perform-custom-action-ajax') }}",
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }} "
                    },
                    data: {'ajaxParams': ajaxParams},
                    success: function (data) {
                        if (data['customMessage'] == "cancel" || data['customMessage'] == "complete" || data['customMessage'] == "sendEmail") {
                            window.location.href = "{{url('admin/contest_score')}}";
                        } else {
                            if (data['customMessage'] == "errors") {
                                window.location.href = "{{url('admin/contest_score')}}";
                            }
                        }
                    }
                });
            };
//end 
//View contest images
    var ajaxParams = {};
    // getContestList(ajaxParams);
    $(document).on('click', '.btn-contest-view', function (e) {
        e.preventDefault();
        var contestId = $(this).attr('data-id');
        console.log(contestId);
        $.ajax({
            type: "GET",
            url: "{{ url('admin/get-contest-images') }}",
            data: {'contestId': contestId},
            success: function (data) {
                var data = JSON.parse(data);
                console.log(data);
                $("#imageAdd").html(' ');
                if( data['data'].length > 0 ) {
                    $('#imageModal').modal('show');
                    for (var i = 0; i < data['data'].length; i++) {

                        var check = (data['data'][i]['status'] == '2') ? '<p>Rejected</p>' : '<input type="checkbox" name="checkboxvar" id="delete_checkbox_' + data['data'][i]['id'] + '" value="' + data['data'][i]['id'] + '" class="img_class checkmark"  onclick="calc();">';

                        //var text = (data['data'][i]['status'] == '2') ? 'block' : 'none';

                        $("#imageAdd").append('<div class="img_main"><a class="thumbnail_cst" data-index=' + i + '><img src=" ' + data.thumbPath + '/' + data['data'][i]['contest_image'] + '"></a><div id="message_data_'+data['data'][i]['id']+'">'+check+'</div></div>');

                        // <button id="delete_button_'+data['data'][i]['id']+'" type="button" data-id="' + data['data'][i]['id'] +'" role="button" class="btn btn-danger delete_img" '+disabled+'>'+buttonText+'</button> value=""
                    }
                }
                else
                {
                   $('#imageModal').modal('show'); 
                   $("#imageAdd").append('<div class=""><h3>No image to display</h3></div>');
                }
            }
        });
    });

    function calc()
    {
        var imgArray = [];
        $("input[name=checkboxvar]:checked").each(function (n) {
            imgArray[n] = $(this).val();
        });
        console.log(imgArray.length);
        if (imgArray.length > 0) {
            $('.delete_img').attr('disabled', false);
        } else {
            $('.delete_img').attr('disabled', true);
        }
    }
    // for reject(delete) images
    $('.delete_img').on('click', function (e)
    {
        var imgArray = [];
        $("input[name=checkboxvar]:checked").each(function (n) {
            imgArray[n] = $(this).val();
        });
        console.log(imgArray);
        var cmessage = 'Are you sure you want to Reject this Image ?';
        var ctitle = 'Reject Image';

        ajaxParams.customActionType = 'groupAction';
        ajaxParams.customActionName = 'delete';
        ajaxParams.id = imgArray;
        ajaxParams.contest_id = "<?php echo $contest->id; ?>";
        bootbox.prompt({
            title: "Request For Re-upload Image",
            inputType: 'textarea',
            callback: function (result) {
                console.log(result);
                if (result === "") {
                    alert("This field is required")

                } else if (result !== null) {
                    console.log(result);
                    ajaxParams.requestData = result;
                    performAction(ajaxParams);
                    for (var i = 0; i < imgArray.length; i++) {
                        $('#message_data_' + imgArray[i]).html('<p>Rejected</p>');
                        //$('#text_' + imgArray[i]).css('display','block');
                        //$('#text_' + imgArray[i]).html('<p>Rejected</p>');
                    }
                }
            }
        });
    });

    //complete the contest
    var ajaxParams = {};
    // getContestList(ajaxParams);
    // Remove user
    $(document).on('click', '.btn-complete-contest', function (e) {
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
                        performAction(ajaxParams);
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
    // getContestList(ajaxParams);
    // Remove user
    $(document).on('click', '.btn-send-request', function (e) {
        e.preventDefault();
        var createdBy = $(this).attr('data-id');

        ajaxParams.customActionType = 'groupAction';
        ajaxParams.customActionName = 'sendEmail';
        ajaxParams.id = createdBy;

        bootbox.prompt({
            title: "Request For Re-upload Image",
            inputType: 'textarea',
            callback: function (result) {
                console.log(result);
                if (result != "" || result != null) {
                    ajaxParams.requestData = result;
                    performAction(ajaxParams);
                } else {
                    alert("This field is required")
                }
            }
        });
    });
    // function success(ajaxParams) 
    // {
    //     performAction(ajaxParams);
    // }
</script>
<style type="text/css">
    /* Hide the browser's default checkbox */
    .container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    /* Create a custom checkbox */
    .checkmark {
        position: absolute;
        /*top: 0;*/
        left: 50%;
        height: 25px;
        width: 25px;
        background-color: black;
        transform: translate(-50%,0);
    }
</style>
@endsection