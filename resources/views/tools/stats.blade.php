@extends('app')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.gband') (BETA! I WILL ADD SO MANY THINGS HERE LATER!!!)</div>
                <div class="panel-body">
                    <h4>General Info:</h4><hr />
                    <div class="row">
                        <div class="col-md-4">
                            <div id="ul_p" class="panel panel-primary">
                                <div class="panel-heading" style="font-size: 12px; padding: 1px 5px">@lang('messages.dl_inf')</div>
                                <div class="panel-body">
                                    <br />
                                    <table style="width:100%" class="ud">
                                        <thead>
                                        <tr>
                                            <th style=""></th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tr>
                                            <td>Global Speed:</td>
                                            <td id="speed" class="bld">{{ $main->formatBytes($aria2->getGlobalStat()['result']['downloadSpeed'], 3) }}/s</td>
                                        </tr>
                                        <tr>
                                            <td>Active Downloads:</td>
                                            <td id="numActive" class="bld">{{ $aria2->getGlobalStat()['result']['numActive'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Stopped Downloads:</td>
                                            <td id="numStopped" class="bld">{{ $aria2->getGlobalStat()['result']['numStopped'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Waiting:</td>
                                            <td id="numWaiting" class="bld">{{ $aria2->getGlobalStat()['result']['numWaiting'] }}</td>
                                        </tr>
                                    </table>
                                    <br />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="ur_p" class="panel panel-primary">
                                <div class="panel-heading" style="font-size: 12px; padding: 1px 5px">@lang('messages.bwg')</div>
                                <div class="panel-body" style="height: 90%">
                                    <canvas id="chart" height=""></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ul_p" class="panel panel-primary">
                        <div class="panel-heading" style="font-size: 12px; padding: 1px 5px">@lang('messages.diskspace')</div>
                            <div class="panel-body">
                                Path: <kbd>{{ $main->get_storage_path() }}</kbd>
                                <br /><br />
                                <div id="disksize">0 B / 0 B (0%)</div>
                                <div class="progress">
                                    <div id="disk" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;width: 0%">
                                        0%
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div id="ul_p" class="panel panel-primary">
                        <div class="panel-heading" style="font-size: 12px; padding: 1px 5px">@lang('messages.lastten')</div>
                        <div class="panel-body">
                            <div class="table-responsive" dir="ltr">
                                <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                    <thead>
                                    <tr class="warning">
                                        <th>@lang('messages.id')</th>
                                        <th>@lang('messages.username')</th>
                                        <th>@lang('messages.file.name')</th>
                                        <th>@lang('messages.size')</th>
                                        <th>@lang('messages.date')</th>
                                        <th>@lang('messages.status')</th>
                                        <th style="width: 85px">@lang('messages.details')</th>
                                    </tr>
                                    </thead>
                                    <tbody id="main_table">
                                    <td colspan="7">Please Wait ...</td>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var canv = $('#chart');
        $(window).on('resize', function(){
            $('#ur_p').height($('#ul_p').height());
            canv.attr('width', canv.parent().width());
            canv.attr('height',canv.parent().height());
        });
        $('#ur_p').height($('#ul_p').height());
        canv.attr('width', canv.parent().width());
        canv.attr('height', canv.parent().height());
        var vals = new TimeSeries();
        var chart = new SmoothieChart({millisPerPixel:100,grid:{fillStyle:'#ffffff',verticalSections:0},labels:{fillStyle:'#000000',fontSize:18,precision:0},minValue:0,maxValueScale:1.19}),
                canvas = document.getElementById('chart'),
                series = new TimeSeries();
        chart.addTimeSeries(vals, {lineWidth:2,strokeStyle:'#ef5050',fillStyle:'rgba(255,100,100,0.20)'});
        chart.streamTo(canvas, 1788);

        function gs() {
            $.ajax({
                url : '',
                type : 'post' ,
                data : "gs=1" ,
                dataType: 'json',

                success: function (response, textStatus, jqXHR) {
                    $('#speed').html(response.speed + '/s');
                    $('#numActive').html(response.numActive);
                    $('#numStopped').html(response.numStopped);
                    $('#numWaiting').html(response.numWaiting);
                    vals.append(new Date().getTime(), response.speed_b);

                },

                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("gs - The following error occurred: " + textStatus, errorThrown);
                }
            });
        }

        function lf() {
            $.ajax({
                url : '',
                type : 'POST' ,
                data : "lf=1" ,
                dataType: 'json',

                success: function (response, textStatus, jqXHR) {
                    $('#main_table').empty();
                    $.each(response, function(i, item) {
                        $('#main_table').append('<tr><td>' + response[i].id + '</td>' + '<td><a target="_blank" href="' + response[i].username_l + '">' + response[i].username + '</a></td>' + '<td>' + response[i].file_name + '</td>' + '<td>' + response[i].length + '</td>' + '<td>' + response[i].date_added + '</td>' + '<td>' + response[i].state + '</td>' + '<td><a target="_blank" style="width: 100%; padding:0 5px 0 5px; margin-bottom: 1px;" href="' + response[i].details + '" class="btn btn-sm btn-primary"><i class="fa fa-info"></i> ' + response[i].details_t + '</a></td>' + '<tr>');
                    });
                },

                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("lf - The following error occurred: " + textStatus, errorThrown);
                }
            });
        }

        function sz() {
            $.ajax({
                url : '',
                type : 'POST' ,
                data : "sz=1" ,
                dataType: 'json',

                success: function (response, textStatus, jqXHR) {
                    console.log(response);
                    var disk = $('#disk');
                    var colour = 'progress-bar progress-bar-success';
                    if (response.percent > 40 && response.percent <= 70)
                        colour = 'progress-bar progress-bar-warning';
                    else if(response.percent > 70)
                        colour = 'progress-bar progress-bar-danger';

                    disk.text(response.percent + '%');
                    disk.attr('style', 'min-width: 2em;width:' + response.percent + '%');
                    disk.attr('class', colour);
                    $('#disksize').text(response.free + ' / ' + response.total + ' (' + response.percent + '%)');
                },

                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("sz - The following error occurred: " + textStatus, errorThrown);
                }
            });
        }
        sz();
        gs();
        lf();
        setInterval(gs, 2000);
        setInterval(lf, 5000);
        setInterval(sz, 60000);
    </script>
@endsection
