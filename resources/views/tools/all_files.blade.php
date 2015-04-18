@extends('app')

@section('title', Lang::get('messages.all_files') . ' - ')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/jquery.twbsPagination.min.js') }}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.all_files')</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="users dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                    <thead>
                                    <tr class="warning">
                                        <th>@lang('messages.id')</th>
                                        <th >@lang('messages.username')</th>
                                        <th >@lang('messages.file.name')</th>
                                        <th style="width: 12%">@lang('messages.size')</th>
                                        <th style="width: 12%">@lang('messages.date')</th>
                                        @if(Config::get('leech.auto_delete'))
                                        <th style="width: 7%">@lang('messages.delete_in')</th>
                                        @endif
                                        <th>@lang('messages.status')</th>
                                        <th>@lang('messages.comments')</th>
                                        <th style="width: 85px">@lang('messages.details')</th>
                                    </tr>
                                    </thead>
                                    @foreach($files as $file)
                                        <tr <?=$file->deleted == 1 ? 'class="danger"' : ''?>>
                                            <td>{{ $file->id }}</td>
                                            <td>
                                                <a href="{{ url('tools/users/' . $file->username) }}">{{ $file->first_name . ' ' . $file->last_name }}</a>
                                            </td>
                                            <td>
                                                @if ($file->state == 0 && $file->deleted == 0 && $file->state != null)
                                                <a target="_blank" href="{{ asset('/' . Config::get('leech.save_to') . '/' . $file->id . '_' . $file->file_name) }}">{{ $file->file_name }}</a>
                                                @else
                                                {{ $file->file_name }}
                                                @endif
                                            </td>
                                            <td>{{ $main->formatBytes($file->completed_length,1) }}/{{ $main->formatBytes($file->length,1) }}</td>
                                            <td>{{  date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</td>
                                            @if(Config::get('leech.auto_delete'))
                                                @if($file->keep)
                                                <td>Never</td>
                                                @elseif($file->state != 0)
                                                <td>-</td>
                                                @else
                                                <td>{{ $main->hours2day(Config::get('leech.auto_delete_time') - ((time() - strtotime($file->date_completed))/60/60)) < 0 ? 'Deleted' : $main->hours2day(Config::get('leech.auto_delete_time') - ((time() - strtotime($file->date_completed))/60/60)) }}</td>
                                                @endif
                                            @endif
                                            @if($file->state == -3 || $file->deleted==1)
                                                <td>Deleted</td>
                                            @elseif($file->state == null)
                                                <td>In queue</td>
                                            @elseif($file->state == -1)
                                                <td>Downloading</td>
                                            @elseif($file->state == -2)
                                                <td>Paused</td>
                                            @elseif($file->state == 0)
                                                <td>Finished</td>
                                            @else
                                                <td>Error</td>
                                            @endif
                                            <td>{{ $file->comment }}</td>
                                            <td>
                                                <a style="width: 100%; padding:0 5px 0 5px; margin-bottom: 1px;" target="_blank" href="{{ url('/files/' . $file->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-info"></i> @lang('messages.details')</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                                <div style="width: 100%;  text-align: center;">
                                    <ul id="page" style="display: table; margin: 0 auto;" class="pagination-sm pagination-demo"></ul>
                                </div>
                                <script>
                                    $('.pagination-demo').twbsPagination({
                                        totalPages: {{ ceil ($files_count / 20) }},
                                        visiblePages: 10,
                                        href: <?= "'?page={{number}}#page'" ?>
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
