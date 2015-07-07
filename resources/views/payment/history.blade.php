@extends('app')

@section('title', Lang::get('messages.usr_pay_hist') . ' - ')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">@lang('messages.usr_pay_hist')</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" dir="ltr">
                            <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                <thead>
                                <tr class="warning">
                                    <th>@lang('messages.id')</th>
                                    @if (Auth::user()->role == 2)
                                    <th>@lang('messages.name')</th>
                                    @endif
                                    <th>@lang('messages.orderId')</th>
                                    <th>@lang('messages.amount') ({{Config::get('leech.currency')}})</th>
                                    <th>@lang('messages.credit')</th>
                                    <th>@lang('messages.RefID')</th>
                                    <th>@lang('messages.settle')</th>
                                    <th>@lang('messages.date')</th>
                                </tr>
                                </thead>
                                <?php $total_amount = 0; $total_credit = 0; $count=0 ?>
                                @foreach($tracks as $track)
                                    <tr>
                                        <td>{{ ++$count }}</td>
                                        <?php if ($track->settleResponse !== null  && $track->settleResponse == 0) $total_amount += $track->amount; $total_credit += $track->credit; ?>
                                        @if (Auth::user()->role == 2)
                                        <td><a href="{{ url('tools/users/' . $track->username) }}">{{ $track->first_name . ' ' . $track->last_name }}</a></td>
                                        @endif
                                        <td><kbd>{{ $track->id }}</kbd></td>
                                        <td>{{ number_format($track->amount) }}</td>
                                        <td>{{ $track->credit }} GB</td>
                                        <td><kbd>{{ $track->RefId }}</kbd></td>
                                        <td {{ ($track->settleResponse !== null  && $track->settleResponse == 0) ? 'class=success' : 'class=danger' }}>{{ ($track->settleResponse !== null  && $track->settleResponse == 0) ? 'YES' : 'NO' }}</td>
                                        <td><time class="timeago" datetime="{{ date( DATE_ISO8601, strtotime( $track->pay_time ) ) }}">{{ date( 'd/m/Y H:i', strtotime( $track->pay_time ) ) }}</time></td>
                                    </tr>
                                @endforeach
                                <tr style="font-size: 17px">
                                    <td colspan="2"></td>
                                    <td class="info" style="font-size: inherit"><strong>Total:</strong></td>
                                    <td class="info" style="font-size: inherit"><strong>{{ number_format($total_amount) }}</strong></td>
                                    <td class="info" style="font-size: inherit"><strong>{{ number_format($total_credit) }} GB</strong></td>
                                    <td colspan="3"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <div class="row">
                    <div style="padding: 5px 15px" class="col-md-2">
                        <button style="width: 100%" onclick="window.history.back();" class="btn btn-success"><i class="fa fa-arrow-left fa-lg"></i> @lang('messages.back')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>   $(document).ready(function() {
        $("time.timeago").timeago();
    });
</script>
@endsection
