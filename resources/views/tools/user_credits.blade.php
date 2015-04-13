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
                            <div class="col-lg-2">
                                <a href="{{ asset('tools/users/' . $user->username) }}" class="btn btn-success"><i class="fa fa-arrow-left fa-lg"></i> @lang('messages.back')</a>
                            </div>
                            @if (Auth::user()->role == 2)
                            <div style="padding: 5px" class="col-md-offset-8 col-md-2">
                                <button id="credit_change" style=" width: 100%" class="btn btn-warning"><i class="fa fa-plus-square fa-lg"></i> @lang('messages.change_credit')</button>
                            </div>
                            @endif
                        </div>
                </div>
            </div>
        </div>
    </div>
    @if (Auth::user()->role == 2)
    <script>
        $('#credit_change').click(function(){
            bootbox.dialog({
                        title: "@lang('messages.change_credit')",
                        message: '<div class="row">  ' +
                        '<div class="col-md-12"> ' +
                        '<form class="form-horizontal" role="form" method="POST" action="">' +
                        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                        '<div class="form-group"> ' +
                        '<label class="col-md-4 control-label" for="new_credit">@lang('messages.credits_user') (GB)</label> ' +
                        '<div class="col-md-4"> ' +
                        '<input name="new_credit" type="number" step="any" value="{{round($user->credit / 1024 / 1024 / 1024, 6)}}" min=0 class="form-control input-md"> ' +
                        '<span class="help-block">@lang('messages.new_amount', array('username' => $user->username))</span>' +
                        '</div> ' +
                        '</div>' +
                        '<button id="new_credit_submit" type="submit" name="action" hidden=""></button></form></div></div>',
                        buttons: {
                            success: {
                                label: '<i class="fa fa-check"></i> Save',
                                className: "btn-success",
                                callback: function () {
                                    $('#new_credit_submit').trigger('click');
                                }
                            }
                        }
                    }
            );
        });
    </script>
    @endif
@endsection
