@extends('app')

@section('title', Lang::get('messages.dl_list') . ' - ')

@section('content')
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
                                <th style="width: 9%">@lang('messages.name')</th>
                                @endif
                                <th style="width: 43%">@lang('messages.file.name')</th>
                                <th style="width: 8%">@lang('messages.dled.size')</th>
                                <th style="width: 8%">@lang('messages.size')</th>
                                <th style="width: 10%">@lang('messages.progress')</th>
                                <th style="width: 10%">@lang('messages.speed')</th>
                                <th style="width: 12%">@lang('messages.date')</th>
                                <th style="width: 85px">@lang('messages.details')</th>
                            </tr>
                            </thead>
                            @foreach($files as $file)
                                <tr id="r-{{ $file->id }}">
                                    <?php
                                    $downloaded_size = 0;
                                    $downloaded_speed = 0;

                                    if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"]["completedLength"])) {

                                        $downloaded_size = $aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"]["completedLength"];
                                    }

                                    if ($downloaded_size == 0) {
                                        $downloaded_size = $file->completed_length;
                                    }

                                    if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))['result']['downloadSpeed'])) {
                                        $downloaded_speed = $main->formatBytes($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))['result']['downloadSpeed'], 0) . '/s';
                                    }

                                    if ($file->state != -1) {
                                        if ($file->state == NULL)
                                            $downloaded_speed = 'In queue';
                                        elseif ($file->state == -2)
                                            $downloaded_speed = 'Paused';
                                        else
                                            $downloaded_speed = (($file->state === NULL) ? ('waiting...') : ('Error (' . $file->state . ')'));
                                    }

                                    ?>

                                    @if (Auth::user()->role == 2)
                                        <td>
                                                <a href="{{ url('tools/users/' . $file->username) }}">{{ $file->first_name . ' ' . $file->last_name }}</a>
                                        </td>
                                    @endif
                                    <td>{{ $file->file_name }}</td>
                                    <td id="dled">{{ $main->formatBytes($downloaded_size,1) }}</td>
                                    <td>{{ $main->formatBytes($file->length,1) }}</td>
                                    <td  style="vertical-align:top !important;">
                                        <div class="progress progress_dl">
                                            <div id="prog" class="progress-bar progress-bar-custom" role="progressbar"
                                                 aria-valuenow="0" aria-valuemin="0"
                                                 aria-valuemax="100"
                                                 style="width: {{  round($downloaded_size/$file->length*100,0) }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td id="speed">{{ $downloaded_speed }}</td>
                                    <td>{{ date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</td>
                                        <td>
                                            <a style="width: 100%; padding:0 5px 0 5px; margin-bottom: 1px;" href="{{ url('/files/' . $file->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-info"></i> @lang('messages.details') </a>
                                        </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="{{Auth::user()->role == 2 ? 3 : 2}}"></td>
                                <td>
                                    Total:
                                </td>
                                <td  style="vertical-align:top !important;">
                                    <div class="progress progress_dl">
                                        <div id="totalProg" class="progress-bar progress-bar-custom" role="progressbar"
                                             aria-valuenow="0" aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width: 0%">
                                        </div>
                                    </div>
                                </td>
                                <td id="totalSpeed">0 KB/s</td>
                                <td colspan="2"></td>
                            </tr>
                        </table>
                    </div>
                    <hr />
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="chart" height="150px"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var canv = $('#chart');
        $(window).on('resize', function(){
            canv.attr('width', canv.parent().width());
        });
        canv.attr('width', canv.parent().width());
        var vals = new TimeSeries();
        $(document).ready(function () {
        var chart = new SmoothieChart({millisPerPixel:67,grid:{fillStyle:'#ffffff',verticalSections:0},labels:{fillStyle:'#000000',fontSize:18,precision:0},minValue:0,maxValueScale:1.19}),
                canvas = document.getElementById('chart'),
                series = new TimeSeries();
        chart.addTimeSeries(vals, {lineWidth:2,strokeStyle:'#ef5050',fillStyle:'rgba(255,100,100,0.20)'});
        chart.streamTo(canvas, 1788);


            setInterval(function(){
                var activeDownloads = [];
                var totalSpeed = 0;
                var totalSpeed_p = 0;
                var files_count = 0;
                $.ajax({
                    url: "",
                    type: "POST",
                    data: "" ,
                    dataType: 'json',

                    success: function (response) {
                        var tableId = [];
                        $.each(response, function(index,jsonObject){
                            totalSpeed += jsonObject.speed_kb;
                            totalSpeed_p += +jsonObject.pprog.replace('%','');
                            files_count++;
                            activeDownloads.push(index);
                            $('#r-' + index + ' #speed').html(jsonObject.speed);
                            $('#r-' + index + ' #dled').html(jsonObject.dled_size);
                            $('#r-' + index + ' #prog').attr('style', 'width:' + jsonObject.pprog);
                        });
                        files_count = files_count ? files_count : 0;
                        $('#totalProg').attr('style', 'width:' + totalSpeed_p / files_count + '%');
                        $('#totalSpeed').html(totalSpeed + ' KB/s');
                        vals.append(new Date().getTime(), totalSpeed);
                        $(".dl-list tr").each(function() {
                            var idv = $(this).attr('id');
                            if(typeof idv !== "undefined")
                            {
                                tableId.push(idv.split("-")[1]);
                            }

                        });

                        $.each(tableId, function(i, v){
                            var exist = false;
                            $.each(activeDownloads, function(i2, v2){
                                if (v == v2) exist = true;
                            });
                            if (exist == false){
                                del = $("#r-" + v)
                                        .find('td')
                                        .wrapInner('<div style="display: block;" />')
                                        .parent()
                                        .find('td > div')
                                        .slideUp(300, function(){
                                            $(this).parent().parent().remove();
                                        });

                            }
                        });
                    },

                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("The following error occurred: " + textStatus, errorThrown);
                    }
                });
            }, 1000);
        });
    </script>
@endsection
