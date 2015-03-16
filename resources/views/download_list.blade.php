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
                                <th style="width: 47%">@lang('messages.file.name')</th>
                                <th style="width: 8%">@lang('messages.dled.size')</th>
                                <th style="width: 8%">@lang('messages.size')</th>
                                <th style="width: 15%">@lang('messages.progress')</th>
                                <th style="width: 10%">@lang('messages.speed')</th>
                                <th style="width: 12%">@lang('messages.date')</th>
                                <th style="width: 12%">@lang('messages.date')</th>
                            </tr>
                            </thead>
                            @foreach($files as $file)
                                <tr>
                                    <?php
                                    $downloaded_size = 0;
                                    $downloaded_speed = 0;
                                    // var_dump($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT)));
                                    if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"]["completedLength"])) {
                                        $downloaded_size = $aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"]["completedLength"];
                                    }

                                    if ($downloaded_size === 0) {
                                        if (file_exists(public_path() . '/' . Config::get('leech.save_to') . '/' . $file->id . '_' . $file->file_name)) {
                                            $downloaded_size = filesize(public_path() . '/' . Config::get('leech.save_to') . '/' . $file->id . '_' . $file->file_name);
                                        }
                                    }

                                    if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))['result']['downloadSpeed'])) {
                                        $downloaded_speed = $main->formatBytes($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))['result']['downloadSpeed'], 0) .'/s';
                                    }
                                    if ($file->state != -1) {
                                        $downloaded_speed = (($file->state === NULL) ? ('waiting...') : ('Error (' . $file->state . ')'));
                                    }

                                    ?>
                                    <td>{{ $file->file_name }}</td>
                                    <td>{{ $main->formatBytes($downloaded_size,3) }}</td>
                                    <td>{{ $main->formatBytes($file->length,3) }}</td>
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
                                </tr>
                            @endforeach
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
