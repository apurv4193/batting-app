@extends('layouts.admin-master')
<style>
    /* .squaredFour */
    .squaredFour {
        width: 20px;
        position: relative;
        margin: 20px auto;
        label {
            width: 20px;
            height: 20px;
            cursor: pointer;
            position: absolute;
            top: 0;
            left: 0;
            background: #fcfff4;
            background: linear-gradient(top, #fcfff4 0%, #dfe5d7 40%, #b3bead 100%);
            border-radius: 4px;
            box-shadow: inset 0px 1px 1px white, 0px 1px 3px rgba(0,0,0,0.5);
            &:after {
                content: '';
                width: 9px;
                height: 5px;
                position: absolute;
                top: 4px;
                left: 4px;
                border: 3px solid #333;
                border-top: none;
                border-right: none;
                background: transparent;
                opacity: 0;
                transform: rotate(-45deg);
            }
            &:hover::after {
                opacity: 0.5;
            }
        }
        input[type=checkbox] {
            visibility: hidden;
            &:checked + label:after {
                opacity: 1;
            }
        }    
    }
    /* end .squaredFour */
</style>
@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.Prize_management')}}
        <small><?php echo (isset($editPrizeDistributionPlan) && !empty($editPrizeDistributionPlan)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.Prize') }}</small>        
    </h1>     
</section>
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($editPrizeDistributionPlan) && !empty($editPrizeDistributionPlan)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.Prize') }}</h3>
                </div>
                <form class="form-horizontal" id="addPrizeDistribution" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-prize') }}">
                    {{ csrf_field() }} 
                    <div class="box-body">
                        <input type="hidden" name="id" value="<?php echo (isset($editPrizeDistributionPlan) && !empty($editPrizeDistributionPlan)) ? $editPrizeDistributionPlan->id : '0'; ?>" >                                                
                        <?php $prizeName = (old('name') ? old('name') : (isset($editPrizeDistributionPlan) ? $editPrizeDistributionPlan->name : '')); ?>
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">{{ trans('adminlabels.Prize_name') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="name" id="name" placeholder="{{ trans('adminlabels.Prize_name') }}" value="{{$prizeName}}" />                        
                                @if ($errors->has('name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>   
                        <?php if (!isset($editPrizeDistributionPlan) && empty($editPrizeDistributionPlan)) { ?>
                            <div class="form-group">
                                <label for="winner" class="col-md-2 control-label">{{ trans('adminlabels.Prize_winner') }}</label>
                                <div class="col-md-3 ">
                                    <input type="number" class="form-control"  name="winner" id="winner" value="" placeholder="Enter numbers only"/>
                                    <span id="winmsg" style="color: red;text-transform: capitalize;font-weight: 600;">Please Enter only numeric values!...</span>
                                    @if ($errors->has('winner'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('winner') }}</strong>
                                    </span>
                                    @endif
                                </div>                            
                                <div class="col-md-3 ">
                                    <div class="squaredThree">
                                        <input type="checkbox" id="squaredThree" name="check" />
                                        <label for="squaredThree">All</label>
                                    </div> 
                                </div>
                            </div>
                        <?php } ?>
                        <div id="winner_prize">
                            
                            <!-- <div class="col-md-6"> -->
                                <?php
                                if (isset($editPrizeDistributionPlan->id) && !empty($editPrizeDistributionPlan->id)) {
                                   
                                    foreach ($editPrizeDistributionRatio as $key => $ratioValue) {
                                        echo '<div class="form-group"><label for="winner_prize" id="win" class="col-md-2 control-label">% for winner '.($key+1).'</label><div class="col-md-6"><input type="text" class="form-control"  id="distribution" name="prize_winner[]" value="' . $ratioValue->ratio . '"  /></div></div>';
                                    }
                                }
                                ?>
                            <!-- </div> -->
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/prize_distribution')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
<script type="text/javascript">
    $(document).ready(function () {
        //$('#winner_prize').hide();
        $('#winmsg').hide();        
    });
    $("#winner").on('keyup blur change', function () {
        $("#winner_prize").html("");
        $('#winner_prize').show();
        var winnerGet = $(this).val();
        var numbers = /^[0-9]+$/;

        if (winnerGet != '') {
            if (winnerGet.match(numbers) && $.isNumeric(winnerGet)) {
                if (winnerGet > 1) {
                    $('#win').show();
                    $('#winmsg').hide();
                    $('#winner_prize').append('<div class="form-group"><label for="winner_prize" id="win" class="col-md-2 control-label"></label><div class="col-md-6" style="color: red;"><label>Sum of % must be 100. </label></div>');
                    for (var i = 1; i <= winnerGet; i++) {
                        $("#winner_prize").append('<div class="form-group"><label for="winner_prize" id="win" class="col-md-2 control-label">% for winner '+i+'</label><div class="col-md-6"><input type="text" class="form-control"  id="distribution' + i + '" name="prize_winner[]" value=""  placeholder="Please enter % for winner"/></div></div');
                    }
                } else {
                    $('#winmsg').hide();
                    $('#winner_prize').hide();
                }
            } else {
                $('#winmsg').show();
            }
        } else {
            if (!$.isNumeric(winnerGet) && winnerGet != 'NULL') {
                $('#win').hide();
            } else {
                $('#winmsg').hide();
            }
        }
    });

    $('#squaredThree').on('change', function () {
        if ($(this).is(':checked')) {
            $('#winner_prize').hide();
            $('#winner').val(0);
            $("#winner_prize").html("");
            $('#winner').attr('disabled', true);
        } else {
            $('#winner_prize').show();
            $('#win').hide();
            $('#winner').attr('disabled', false);
        }
    });
</script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var id = $("input[name=id]").val();
        if (id == 0) {
            console.log("if");
            $('#winner_prize').hide();
            $("#addPrizeDistribution").validate({
                rules: {
                    name: {
                        required: true
                    },
                    winner: {
                        required: true
                    },
                    'prize_winner[]': {
                        required: true,
                        range: [1, 100]
                    },
                }
            });
        } else {
            $('#winner_prize').show();
            $("#addPrizeDistribution").validate({
                rules: {
                    name: {
                        required: true
                    },
                    winner: {
                        required: true
                    },
                    'prize_winner[]': {
                        required: true,
                        range: [1, 100]
                    },
                }
            });
        }
    });
</script>
@endsection