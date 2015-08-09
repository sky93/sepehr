@extends('app')

@section('title', Lang::get('messages.file_details') . ' - ')

@section('more_actions')
    <div class="dropup btn-group" role="group">
        <button type="button" class="btn btn-info dropdown-toggle dbtn" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-plus"></i> @lang('messages.more_actions') <span class="caret"></span>
        </button>
        <ul class="dropdown-menu mo" role="menu">
            <li><a href="javascript:void(0)" id="md5">@lang('messages.md5')</a></li>
            <li><a href="javascript:void(0)" id="sha1">@lang('messages.sha1')</a></li>
            <li class="divider"></li>
            <li><a href="javascript:void(0)" id="rename">@lang('messages.rename')</a></li>
        </ul>
    </div>
@endsection

@section('public')
    <button type="submit" name="action" value="public" class="btn btn-success"{{((Auth::user()->public == 0) ? ' disabled':'')}}><i class="fa fa-{{ $file->public == 1 ? 'lock' : 'unlock' }} fa-lg"></i> {{ $file->public == 1 ? 'Make Private' : 'Make Public' }}</button>
@endsection

@section('back')
    @if ($file->state == 0 && $file->state != null)
        <a class="btn btn-success" href="{{ asset('/files') }}"><i class="fa fa-arrow-left fa-lg"></i> Back</a>
    @else
        <a class="btn btn-success" href="{{ asset('/downloads') }}"><i class="fa fa-arrow-left fa-lg"></i> Back</a>
    @endif
@endsection

@section('edit')
    <button type="submit" name="action" value="edit" class="btn btn-warning"><i class="fa fa-pencil fa-lg"></i> Edit Link</button>
@endsection

@section('pause')
    <button type="submit" name="action" value="pause" class="btn btn-success"><i class="fa fa-{{ $file->state == -1 ? 'pause' : 'play' }} fa-lg"></i> {{ $file->state == -1 ? 'Pause' : 'Resume' }}</button>
@endsection

@section('retry')
    <button type="submit" name="action" value="retry" class="btn btn-success"><i class="fa fa-repeat fa-lg"></i> Retry</button>
@endsection

@section('remove')
    <a href="javascript:void(0)" id="remove" class="btn btn-danger delacc"><i class="fa fa-trash-o fa-lg"></i> Remove</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">{{ $file->file_name }}</div>
                <div class="panel-body">
                    @if (isset($errors) && count($errors) > 0)
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>@lang('messages.wops')</strong> {{ Lang::get('messages.inputError')}}<br><br>
                            <ul>
                                @foreach ($errors->all() as $erro)
                                    <li>{{ $erro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (Session::has('message'))
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div style="text-align: center">
                         <?=Session::get('message')?>
                        </div>
                    </div>
                    @endif
                    @if(+$file->state == 0 && $file->state !== null)
                        <div class="alert alert-success" role="alert"><span
                                    style="font-weight: bold">Yaay! </span>@lang('errors.0')
                            @if($file->deleted == 0)
                                Click <a target="_blank"
                                         href="{{ asset('/' . 'link' . '/' . $file->id . '_' . $file->file_name) }}">Here</a>
                                to download the file.
                            @endif
                        </div>
                    @elseif($file->state===NULL)
                        <div class="alert alert-info" role="alert"><span
                                    style="font-weight: bold">Wait more! </span>@lang('errors.null')</div>
                    @elseif($file->state == -1)
                        <div class="alert alert-info" role="alert"><span style="font-weight: bold"><i
                                        class="fa fa-spinner fa-pulse"></i> Wait more! </span>@lang('errors.-1')</div>
                    @elseif ($file->state != -3)
                        @if(Lang::has('errors.' . $file->state))
                            <div class="alert alert-danger" role="alert"><span
                                        style="font-weight: bold">Oh snap! </span>@lang('errors.' . $file->state)</div>
                        @else
                            <div class="alert alert-danger" role="alert"><span
                                        style="font-weight: bold">Oh snap! </span>@lang('errors.999')</div>
                        @endif
                    @endif
                    @if($file->deleted == 1)
                        <div class="alert alert-danger" role="alert"><span
                                    style="font-weight: bold"></span>@lang('errors.-3')</div>
                    @endif
                    <legend></legend>
                    <div class="row">
                        <div class="col-sm-3">
                            <img style="max-height: 300px; display: block; margin-left: auto; margin-right: auto"
                                 class="img-responsive" src="{{ url('/img/file-text.png') }}"><br/>
                            {{--@if($user->active == 1)--}}
                            {{--<p style="text-align: center">User is <span style="color: #2ca02c; font-weight: bold">Active</span>.</p>--}}
                            {{--@else--}}
                            {{--<p style="text-align: center">User is <span style="color: #b92c28; font-weight: bold">Inactive</span>.</p>--}}
                            {{--@endif--}}
                        </div>
                        <div class="col-sm-9">
                            <div class="form-horizontal" method="GET" action="" novalidate="">
                                <fieldset>
                                    <h4>File Info:</h4>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label" for="link">File Name:</label>

                                        <div class="col-md-5">
                                            <input style="cursor: default" class="form-control input-md" type="text"
                                                   value="{{ $file->file_name }}" readonly>
                                        </div>
                                    </div>
                                    @if(! $file->torrent)
                                    <div class="form-group">
                                        <label class="col-md-4 control-label" for="link">Link:</label>

                                        <div class="col-md-5">
                                            <input style="cursor: default" class="form-control input-md" type="text"
                                                   value="{{ $file->link }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"
                                               for="http_username">{{ Lang::get('messages.http.username') }}</label>

                                        <div class="col-md-2">
                                            <input style="cursor: default" type="text" class="form-control input-md"
                                                   readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"
                                               for="http_password">{{ Lang::get('messages.http.password') }}</label>

                                        <div class="col-md-2">
                                            <input style="cursor: default" type="password" class="form-control input-md"
                                                   readonly>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"
                                               for="comment">{{ Lang::get('messages.comment') }}</label>

                                        <div class="col-md-4">
                                            <textarea
                                                    style="cursor: default; max-width: 330px; max-height: 200px; min-height: 70px"
                                                    class="form-control" readonly>{{ $file->comment }}</textarea>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="panel-footer">
                    <form class="form-horizontal" role="form" method="POST" action="">
                        <div class="row">
                            <div class="col-md-3">
                                @yield('back')
                            </div>
                            <div class="col-md-9">
                                <div class="pull-right">
                                    <div class="btn-group" role="group" aria-label="BECCA">
                                        @if($file->deleted != 1)
                                            @if($file->state == 0 && $file->state != null)
                                                @if(Auth::user()->id == $file->user_id || Auth::user()->role == 2)
                                                @yield('public')
                                                @yield('remove')
                                                @yield('more_actions')
                                                @endif
                                            @elseif($file->state == -1 || $file->state == -2 || $file->state == null)
                                                @yield('pause')
                                                @yield('edit')
                                                @yield('remove')
                                            @elseif($file->state == -3)
                                            @else
                                                @yield('retry')
                                                @yield('remove')
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input id="new_name" type="hidden" name="new_name" value="">
                        <button id="more_actions" type="submit" name="action" value="n/a" hidden></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('.mo li a').click(function () {
            var last_name = '<i class="fa fa-plus"></i> More Actions <span class="caret"></span>';
            $('.dbtn').html('<i class="fa fa-plus"></i> ' + $(this).text() + ' <span class="caret"></span>');
            var idattr = $(this).attr('id');
            var more = $('#more_actions');
            if (idattr == 'rename'){
                var mainResult = null;
                bootbox.prompt({
                    title: "Enter the new name:",
                    value: "{{ $file->file_name }}",
                    callback: function(result) {
                        mainResult = result;
                        if (result === null) {
                            $('.dbtn').html(last_name);
                            return 0;
                        } else {
                            if (result == '{{ $file->file_name }}'){
                                bootbox.alert('You did not change the name!');
                                $('.dbtn').html(last_name);
                                return 0;
                            }
                            $('#new_name').attr('value', result);
                            more.attr('value', idattr);
                            more.trigger('click');
                        }
                    }
                });
            }else {
                more.attr('value', idattr);
                more.trigger('click');
            }
        });


        $('.delacc').click(function () {
            var idattr = $(this).attr('id');
            bootbox.confirm('You are about to delete {{ $file->file_name }}. Are you sure?', function (result) {
                if (result) {
                    var more = $('#more_actions');
                    more.attr("value", idattr);
                    more.trigger('click');
                }
            });

        });
    </script>
@endsection

