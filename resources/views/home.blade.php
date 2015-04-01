@extends('app')

@section('content')
    <link href="{{ asset('/css/bootstrap-combobox.css') }}" rel="stylesheet">
    <script src="{{ asset('/js/bootstrap-combobox.js') }}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">{{ Lang::get('messages.home') }}</div>
                <div class="panel-body">
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
                    <form class="form-horizontal" method="POST" action="" novalidate="">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <fieldset>

                            <!-- Form Name -->
                            <legend>{{ Lang::get('messages.home.title') }}</legend>
                            <br/>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $('#http_auth').click(function () {
                                        $("#HTTP").slideToggle(150);
                                        $('#http_password').prop('required', true);
                                        $('#http_username').prop('required', true);
                                    });
                                });
                            </script>
                            <!-- Link input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label"
                                       for="link">{{ Lang::get('messages.link.to.transload') }}</label>

                                <div class="col-md-5">
                                    <input id="link" name="link" type="text"
                                           placeholder="{{ Lang::get('messages.your.link') }}"
                                           class="form-control input-md" required="">
                                </div>
                            </div>

                            <!-- HTTP CheckBox -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="http_auth"></label>

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
                                <!-- HTTP User input-->
                                <div class="form-group">
                                    <label class="col-md-4 control-label"
                                           for="http_username">{{ Lang::get('messages.http.username') }}</label>

                                    <div class="col-md-2">
                                        <input id="http_username" name="http_username" type="text"
                                               placeholder="{{ Lang::get('messages.http.username') }}"
                                               class="form-control input-md" required="">

                                    </div>
                                </div>

                                <!-- HTTP Password input-->
                                <div class="form-group">
                                    <label class="col-md-4 control-label"
                                           for="http_password">{{ Lang::get('messages.http.password') }}</label>

                                    <div class="col-md-2">
                                        <input id="http_password" name="http_password" type="password"
                                               placeholder="{{ Lang::get('messages.http.password') }}"
                                               class="form-control input-md" required="">

                                    </div>
                                </div>
                            </div>

                            <!-- Comment -->
                            <div class="form-group">
                                <label class="col-md-4 control-label"
                                       for="comment">{{ Lang::get('messages.comment') }}</label>

                                <div class="col-md-4">
                                    <textarea style="max-width: 386px; max-height: 200px; min-height: 70px"
                                              class="form-control" id="comment" name="comment"
                                              placeholder="{{ Lang::get('messages.desired.comment') }}"></textarea>
                                </div>
                            </div>


                            <!-- HTTP Hold -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="hold"></label>

                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label for="hold">
                                            <input type="checkbox" name="hold" id="hold" value="1">
                                            {{ Lang::get('messages.http.hold') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="transload"></label>

                                <div class="col-md-1">
                                    <button id="transload" name="transload"
                                            class="btn btn-primary">{{ Lang::get('messages.transload') }}</button>
                                </div>
                            </div>

                        </fieldset>
                    </form>
                    @if(Auth::user()->torrent != 1)
                        <br/>
                        <div class="alert alert-info" role="alert" style="text-align: center"><span
                                    style="font-weight: bold"><i class="fa fa-exclamation"></i> @lang('messages.notice')
                                : </span>@lang('messages.torrent_disabled')</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
