@extends('app')

@section('content')
    <script>
        function checkFile(id) {
            if (document.getElementById('file_' + id).checked == true) document.getElementById('file_' + id).checked = false;
            else document.getElementById('file_' + id).checked = true;
            return false;
        }
    </script>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">

                <div class="panel-heading">@lang('messages.files.list')</div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <ul>
                                @foreach ($errors->all() as $error_message)
                                    <li>{{ $error_message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (isset($messages) && count($messages) > 0)
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>@lang('messages.yaay')</strong> @choice('messages.file.completed', count($messages))<br><br>
                            <ul>
                                @foreach ($messages as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (isset($error) && count($error) > 0)
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>@lang('messages.wops')</strong> @choice('messages.file.not.completed', count($error))<br><br>
                            <ul>
                                @foreach ($error as $erro)
                                    <li>{{ $erro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="form-horizontal" role="form" method="POST" action="">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="row">
                            <div class="col-md-1">
                                <button type="submit" name="action" value="delete" class="btn btn-danger"><i class="fa fa-trash-o fa-lg"></i> Delete</button>
                            </div>
                            <div class="col-md-11">
                                <button type="submit" name="action" value="public" class="btn btn-success"><i class="fa fa-globe fa-lg"></i> Public</button>
                            </div>
                        </div><br />
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive" dir="ltr">
                                    <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                        <thead>
                                        <tr class="warning">
                                            <th style="width: 5%">@lang('messages.file.select')</th>
                                            <th style="width: 45%">@lang('messages.file.name')</th>
                                            <th style="width: 10%">@lang('messages.size')</th>
                                            <th style="width: 15%">@lang('messages.date')</th>
                                            <th style="width: 25%">@lang('messages.comments')</th>
                                            <th style="width: 85px">@lang('messages.details')</th>
                                        </tr>
                                        </thead>
                                        @foreach($files as $file)
                                            <tr>
                                                <td onmousedown="checkFile({{ $file->id }}); return false;">
                                                    <input onmousedown="checkFile({{ $file->id }}); return false;" type="checkbox" name="files[]" id="file_{{ $file->id }}" value="{{ $file->id }}" />
                                                </td>
                                                <td>
                                                    <a target="_blank" href="{{ asset('/' . Config::get('leech.save_to') . '/' . $file->id . '_' . $file->file_name) }}">{{ $file->file_name }}</a>
                                                </td>
                                                <td>{{ $main->formatBytes($file->length,1) }}</td>
                                                <td>{{ date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</td>
                                                <td>{{ $file->comment }}</td>
                                                <td>
                                                    <a style="width: 100%; padding:0 5px 0 5px; margin-bottom: 1px;"
                                                       href="{{ url('/files/' . $file->id) }}"
                                                       class="btn btn-sm btn-primary"><i
                                                                class="fa fa-info"></i> @lang('messages.details')
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>

                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
