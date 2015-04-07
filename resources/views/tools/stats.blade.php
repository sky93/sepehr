@extends('app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.gband') (BETA! I WILL ADD SO MANY THINGS HERE LATER!!!)</div>
                <div class="panel-body">
                    <h4>General Info:</h4><hr />
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="chart" height="250px"></canvas>
                        </div>
                    </div>
                    <hr />
                    <table style="width:100%" class="ud">
                        <thead>
                        <tr>
                            <th style="width: 20%"></th>
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
                    </table><br /><br />
                    <h4>Detailed Info:</h4><legend></legend>
                    <table style="width:100%" class="uds">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <?php $gos = $aria2->getGlobalOption() ?>
                        @foreach($gos['result'] as $key => $go)
                            <tr>
                                <td>{{str_replace('-',' ', $key)}}:</td>
                                <td class="bld">{{ $go }}</td>
                            </tr>
                        @endforeach
                    </table>

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
        var chart = new SmoothieChart({millisPerPixel:67,grid:{fillStyle:'#ffffff',verticalSections:0},labels:{fillStyle:'#000000',fontSize:18,precision:0}}),
                canvas = document.getElementById('chart'),
                series = new TimeSeries();
        chart.addTimeSeries(vals, {lineWidth:2,strokeStyle:'#ef5050',fillStyle:'rgba(255,100,100,0.20)'});
        chart.streamTo(canvas, 1788);

        $(function() {
            var request;

            setInterval(function(){
                if (request) {
                    request.abort();
                }

                request = $.ajax({
                    url : "",
                    type : "post" ,
                    data : "_token={{ csrf_token() }}" ,
                    dataType: 'json'
                });

                request.done(function (response, textStatus, jqXHR) {
                    $('#speed').html(response.speed + '/s');
                    $('#numActive').html(response.numActive);
                    $('#numStopped').html(response.numStopped);
                    $('#numWaiting').html(response.numWaiting);
                    vals.append(new Date().getTime(), response.speed_b);

                });

                request.fail(function (jqXHR, textStatus, errorThrown) {
                    random.append(new Date().getTime(), 0);
                    console.error("The following error occurred: " +textStatus, errorThrown)
                });
            }, 1000);
        });
    </script>
@endsection
