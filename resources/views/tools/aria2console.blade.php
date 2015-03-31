@extends('app')

@section('content')
    <?php
    if (Session::has('result'))
        $result = json_encode(Session::get('result'));
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.aria2console')</div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="frm" class="form-horizontal" method="POST" action="" novalidate="">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <fieldset>

                            <!-- Form Name -->
                            <legend>{{ Lang::get('messages.aria2_enter') }}:</legend>
                            <br/>
                            <!-- Link input-->
                            <div class="form-group">
                                <label class="col-md-3 control-label"
                                       for="function">{{ Lang::get('messages.command_name') }}</label>

                                <div class="col-md-3">
                                    <select class="combobox input-large form-control" name="function">
                                        <option value="" selected="selected">Select a Function</option>
                                        <option value="getGlobalStat"{{old('param') == 'getGlobalStat' ? 'selected':''}}>
                                            getGlobalStat
                                        </option>
                                        <option value="getVersion"{{old('param') == 'getVersion' ? 'selected':''}}>
                                            getVersion
                                        </option>
                                        <option value="addUri"{{old('param') == 'addUri' ? 'selected':''}}>addUri
                                        </option>
                                        <option value="addTorrent"{{old('param') == 'addTorrent' ? 'selected':''}}>
                                            addTorrent
                                        </option>
                                        <option value="addMetalink"{{old('param') == 'addMetalink' ? 'selected':''}}>
                                            addMetalink
                                        </option>
                                        <option value="remove"{{old('param') == 'remove' ? 'selected':''}}>remove
                                        </option>
                                        <option value="forceRemove"{{old('param') == 'forceRemove' ? 'selected':''}}>
                                            forceRemove
                                        </option>
                                        <option value="pause"{{old('param') == 'pause' ? 'selected':''}}>pause</option>
                                        <option value="pauseAll"{{old('param') == 'pauseAll' ? 'selected':''}}>
                                            pauseAll
                                        </option>
                                        <option value="forcePause"{{old('param') == 'forcePause' ? 'selected':''}}>
                                            forcePause
                                        </option>
                                        <option value="forcePauseAll"{{old('param') == 'forcePauseAll' ? 'selected':''}}>
                                            forcePauseAll
                                        </option>
                                        <option value="unpause"{{old('param') == 'unpause' ? 'selected':''}}>unpause
                                        </option>
                                        <option value="unpauseAll"{{old('param') == 'unpauseAll' ? 'selected':''}}>
                                            unpauseAll
                                        </option>
                                        <option value="tellStatus"{{old('param') == 'tellStatus' ? 'selected':''}}>
                                            tellStatus
                                        </option>
                                        <option value="getUris"{{old('param') == 'getUris' ? 'selected':''}}>getUris
                                        </option>
                                        <option value="getFiles"{{old('param') == 'getFiles' ? 'selected':''}}>
                                            getFiles
                                        </option>
                                        <option value="getPeers"{{old('param') == 'getPeers' ? 'selected':''}}>
                                            getPeers
                                        </option>
                                        <option value="getServers"{{old('param') == 'getServers' ? 'selected':''}}>
                                            getServers
                                        </option>
                                        <option value="tellActive"{{old('param') == 'tellActive' ? 'selected':''}}>
                                            tellActive
                                        </option>
                                        <option value="tellWaiting"{{old('param') == 'tellWaiting' ? 'selected':''}}>
                                            tellWaiting
                                        </option>
                                        <option value="tellStopped"{{old('param') == 'tellStopped' ? 'selected':''}}>
                                            tellStopped
                                        </option>
                                        <option value="changePosition"{{old('param') == 'changePosition' ? 'selected':''}}>
                                            changePosition
                                        </option>
                                        <option value="changeUri"{{old('param') == 'changeUri' ? 'selected':''}}>
                                            changeUri
                                        </option>
                                        <option value="getOption"{{old('param') == 'getOption' ? 'selected':''}}>
                                            getOption
                                        </option>
                                        <option value="changeOption"{{old('param') == 'changeOption' ? 'selected':''}}>
                                            changeOption
                                        </option>
                                        <option value="getGlobalOption"{{old('param') == 'getGlobalOption' ? 'selected':''}}>
                                            getGlobalOption
                                        </option>
                                        <option value="changeGlobalOption"{{old('param') == 'changeGlobalOption' ? 'selected':''}}>
                                            changeGlobalOption
                                        </option>
                                        <option value="purgeDownloadResult"{{old('param') == 'purgeDownloadResult' ? 'selected':''}}>
                                            purgeDownloadResult
                                        </option>
                                        <option value="removeDownloadResult"{{old('param') == 'removeDownloadResult' ? 'selected':''}}>
                                            removeDownloadResult
                                        </option>
                                        <option value="getSessionInfo"{{old('param') == 'getSessionInfo' ? 'selected':''}}>
                                            getSessionInfo
                                        </option>
                                        <option value="shutdown"{{old('param') == 'shutdown' ? 'selected':''}}>
                                            shutdown
                                        </option>
                                        <option value="forceShutdown"{{old('param') == 'forceShutdown' ? 'selected':''}}>
                                            forceShutdown
                                        </option>
                                        <option value="saveSession"{{old('param') == 'saveSession' ? 'selected':''}}>
                                            saveSession
                                        </option>
                                        <option value="multicall"{{old('param') == 'multicall' ? 'selected':''}}>
                                            multicall
                                        </option>
                                    </select>

                                    <script type="text/javascript">
                                        $(document).ready(function () {
                                            $('.combobox').combobox();
                                        });
                                    </script>

                                    <br/>
                                </div>
                                <div class="col-md-4">
                                    <input style="font-family: 'Courier New';" id="param" name="param" type="text"
                                           placeholder="[ ]"
                                           class="form-control input-md"
                                           value="{{ old('param') }}">
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="form-group">
                                <div class="col-md-9">
                                    <p style="padding-top: 6px" class="pull-right">Click <a target="_blank"
                                                                                            href="https://github.com/Becca4Eva/Aria-Leecher/wiki/Aria2-Console">Here</a>
                                        to gain more information and examples. </p>
                                </div>
                                <div class="col-md-1">
                                    <button id="exe" name="exe"
                                            class="btn btn-primary pull-right">{{ Lang::get('messages.run') }}</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>

                    <legend></legend>
                    <div class="results"></div>
                    <script src="{{ asset('/js/prettyprint.js') }}"></script>
                    <script>
                        $(document).ready(function () {
                            var request;

                            $("#frm").submit(function (event) {

                                if (request) {
                                    request.abort();
                                }
                                var $form = $(this);

                                var $inputs = $form.find("input, select, button, textarea");

                                var serializedData = $form.serialize();

                                $inputs.prop("disabled", true);
                                var sbtn = $('#exe');
                                var last_text = sbtn.html();
                                sbtn.html('<i class="fa fa-refresh fa-spin"></i> loading');

                                request = $.ajax({
                                    url: "",
                                    type: "post",
                                    data: serializedData
                                });

                                request.done(function (response, textStatus, jqXHR) {
                                    $('.results').html(prettyPrint(response));
                                });

                                request.fail(function (jqXHR, textStatus, errorThrown) {
                                    // Log the error to the console
                                    console.error(
                                            "The following error occurred: " +
                                            textStatus, errorThrown
                                    );
                                });

                                request.always(function () {
                                    $inputs.prop("disabled", false);
                                    sbtn.html(last_text);
                                });

                                event.preventDefault();
                            });
                        });

                    </script>

                </div>
            </div>
        </div>
    </div>

@endsection
