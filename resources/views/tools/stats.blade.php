@extends('app')

@section('content')
    <?php
    $aria2 = new aria2(); //We Check it in the controller
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.gband') (BETA! I WILL ADD SO MANY THINGS HERE LATER!!!)</div>
                <div class="panel-body">
                    <h4>General Info:</h4><legend></legend>
                    <table style="width:100%" class="ud">
                        <thead>
                        <tr>
                            <th style="width: 20%"></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tr>
                            <td>Global Speed:</td>
                            <td class="bld">{{ $main->formatBytes($aria2->getGlobalStat()['result']['downloadSpeed'], 0) }}/s</td>
                        </tr>
                        <tr>
                            <td>Active Downloads:</td>
                            <td class="bld">{{ $aria2->getGlobalStat()['result']['numActive'] }}</td>
                        </tr>
                        <tr>
                            <td>Stopped Downloads:</td>
                            <td class="bld">{{ $aria2->getGlobalStat()['result']['numStopped'] }}</td>
                        </tr>
                        <tr>
                            <td>Waiting:</td>
                            <td class="bld">{{ $aria2->getGlobalStat()['result']['numWaiting'] }}</td>
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

@endsection
