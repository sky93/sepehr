@extends('app')

@section('content')
    <?php
    $aria2 = new aria2(); //We Check it in the controller

    ?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">{{ Lang::get('messages.dl.list') }}</div>
                <div class="panel-body">
                <div class="table-responsive" dir="ltr">
                        <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                            <thead>
                            <tr class="warning">
                                @if (Auth::user()->role == 2)
                                    <th style="width: 9%">@lang('messages.username')</th>
                                @endif
                                <th style="width: 43%">@lang('messages.file.name')</th>
                                <th style="width: 8%">@lang('messages.dled.size')</th>
                                <th style="width: 8%">@lang('messages.size')</th>
                                <th style="width: 10%">@lang('messages.progress')</th>
                                <th style="width: 10%">@lang('messages.speed')</th>
                                <th style="width: 12%">@lang('messages.date')</th>
                                {{--<th style="width: 12%">@lang('messages.operations')</th>--}}
                            </tr>
                            </thead>
                            @foreach($files as $file)
                                <tr>
                                    <?php

                                   echo str_pad($file->id, 16, '0', STR_PAD_LEFT);

                                    $downloaded_size = 0;
                                    $downloaded_speed = 0;

                                    if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"]["completedLength"])) {
                                        $downloaded_size = $aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"]["completedLength"];
                                    }


                                    if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))['result']['downloadSpeed'])) {
                                        $downloaded_speed = $main->formatBytes($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))['result']['downloadSpeed'], 0) . '/s';
                                    }

                                    if ($downloaded_size == 0) {
                                        $downloaded_size = $file->completed_length;
                                    }

                                    if ($file->state != -1) {
                                        $downloaded_speed = (($file->state === NULL) ? ('waiting...') : ('Error (' . $file->state . ')'));
                                    }

                                    ?>
                                    @if (Auth::user()->role == 2)
                                        <td><a target="_blank"
                                               href="{{ url('/user/' . $file->username) }}">{{ $file->username }}</a>
                                        </td>
                                    @endif
                                    <td>{{ $file->file_name }}</td>
                                    <td>{{ $main->formatBytes($downloaded_size,1) }}</td>
                                    <td>{{ $main->formatBytes($file->length,1) }}</td>
                                    <td style="vertical-align:top !important;">
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-custom" role="progressbar"
                                                 aria-valuenow="{{ $file->completed_length }}" aria-valuemin="0"
                                                 aria-valuemax="{{ $file->length }}"
                                                 style="width: {{  round($downloaded_size/$file->length*100,0) }}%;">
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $downloaded_speed }}</td>
                                    <td>{{ date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</td>
                                    {{--<td>{{ time() }}</td>--}}
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
