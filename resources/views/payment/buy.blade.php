@extends('app')

@section('title', Lang::get('messages.buy_credit') . ' - ')

@section('content')
    <link href="{{ asset('/css/iCheck/square/green.css') }}" rel="stylesheet">
    <script type="text/javascript" src="{{ asset('/js/icheck.min.js') }}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.buy_credit')</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-6">
                                    <div class="panel with-nav-tabs panel-success">
                                        <div class="panel-heading">
                                            <ul class="nav nav-tabs">
                                                <li{{ isset($post) ? '' : ' class=active'}}><a href="#plans" onclick="return 0;" style="cursor: default"><span class="badge">1</span> Plans</a></li>
                                                <li><a id="t2" href="#confirm" onclick="return 0;" style="cursor: default"><span class="badge">2</span> Confirm</a></li>
                                                <li{{ isset($post) ? ' class=active' : ''}}><a id="t3" href="#buy" onclick="return 0;" style="cursor: default"><span class="badge">3</span> Buy</a></li>
                                            </ul>
                                        </div>
                                        <div class="panel-body">
                                            <div class="tab-content">
                                                <div class="tab-pane fade in{{ isset($post) ? '' : ' active'}}" id="plans">
                                                    <form class="form-horizontal" id="buy-form">
                                                        <fieldset>
                                                            <div class="form-group">
                                                                <label class="col-lg-3 hidden-md control-label" for="plans">Select a plan:</label>
                                                                <div class="col-lg-8 col-lg-offset-0 col-md-offset-1 col-md-10">

                                                                    <div class="radio space">
                                                                        <label for="plans-0" style="width: 100%">
                                                                            <div id="5gb" class="well well-sm" style="width: 100%; padding: 10px 100px 10px 10px">
                                                                                <input type="radio" name="plans" id="plans-0" value="5g">&nbsp;&nbsp; <strong>5</strong> Gigabytes
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                    <div class="radio space">
                                                                        <label for="plans-1" style="width: 100%">
                                                                            <div id="10gb" class="well well-sm" style="width: 100%; padding: 10px 100px 10px 10px">
                                                                                <input type="radio" name="plans" id="plans-1" value="10g" checked="checked">&nbsp;&nbsp; <strong>10</strong> Gigabytes
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                    <div class="radio space">
                                                                        <label for="plans-2" style="width: 100%">
                                                                            <div id="20gb" class="well well-sm" style="width: 100%; padding: 10px 100px 10px 10px">
                                                                                <input type="radio" name="plans" id="plans-2" value="20g">&nbsp;&nbsp; <strong>20</strong> Gigabytes
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                    <div class="radio space">
                                                                        <label for="plans-3" style="width: 100%">
                                                                            <div id="50gb" class="well well-sm" style="width: 100%; padding: 10px 100px 10px 10px">
                                                                                <input type="radio" name="plans" id="plans-3" value="50g">&nbsp;&nbsp; <strong>50</strong> Gigabytes
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                    @if (Config::get('leech.payment_type') == 'discount')
                                                                        <div class="radio space">
                                                                            <label for="plans-4" style="width: 100%">
                                                                                <div id="100gb" class="well well-sm" style="width: 100%; padding: 10px 100px 10px 10px">
                                                                                    <input type="radio" name="plans" id="plans-4" value="100g">&nbsp;&nbsp; <strong>100</strong> Gigabytes
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    @else
                                                                        <div class="radio">
                                                                            <label for="plans-4" style="width: 100%">
                                                                                <div id="cgb" class="well well-sm" style="width: 100%; padding: 10px 100px 10px 10px">
                                                                                    <input type="radio" name="plans" id="plans-4" value="cg">&nbsp;&nbsp; <span id="err"><input id="plans-4t" style="width: 80px!important;" name="plans" type="number" placeholder="80" value="80" class="form-control input-sm form-control-inline" min="5" max="100"></span> Gigabytes
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    @endif
                                                                    <hr style="padding: 0!important;" />
                                                                    <div id="total-panel" class="alert alert-info"  style="text-align: center; padding: 15px 5px 5px 5px!important;">
                                                                        <h4><strong id="total">{{number_format(Config::get('leech.payment_type') == 'normal' ? Config::get('leech.credit_unit') * 10 : Config::get('leech.10GB_price'))}}</strong> {{ Config::get('leech.currency') }}</h4>
                                                                    </div>
                                                                    <div id="err-panel" class="alert alert-danger" style="text-align: center; padding: 5px!important;">
                                                                        <h6></h6>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Button -->
                                                            <div class="form-group">
                                                                <div class="col-md-offset-8 col-md-3">
                                                                    <button id="submit_buyid" style="width: 100%" name="submit_buy" class="btn btn-success">Next <span class="fa fa-chevron-right" style=" vertical-align: middle; padding-left: 10px"></span></button>
                                                                </div>
                                                            </div>

                                                        </fieldset>
                                                    </form>
                                                </div>
                                                <div class="tab-pane fade" id="confirm">
                                                    <form class="form-horizontal" method="POST" action="{{ Config::get('leech.bank_url') }}">
                                                        <fieldset>
                                                            <input id="refid" hidden name="RefId" value="25">
                                                            <legend>Confirm</legend>

                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <span class="pull-right">Order ID :</span>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong><kbd id="o_id"></kbd></strong>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <span class="pull-right">Username :</span>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>{{ Auth::user()->username }}</strong>
                                                                </div>
                                                            </div>
                                                            <br />
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <span class="pull-right">Amount :</span>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong id="t_amount"></strong>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <span class="pull-right">Credits :</span>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong id="t_credits"></strong>
                                                                </div>
                                                            </div>
                                                            <br />
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <span class="pull-right">Bank :</span>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <img style="max-height: 100px; display: block; margin-left: auto; margin-right: auto"
                                                                         class="img-responsive img-thumbnail pull-left" src="{{ asset(Config::get('leech.bank_logo')) }}">
                                                                </div>
                                                            </div>

                                                            <hr />
                                                            <div class="form-group">
                                                                <div class="col-md-12">
                                                                    <button id="buy_tab2" class="btn btn-success pull-right">Buy <span class="fa fa-chevron-right" style=" vertical-align: middle; padding-left: 10px"></span></button>
                                                                </div>
                                                            </div>

                                                        </fieldset>
                                                    </form>

                                                </div>
                                                <div class="tab-pane fade in{{ isset($post) ? ' active' : ''}}" id="buy">
                                                    @if (isset($res) && $res == 'error')
                                                    <div class="alert alert-danger">
                                                        <strong>{{ Lang::get('messages.wops' )}}</strong> {{ Lang::get('messages.error.ocu' )}}<br><br>
                                                        <ul>
                                                                <li>{{ $error }}</li>
                                                        </ul>
                                                    </div>
                                                    @elseif (isset($res) && $res == 'success')
                                                        <div class="alert alert-success">
                                                            <strong>{{ Lang::get('messages.yaay' )}}</strong> {{ Lang::get('messages.buy.suc' )}}
                                                        </div>
                                                        @if (isset($info))
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <span class="pull-right">Username :</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>{{ $info['username'] }}</strong>
                                                            </div>
                                                        </div><br />
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <span class="pull-right">Old Credit :</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>{{ $main->formatBytes($info['old.credit'], 1) }}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <span class="pull-right">New Credit :</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>{{ $main->formatBytes($info['new.credit'], 1) }}</strong>
                                                            </div>
                                                        </div><br />
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <span class="pull-right">Reference ID :</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <kbd>{{ $info['refID'] }}</kbd>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <span class="pull-right">Card Number :</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <kbd>{{ $info['card'] }}</kbd>
                                                            </div>
                                                        </div>
                                                            <div class="row">
                                                            <div class="col-md-3">
                                                                <span class="pull-right">Time :</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <kbd>{{ date('d/m/Y H:i', time()) }}</kbd>
                                                            </div>
                                                        </div>
                                                            <script>
                                                                $('.user-credit').text('{{ $main->formatBytes($info['new.credit'], 1) }}');
                                                            </script>
                                                        @endif
                                                    @else
                                                        <div class="alert alert-danger">
                                                            <strong>{{ Lang::get('messages.wops' )}}</strong> Unknown error occurred.
                                                        </div>
                                                    @endif
                                                    <hr />
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <a href="{{ asset('') }}" class="btn btn-success pull-right"><i class="fa fa-home"></i> Home</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <script>
                                                    function commafy( num ) {
                                                        return num.toString().split( /(?=(?:\d{3})+(?:\.|$))/g ).join( "," );
                                                    }
                                                    $(document).ready(function(){
                                                        var clk = 1;
                                                        var dis = 0;
                                                        var amount = 10;
                                                        var unit = {{ Config::get('leech.credit_unit','0') }};
                                                        var type = '{{ Config::get('leech.paymenxt_type') }}';
                                                        var p5 = {{ Config::get('leech.5GB_price',0) }};
                                                        var p10 = {{ Config::get('leech.10GB_price',0) }};
                                                        var p20 = {{ Config::get('leech.20GB_price',0) }};
                                                        var p50 = {{ Config::get('leech.50GB_price',0) }};
                                                        var p100 = {{ Config::get('leech.100GB_price',0) }};

                                                        var oldtext = 'Next <span class="fa fa-chevron-right" style=" vertical-align: middle; padding-left: 10px"></span>';

                                                        function ck(){
                                                            var ret = 1;
                                                            var ep = $('#err-panel');
                                                            var v = $('#plans-4t').val();
                                                            if (v < 5 || v > 100) {
                                                                amount = -1;
                                                                $('#err').attr('class', 'has-error');
                                                                $('#total-panel').fadeOut(100, function() {
                                                                    //ep.attr('class', 'alert alert-danger')
                                                                    $('#err-panel h6').text(v < 5 ? 'Be cool! You need more than 5 GB tho.' : 'Woha! Do you really need this?!');
                                                                    ep.fadeIn(100);
                                                                    ret = 0;
                                                                });
                                                            }
                                                            else{
                                                                amount = v;
                                                                $('#total').fadeOut(100, function() {
                                                                    $(this).text(v * unit).fadeIn(100);
                                                                });
                                                                ep.hide();
                                                                $('#total-panel').show();
                                                                $('#err').attr('class', 'has-success');
                                                            }
                                                            return ret;
                                                        }

                                                        $('input').iCheck({
                                                            checkboxClass: 'icheckbox_square-green',
                                                            radioClass: 'iradio_square-green'
                                                        });

                                                        $('#submit_buyid').click(function(e){
                                                            e.preventDefault();
                                                            if (clk) {
                                                                var v = $('#plans-4t').val();
                                                                if (v < 5 || v > 100) ck();
                                                                else {
                                                                    $(this).html('<i class="fa fa-spinner fa-pulse"></i> Wait...');
                                                                    $('#buy-form :input').prop('disabled', true);
                                                                    dis = 1;

                                                                    $.ajax({
                                                                        url: "",
                                                                        type: "post",
                                                                        data: "amount=" + amount,

                                                                        success: function (response) {
                                                                            if (response.r == 'e') {
                                                                                toastr["error"]("Invalid input. Please choose your plan again.", "Oh Snap!");
                                                                                $('#buy-form :input').prop('disabled', false);
                                                                                $('#submit_buyid').html(oldtext);
                                                                                clk = 1;
                                                                            } else if (response.r = 's') {
                                                                                $('#refid').attr('value', response.RefId);
                                                                                $('#o_id').text(response.o_id);
                                                                                $('#t_amount').text(response.t_amount);
                                                                                $('#t_credits').text(response.t_credits);
                                                                                $('#t2').tab('show');
                                                                            } else {
                                                                                toastr["error"]("Unknown error occured. Please refresh the page.", "Oh Snap!");
                                                                                $('#buy-form :input').prop('disabled', false);
                                                                                $('#submit_buyid').html(oldtext);
                                                                                clk = 1;
                                                                            }
                                                                        },

                                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                                            toastr["error"]("Cannot connect to the server. Please check your connection or refresh the page.", "Oh Snap!");
                                                                        }
                                                                    });
                                                                    clk = 0;
                                                                }

                                                            }

                                                            //
                                                        });

                                                        $('#5gb, #5gb *').click(function(e){
                                                            if (!dis)
                                                                $('#total').fadeOut(100, function() {
                                                                    $(this).text(commafy(type == 'normal' ? unit * 5 : p5)).fadeIn(100);
                                                                });
                                                            amount = 5;
                                                            $('#err-panel').hide();
                                                            $('#total-panel').show();
                                                        });

                                                        $('#10gb, #10gb *').click(function(e){
                                                            if (!dis)
                                                                $('#total').fadeOut(100, function() {
                                                                    $(this).text(commafy(type == 'normal' ? unit * 10 : p10)).fadeIn(100);
                                                                });
                                                            amount = 10;
                                                            $('#err-panel').hide();
                                                            $('#total-panel').show();
                                                        });

                                                        $('#20gb, #20gb *').click(function(e){
                                                            if (!dis)
                                                                $('#total').fadeOut(100, function() {
                                                                    $(this).text(commafy(type == 'normal' ? unit * 20 : p20)).fadeIn(100);
                                                                });
                                                            amount = 20;
                                                            $('#err-panel').hide();
                                                            $('#total-panel').show();
                                                        });

                                                        $('#50gb, #50gb *').click(function(e){
                                                            if (!dis)
                                                                $('#total').fadeOut(100, function() {
                                                                    $(this).text(commafy(type == 'normal' ? unit * 50 : p50)).fadeIn(100);
                                                                });
                                                            amount = 50;
                                                            $('#err-panel').hide();
                                                            $('#total-panel').show();
                                                        });

                                                        @if (Config::get('leech.payment_type') == 'discount')
                                                        $('#100gb, #100gb *').click(function(e){
                                                            if (!dis)
                                                                $('#total').fadeOut(100, function() {
                                                                    $(this).text(commafy(type == 'normal' ? unit * 100 : p100)).fadeIn(100);
                                                                });
                                                            amount = 100;
                                                            $('#err-panel').hide();
                                                            $('#total-panel').show();
                                                        });
                                                        @endif

                                                        $('#cgb, #cgb *').click(function(e){
                                                            var v = $('#plans-4t').val();
                                                            if (!dis && ck() && (v < 5 || v > 100)) {
                                                                $('#total').fadeOut(100, function () {
                                                                    $(this).text(commafy($('#plans-4t').val() * unit)).fadeIn(100);
                                                                });
                                                                amount = v;
                                                                $('#err-panel').hide();
                                                                $('#total-panel').show();
                                                            }
                                                        });
                                                        $('#err-panel').hide();
                                                        $('#total-panel').show();
                                                        $('#plans-4t').change(function(){
                                                            ck();
                                                        });
                                                    });
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection