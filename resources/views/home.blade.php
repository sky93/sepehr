@extends('app')

@section('content')
    <link href="{{ asset('/css/bootstrap-combobox.css') }}" rel="stylesheet">
    <script src="{{ asset('/js/bootstrap-combobox.js') }}"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">{{ Lang::get('messages.home') }}
                    @if (Auth::user()->role == 2)
                        <a style="padding:0 5px 0 5px; margin-bottom: 1px;" href="{{ url('/tools/status') }}" class="btn btn-sm btn-success pull-right"><i class="fa fa-area-chart"></i> @lang('messages.gband') </a><span class="pull-right">@lang('messages.quick_acc'): &nbsp;</span>
                    @endif
                </div>
                <div class="panel-body">
                    @if (Config::get('leech.main_show_message'))
                        <div class="alert alert-info" dir="{{Config::get('leech.main_dir')}}">
                            {{Config::get('leech.main_message')}}
                        </div><hr />
                    @endif
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>{{ Lang::get('messages.wops' )}}</strong> {{ Lang::get('messages.inputError' )}}<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8">
                            <div role="tabpanel">

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#single" aria-controls="single" role="tab" data-toggle="tab">Single Link</a></li>
                                    <li role="presentation"><a href="#multi" aria-controls="multi" role="tab" data-toggle="tab">Multiple Links</a></li>
                                    <li role="presentation"><a href="#torrent_tab" aria-controls="torrent_tab" role="tab" data-toggle="tab">Torrent</a></li>
                                    <li role="presentation"><a href="#check_tab" aria-controls="check_tab" role="tab" data-toggle="tab">Link Checker</a></li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade in active" id="single">
                                        <form id="frm_single" class="form-horizontal" method="POST" action="" novalidate="">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <fieldset>
                                                {{--<legend>{{ Lang::get('messages.home.title') }}</legend>--}}
                                                <br/><br/>
                                                <script type="text/javascript">
                                                    $(document).ready(function () {
                                                        $('#http_auth').click(function () {
                                                            $("#HTTP").slideToggle(150);
                                                            $('#http_password').prop('required', true);
                                                            $('#http_username').prop('required', true);
                                                        });
                                                    });
                                                </script>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label"
                                                           for="link">{{ Lang::get('messages.link.to.transload') }}</label>
                                                    <div class="col-md-8 pull-left">
                                                        <input id="link" name="link" type="text" placeholder="{{ Lang::get('messages.your.link') }}" class="form-control input-md" required="">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="http_auth"></label>
                                                    <div class="col-md-4">
                                                        <div class="checkbox">
                                                            <label for="http_auth">
                                                                <input type="checkbox" name="http_auth" id="http_auth" value="1">
                                                                {{ Lang::get('messages.http.authorization') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="HTTP" style="display: none;">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="http_username">{{ Lang::get('messages.http.username') }}</label>
                                                        <div class="col-md-3">
                                                            <input id="http_username" name="http_username" type="text" placeholder="{{ Lang::get('messages.http.username') }}" class="form-control input-md" required="">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="http_password">{{ Lang::get('messages.http.password') }}</label>
                                                        <div class="col-md-3">
                                                            <input id="http_password" name="http_password" type="password" placeholder="{{ Lang::get('messages.http.password') }}" class="form-control input-md" required="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="comment">{{ Lang::get('messages.comment') }}</label>

                                                    <div class="col-md-5">
                                                        <textarea style="max-width: 386px; max-height: 200px; min-height: 70px" class="form-control" id="comment" name="comment" placeholder="{{ Lang::get('messages.desired.comment') }}"></textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="hold"></label>
                                                    <div class="col-md-3">
                                                        <div class="checkbox">
                                                            <label for="hold">
                                                                <input type="checkbox" name="hold" id="hold" value="1">
                                                                {{ Lang::get('messages.http.hold') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="transload"></label>
                                                    <div class="col-md-1">
                                                        <button id="transload" name="transload" class="btn btn-primary" data-loading-text="Loading..."><i class="fa fa-cloud-download"></i> {{ Lang::get('messages.transload') }}</button>
                                                    </div>
                                                </div>
                                                <script>
                                                    $('#transload').on('click', function () {
                                                        var $btn = $(this).button('loading');
                                                    })
                                                </script>
                                            </fieldset>
                                        </form>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="multi">
                                        <div id="linksres_div">
                                            <br /><br />
                                            <table class="users dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                                <thead>
                                                <tr class="warning">
                                                    <th style="width: 5%">@lang('messages.id')</th>
                                                    <th>@lang('messages.link')</th>
                                                    <th>@lang('messages.status')</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                            <div class="row">
                                                <div class="col-sm-offset-6 col-sm-3">
                                                    <a href="{{ asset('downloads') }}" style="width: 100%" class="btn btn-default"><i class="fa fa-tasks"></i> @lang('messages.download.list')</a>
                                                </div>
                                                <div class="col-sm-3">
                                                    <button id="addmore" style="width: 100%" class="btn btn-default"><i class="fa fa-plus-square-o"></i> @lang('messages.add.more')</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="links_div">
                                            <form id="frm_multi" class="form-horizontal" method="POST" action="" novalidate="">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <fieldset>
                                                {{--<legend>{{ Lang::get('messages.home.title') }}</legend>--}}
                                                <br/><br/>
                                                <script type="text/javascript">
                                                    $(document).ready(function () {
                                                        $('#http_auth_multi').click(function () {
                                                            $("#HTTP_multi").slideToggle(150);
                                                        });
                                                    });
                                                </script>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label"
                                                           for="link">{{ Lang::get('messages.links.to.transload') }}</label>
                                                    <div class="col-md-8">
                                                            <textarea style="max-width: 486px; max-height: 500px; min-height: 100px" class="form-control" id="links" name="links" wrap="off" placeholder="{{ Lang::get('messages.links') }}"></textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label id="http_auth_multil" class="col-md-3 control-label" for="http_auth1"></label>
                                                    <div class="col-md-3">
                                                        <div class="checkbox">
                                                            <label for="http_auth">
                                                                <input type="checkbox" name="http_auth" id="http_auth_multi" value="1">
                                                                {{ Lang::get('messages.http.authorization') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="HTTP_multi" style="display: none;">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="http_username">{{ Lang::get('messages.http.username') }}</label>
                                                        <div class="col-md-3">
                                                            <input id="http_username" name="http_username" type="text" placeholder="{{ Lang::get('messages.http.username') }}" class="form-control input-md" required="">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="http_password">{{ Lang::get('messages.http.password') }}</label>
                                                        <div class="col-md-3">
                                                            <input id="http_password" name="http_password" type="password" placeholder="{{ Lang::get('messages.http.password') }}" class="form-control input-md" required="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="comment">{{ Lang::get('messages.comment') }}</label>
                                                    <div class="col-md-5">
                                                        <textarea style="max-width: 386px; max-height: 200px; min-height: 70px" class="form-control" id="comment" name="comment" placeholder="{{ Lang::get('messages.desired.comment') }}"></textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="hold"></label>
                                                    <div class="col-md-3">
                                                        <div class="checkbox">
                                                            <label for="hold">
                                                                <input type="checkbox" name="hold" id="hold" value="1">
                                                                {{ Lang::get('messages.http.hold') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="transload"></label>
                                                    <div class="col-md-1">
                                                        <button id="transload" name="transload" class="btn btn-primary" data-loading-text="Loading..."><i class="fa fa-cloud-download"></i> {{ Lang::get('messages.transload') }}</button>
                                                    </div>
                                                </div>
                                                <script>
                                                    $('#transload').on('click', function () {
                                                        var $btn = $(this).button('loading');
                                                    })
                                                </script>
                                            </fieldset>
                                        </form>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="torrent_tab">
                                        @if(Auth::user()->torrent != 1)
                                            <br/>
                                            <div class="alert alert-info" role="alert" style="text-align: center"><span style="font-weight: bold"><i class="fa fa-exclamation"></i> @lang('messages.notice'): </span>@lang('messages.torrent_disabled')</div>
                                        @endif
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="check_tab">
                                        <div id="checkres_div">
                                            <br /><br />
                                            <table class="users dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                                <tbody>
                                                </tbody>
                                            </table>
                                            <div class="row">
                                                <div class="col-sm-offset-9 col-sm-3">
                                                    <button id="checkmore" style="width: 100%" class="btn btn-default"><i class="fa fa-plus-square-o"></i> @lang('messages.check.more')</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="check_div">
                                            <h4><small>@lang('messages.check_link_desc')</small></h4>
                                            <form id="frm_check" class="form-horizontal" method="POST" action="" novalidate="">
                                                <input type="hidden" id="check_token" name="_token" value="{{ csrf_token() }}">
                                                <fieldset>
                                                    <br/>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="check_link">{{ Lang::get('messages.link.to.check') }}</label>
                                                        <div class="col-md-8 pull-left">
                                                            <input id="check_link" name="check_link" type="text" placeholder="{{ Lang::get('messages.your.link') }}" class="form-control input-md" required="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="check"></label>
                                                        <div class="col-md-1">
                                                            <button id="check" name="check" data-loading-text="Loading..." class="btn btn-primary"><i class="fa fa fa-check"></i> {{ Lang::get('messages.check') }}</button>
                                                        </div>
                                                    </div>
                                                    <script>
                                                        $('#check').on('click', function () {
                                                            var $btn = $(this).button('loading');
                                                        })
                                                    </script>
                                                </fieldset>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">
                            <a href="{{ asset('/buy') }}" data-toggle="tooltip" data-placement="left" title="@lang('messages.credit_buy')" class="pull-right btn btn-warning"><i class="fa fa-usd fa-lg"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            $("#check").click(function (event) {
                event.preventDefault();
                $('#linksres_div table tbody').empty();

                var data = 'type=check&_token=' + $('#check_token').val() + '&link=' +  encodeURIComponent( $('#check_link').val() );
                $.ajax({
                    url: "",
                    type: "POST",
                    data: data ,
                    dataType: 'json',

                    success: function (response) {
                        $('#l' + response.id).text(response.message);
                        if (response['type'] == 'success'){
                            $('#check_div').fadeOut(500);
                            $('#checkres_div').delay(500).fadeIn(500);
                            $('#l' + response.id).attr('class', 'success');
                            $('#checkres_div table tbody').append('<tr><td><strong>Location:</strong></td><td>' + response.location + '</td></tr>');
                            $('#checkres_div table tbody').append('<tr><td><strong>File Name:</strong></td><td>' + response.filename + '</td></tr>');
                            $('#checkres_div table tbody').append('<tr><td><strong>Extension:</strong></td><td>' + response.file_extension + '</td></tr>');
                            $('#checkres_div table tbody').append('<tr><td><strong>File Size:</strong></td><td>' + response.filesize + '</td></tr>');
                            $('#checkres_div table tbody').append('<tr><td><strong>Status:</strong></td><td>' + response.status + '</td></tr>');
                            var $btn = $('#check').html('<i class="fa fa fa-check"></i> {{ Lang::get('messages.check') }}');
                            $btn.button('reset');
                        }
                        else{
                            toastr["error"](response.message, "Oh Snap!");
                            var $btn = $('#check').html('<i class="fa fa fa-check"></i> {{ Lang::get('messages.check') }}');
                            $btn.button('reset');
                        }
                    },

                    error: function (jqXHR, textStatus, errorThrown) {
                        toastr["error"]("Cannot connect to the server. Please check your connection or refresh the page.", "Oh Snap!");
                        var $btn = $('#check').html('<i class="fa fa fa-check"></i> {{ Lang::get('messages.check') }}');
                        $btn.button('reset');
                    }
                });

            });

            $("#checkmore").click(function (event) {
                $('#frm_check')[0].reset();
                $('#checkres_div').fadeOut(500);
                $('#check_div').delay(500).fadeIn(500);

            });

            $("#addmore").click(function (event) {
                $('#frm_multi')[0].reset();
                $('#linksres_div').fadeOut(500);
                $('#links_div').delay(500).fadeIn(500);

            });

            $('#linksres_div').hide();
            $('#checkres_div').hide();

            $("#frm_multi").submit(function (event) {
                event.preventDefault();
                $('#links_div').fadeOut(500);
                $('#linksres_div').delay(500).fadeIn(500);
                var lines = $('textarea[name=links]').val().split('\n');
                var time = 500;
                var id = 0, id1=0;
                $('#linksres_div table tbody').empty();
                $.each(lines, function(){
                    var lline = this;
                    if (lline == '') return true;
                    id++;
                    $('#linksres_div table tbody').append('<tr><td>' + id + '</td><td>' + lline + '</td><td id="l' + id + '">' + 'Pending...' + '</td></tr>');
                    setTimeout( function(){
                        id1++;
                        var data = 'type=multi&' + $("#frm_multi :input[name!='links']").serialize() + '&id=' + id1 + '&link=' +  encodeURIComponent( lline );
                        $.ajax({
                            url: "",
                            type: "POST",
                            data: data ,
                            dataType: 'json',

                            success: function (response) {
                                $('#l' + response.id).text(response.message);
                                if (response['type'] == 'success')
                                    $('#l' + response.id).attr('class', 'success');
                                else
                                    $('#l' + response.id).attr('class', 'danger');
                            },

                            error: function (jqXHR, textStatus, errorThrown) {
                                toastr["error"]("Cannot connect to the server. Please check your connection or refresh the page.", "Oh Snap!");
                            }
                        });
                    }, time);
                    time += 500;
                });

            });
        });
    </script>

@endsection
