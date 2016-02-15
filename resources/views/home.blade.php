@extends('app')

@section('content')
    <link href="{{ asset('/assets/bootstrap-combobox/css/bootstrap-combobox.css') }}" rel="stylesheet">
    <link href="{{ asset('/assets/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet">
    <script src="{{ asset('/assets/bootstrap-combobox/js/bootstrap-combobox.js') }}"></script>
    <script src="{{ asset('/assets/torrent.js?v2') }}"></script>
    <script src="{{ asset('/assets/jstree/dist/jstree.min.js') }}"></script>
    <script src="{{ asset('/assets/bootstrap-tabcollapse/bootstrap-tabcollapse.js') }}"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('#mainTabs').tabCollapse({
                tabsClass: 'hidden-xs',
                accordionClass: ' visible-xs'
            });
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
                                <ul id="mainTabs" class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#single" aria-controls="single" role="tab" data-toggle="tab">Single Link</a></li>
                                    <li role="presentation"><a href="#multi" aria-controls="multi" role="tab" data-toggle="tab">Multiple Links</a></li>
                                    <li role="presentation"><a href="#torrent_tab" aria-controls="torrent_tab" role="tab" data-toggle="tab">Torrent</a></li>
                                    <li role="presentation"><a href="#check_tab" aria-controls="check_tab" role="tab" data-toggle="tab">Link Checker</a></li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade in active" id="single">
                                        <script type="text/javascript">
                                            $(document).ready(function () {
                                                $('#advanced').click(function () {
                                                    $("#advanced_panel").slideToggle(100);
                                                });

                                                $('#link').change(function () {
                                                    $.ajax({
                                                        url: "",
                                                        type: "POST",
                                                        data: 'type=size&' + $("#frm_single").find('input[name!=comment]').serialize(),
                                                        dataType: 'json',

                                                        success: function (response) {
                                                            if (response['result'] == 'ok') {
                                                                var a = $('#link_size').html("<kbd><span style='font-weight: 700'>Size: </span>" + response['size'] + "</kbd><br /><kbd><span style='font-weight: 700'>Name: </span>" + response['name'] + "</kbd>");
                                                                a.slideDown(150);
                                                            } else {
                                                                $("#link_size").slideUp(150);
                                                            }
                                                        },
                                                        error: function () {
                                                            $("#link_size").slideUp(150);
                                                        }
                                                    });
                                                });
                                                $('#transload').on('click', function () {
                                                    var $btn = $(this).button('loading');
                                                });
                                                $('#advanced_multi_fetch').click(function () {
                                                    $("#advanced_panel_multi_fetch").slideToggle(150);
                                                });
                                                $('#advanced_multi').click(function () {
                                                    $("#advanced_panel_multi").slideToggle(150);
                                                });
                                                $('#transload').on('click', function () {
                                                    var $btn = $(this).button('loading');
                                                });
                                            });
                                        </script>
                                        <form id="frm_single" class="form-horizontal main-panel-padding" method="POST" action="" novalidate="">
                                            <input type="hidden" name="_token" id="m_token" value="{{ csrf_token() }}">
                                            <fieldset>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="link">{{ Lang::get('messages.link.to.transload') }}</label>
                                                    <div class="col-md-8">
                                                        <input id="link" name="link" type="text" placeholder="{{ Lang::get('messages.your.link') }}" class="form-control input-md courier_font" required="">
                                                        <span id="link_size" class="help-block"  style="width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; display: none"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="comment">{{ Lang::get('messages.comment') }}</label>
                                                    <div class="col-md-5">
                                                        <textarea style="max-width: 386px; max-height: 200px; min-height: 70px" class="form-control" id="comment" name="comment" placeholder="{{ Lang::get('messages.desired.comment') }}"></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-9 col-md-offset-3">
                                                        <div class="checkbox">
                                                            <label for="hold" class="checkbox-label">
                                                                <input type="checkbox" name="hold" id="hold" value="1">
                                                                {{ Lang::get('messages.http.hold') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="advanced"></label>
                                                    <div class="col-md-4">
                                                        <div class="checkbox">
                                                            <label for="advanced" class="checkbox-label">
                                                                <input type="checkbox" name="advanced" id="advanced" value="1">
                                                                {{ Lang::get('messages.advanced') }} <span class="label label-danger">NEW</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="advanced_panel" style="display: none">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="custom_user_agent">{{ Lang::get('messages.custom.user.agent') }}</label>
                                                        <div class="col-md-8">
                                                            <input id="custom_user_agent" name="custom_user_agent" type="text" placeholder="{{ env('APP_NAME', 'SEPEHR') }}/{{ env('VERSION', '2.0') }}" class="form-control input-md courier_font">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="custom_cookie">{{ Lang::get('messages.custom.cookie') }}</label>
                                                        <div class="col-md-8">
                                                            <input id="custom_cookie" name="custom_cookie" type="text" id="custom_cookie" placeholder="session=4245434341; token=7f2422a1d" class="form-control input-md courier_font">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="custom_headers">{{ Lang::get('messages.custom.header') }}</label>
                                                        <div class="col-md-6">
                                                            <textarea style="max-width: 386px; max-height: 300px; min-height: 70px" class="form-control courier_font" id="custom_headers" name="custom_headers" placeholder="header1: Details&#10;header2: Details"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="transload"></label>
                                                    <div class="col-md-1">
                                                        <button id="transload" name="transload" class="btn btn-primary" data-loading-text="Loading..."><i class="fa fa-cloud-download"></i> {{ Lang::get('messages.transload') }}</button>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </form>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="multi">
                                        <div id="linksres_div" class="main-panel-padding">
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
                                        <div id="links_div" class="main-panel-padding">
                                            <form id="frm_fetch" class="form-horizontal" method="POST" action="">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <fieldset>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="link">{{ Lang::get('messages.fetch') }}</label>
                                                        <div class="col-md-8">
                                                            <input id="fetch_link" name="link" type="text" placeholder="{{ Lang::get('messages.fetch.place.holder') }}" class="form-control input-md" required="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="fetch_filter">{{ Lang::get('messages.filter') }}</label>
                                                        <div class="col-md-5">
                                                            <input style="font-family: 'Courier New'" id="fetch_filter" name="fetch_filter" type="text" placeholder=".mp4" value=".mp4" class="form-control input-md">
                                                            <span style="word-break: normal" class="help-block">Search for all links in the URL<span id="empty_filter"> that contains <br /><kbd id="f_help" style="word-break: break-all">.mp4</kbd></span>.</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-4 col-md-offset-3">
                                                            <div class="checkbox">
                                                                <label for="advanced_multi_fetch" class="checkbox-label">
                                                                    <input type="checkbox" name="advanced_multi_fetch" id="advanced_multi_fetch" value="1">{{ Lang::get('messages.advanced') }} <span class="label label-danger">NEW</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="advanced_panel_multi_fetch" style="display: none;">
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="custom_user_agent">{{ Lang::get('messages.custom.user.agent') }}</label>
                                                            <div class="col-md-8">
                                                                <input id="custom_user_agent" name="custom_user_agent" type="text" placeholder="{{ env('APP_NAME', 'SEPEHR') }}/{{ env('VERSION', '2.0') }}" class="form-control input-md courier_font">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="custom_cookie">{{ Lang::get('messages.custom.cookie') }}</label>
                                                            <div class="col-md-8">
                                                                <input id="custom_cookie" name="custom_cookie" type="text" placeholder="session=4245434341; token=7f2422a1d" class="form-control input-md courier_font">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="custom_headers">{{ Lang::get('messages.custom.header') }}</label>
                                                            <div class="col-md-6">
                                                                <textarea style="max-width: 386px; max-height: 300px; min-height: 70px" class="form-control courier_font" id="custom_headers" name="custom_headers" placeholder="header1: Details&#10;header2: Details"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-1 col-md-offset-3">
                                                            <button id="fetch_submit" name="fetch_submit" class="btn btn-success btn-sm"><i class="fa fa-search"></i> {{ Lang::get('messages.find.all.matches') }}</button>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </form>
                                            <hr />
                                            <form id="frm_multi" class="form-horizontal" method="POST" action="" novalidate="">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <fieldset>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="links">{{ Lang::get('messages.links.to.transload') }}</label>
                                                        <div class="col-md-8">
                                                            <textarea style="max-width: 486px; max-height: 500px; min-height: 100px" class="form-control" id="links" name="links" wrap="off" placeholder="{{ Lang::get('messages.links') }}"></textarea>
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
                                                                <label for="hold" class="checkbox-label">
                                                                    <input type="checkbox" name="hold" id="hold" value="1">{{ Lang::get('messages.http.hold') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-4 col-md-offset-3">
                                                            <div class="checkbox">
                                                                <label for="advanced_multi" class="checkbox-label">
                                                                    <input type="checkbox" name="advanced_multi" id="advanced_multi" value="1">{{ Lang::get('messages.advanced') }} <span class="label label-danger">NEW</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="advanced_panel_multi" style="display: none">
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="custom_user_agent">{{ Lang::get('messages.custom.user.agent') }}</label>
                                                            <div class="col-md-8">
                                                                <input id="custom_user_agent" name="custom_user_agent" type="text" placeholder="{{ env('APP_NAME', 'SEPEHR') }}/{{ env('VERSION', '2.0') }}" class="form-control input-md courier_font">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="custom_cookie">{{ Lang::get('messages.custom.cookie') }}</label>
                                                            <div class="col-md-8">
                                                                <input id="custom_cookie" name="custom_cookie" type="text" placeholder="session=4245434341; token=7f2422a1d" class="form-control input-md courier_font">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="custom_headers">{{ Lang::get('messages.custom.header') }}</label>
                                                            <div class="col-md-6">
                                                                <textarea style="max-width: 386px; max-height: 300px; min-height: 70px" class="form-control courier_font" id="custom_headers" name="custom_headers" placeholder="header1: Details&#10;header2: Details"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-1 col-md-offset-3">
                                                            <button id="transload" name="transload" class="btn btn-primary" data-loading-text="Loading..."><i class="fa fa-cloud-download"></i> {{ Lang::get('messages.transload') }}</button>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </form>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="torrent_tab">
                                        @if(Auth::user()->torrent != 1)
                                            <div class="alert alert-info main-panel-padding" role="alert" style="text-align: center"><span style="font-weight: bold"><i class="fa fa-exclamation"></i> @lang('messages.notice'): </span>@lang('messages.torrent_disabled')</div>
                                        @else
                                        <ul class="nav nav-pills main-panel-padding" id="magnet_torrent_tab">
                                            <li class="active"><a data-toggle="pill" href="#torrent_file" style="padding: 2px 10px"><i class="fa fa-upload"></i> Torrent File</a></li>
                                            <li> <a data-toggle="pill" href="#magnet" style="padding: 2px 10px"><i class="fa fa-magnet"></i> Magnet</a></li>
                                        </ul>

                                        <div class="tab-content">
                                            <div id="torrent_file" class="tab-pane fade in active">
                                                <div id="torrent_div">
                                                    <form id="torrent_upload_form" class="form-horizontal" method="POST" enctype="multipart/form-data"  action="">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <fieldset>
                                                            <br/>
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label" for="torrent_file_upload">Torrent File Name: </label>
                                                                <div class="col-md-8 pull-left">
                                                                    <input id="torrent_upload_form_files" name="torrent_file_upload" type="file" class="input-file" required="">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label" for="submit"></label>
                                                                <div class="col-md-1">
                                                                    <button id="torrent_upload_form_submit" name="submit" class="btn btn-primary"><i class="fa fa fa-check"></i> Get Info</button>
                                                                </div>
                                                            </div>
                                                        </fieldset>
                                                        <br />
                                                    </form>
                                                </div>
                                            </div>
                                            <div id="magnet" class="tab-pane fade">
                                                <div id="magnet_torrent_div">
                                                    <form id="magnet_torrent_upload_form" class="form-horizontal" method="POST" enctype="multipart/form-data"  action="">
                                                        <fieldset>
                                                            <br/>
                                                            <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label"
                                                                       for="magnet_uri">Your Magnet URI
                                                                </label>
                                                                <div class="col-md-8">
                                                                    <input id="magnet_uri" name="link" type="text" placeholder="magnet:?xt=urn:btih:c12fe1c06bba254a9dc9f519b335aa7c1367a88a&dn" class="form-control input-md courier_font" required="">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label" for="submit"></label>
                                                                <div class="col-md-1">
                                                                    <button id="magnet_torrent_upload_form_submit" name="submit" class="btn btn-primary"><i class="fa fa-check"></i> Get Info</button>
                                                                </div>
                                                            </div>
                                                        </fieldset>
                                                        <br />
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                            <div id="torrent_res_div">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="pull-right">Torrent Name :</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <strong><kbd id="t_name"></kbd></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="pull-right">Torrent Size :</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <strong><kbd id="t_size"></kbd></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="pull-right">Comment :</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <strong><kbd id="t_comment"></kbd></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="pull-right">Hash Info :</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <strong><kbd id="t_hash"></kbd></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="pull-right">Piece Length :</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <strong><kbd id="t_pl"></kbd></strong>
                                                    </div>
                                                </div>
                                                <br /><br />
                                                <div id="jstree_demo_div"></div>
                                                <br />
                                                <form id="torrent_submit_form" class="form-horizontal" method="POST" action="">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input  id="torrent_file_name" name="torrent_file_name" value="">
                                                    <fieldset>
                                                        <br/><br/>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="t_submit_name">Torrent Name</label>
                                                            <div class="col-md-8 pull-left">
                                                                <input id="t_submit_name" name="t_submit_name" type="text" class="form-control input-md" required="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label" for="comment">{{ Lang::get('messages.comment') }}</label>
                                                            <div class="col-md-5">
                                                                <textarea style="max-width: 386px; max-height: 200px; min-height: 70px" class="form-control" id="t_submit_comment" name="comment" placeholder="{{ Lang::get('messages.desired.comment') }}"></textarea>
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
                                                                <button id="transload" name="t_transload" class="btn btn-primary" data-loading-text="Loading..."><i class="fa fa-cloud-download"></i> {{ Lang::get('messages.transload') }}</button>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </form>
                                            </div>

                                        @endif
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="check_tab">
                                        <div id="checkres_div" class="main-panel-padding">
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
                                        <div id="check_div" class="main-panel-padding">
                                            <p>@lang('messages.check_link_desc')</p>
                                            <form id="frm_check" class="form-horizontal" method="POST" action="" novalidate="">
                                                <input type="hidden" id="check_token" name="_token" value="{{ csrf_token() }}">
                                                <fieldset>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="check_link">{{ Lang::get('messages.link.to.check') }}</label>
                                                        <div class="col-md-8">
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
            $('#torrent_res_div').hide();


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
