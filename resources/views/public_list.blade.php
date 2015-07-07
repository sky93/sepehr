@extends('app')

@section('title', Lang::get('messages.public_files') . ' - ')

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
                <div class="panel-heading">@lang('messages.files.public')</div>
                <div class="panel-body">
                    @if (Config::get('leech.public_show_message'))
                        <div class="alert alert-info" dir="{{Config::get('leech.public_dir')}}">
                            {{Config::get('leech.public_message')}}
                        </div><hr />
                    @endif
                    <p>@lang('messages.public_tip')</p><br />
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive" dir="ltr">
                                <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                    <thead>
                                    <tr class="warning">
                                        <th style="width: 45%">@lang('messages.file.name')</th>
                                        <th style="width: 15%">@lang('messages.size')</th>
                                        <th style="width: 15%">@lang('messages.date')</th>
                                        <th style="width: 25%">@lang('messages.comments')</th>
                                    </tr>
                                    </thead>
                                    @foreach($files as $file)
                                        <tr>
                                            <td>
                                                <a target="_blank" href="{{ asset('/' . Config::get('leech.save_to') . '/' . $file->id . '_' . $file->file_name) }}">{{ $file->file_name }}</a>
                                            </td>
                                            <td>{{ $main->formatBytes($file->length) }}</td>
                                            <td>{{ date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</td>
                                            <td>{{ $file->comment }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
