@extends('app')

@section('title', Lang::get('messages.usr_credit') . ' - ')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.user_credits')</div>
                <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" dir="ltr">
                            <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                <thead>
                                <tr class="warning">
                                    <th>@lang('messages.id')</th>
                                    <th>@lang('messages.credit_change')</th>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('messages.agent')</th>
                                </tr>
                                </thead>
                                @foreach($tracks as $track)
                                    <tr>
                                        <td>{{ $track->id }}</td>
                                        <td>{{ $main->formatBytes($track->credit_change, 3) }}</td>
                                        <td>{{ date( 'd/m/Y H:i', strtotime( $track->date ) ) }}</td>
                                        <td>{{ $track->username }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>

                </div>
                <div class="panel-footer">
                        <div class="row">
                            <div style="padding: 5px" class="col-md-2">
                                <a style="width: 100%" href="{{ Auth::user()->role == 2 ? asset('tools/users/' . $user->username) : asset('/') }}" class="btn btn-success"><i class="fa fa-arrow-left fa-lg"></i> @lang('messages.back')</a>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
