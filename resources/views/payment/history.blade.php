@extends('app')

@section('title', Lang::get('messages.ust_pay_hist') . ' - ')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">@lang('messages.ust_pay_hist')</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" dir="ltr">
                            <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                <thead>
                                <tr class="warning">
                                    <th>@lang('messages.orderId')</th>
                                    <th>@lang('messages.amount') ({{Config::get('leech.currency')}})</th>
                                    <th>@lang('messages.credit')</th>
                                    <th>@lang('messages.RefID')</th>
                                    <th>@lang('messages.date')</th>
                                </tr>
                                </thead>
                                @foreach($tracks as $track)
                                    <tr>
                                        <td>{{ $track->id }}</td>
                                        <td>{{ $track->amount }}</td>
                                        <td>{{ $track->credit }}</td>
                                        <td>{{ $track->SaleReferenceId }}</td>
                                        <td>{{ date( 'd/m/Y H:i', strtotime( $track->pay_time ) ) }}</td>
                                    </tr>
                                @endforeach
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
@endsection
