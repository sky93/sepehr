@extends('app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger">
                <div class="panel-heading">{{ Lang::get('messages.error') }}</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h1 style="font-family: 'Courier New'; font-weight: bolder"> @if(isset($error_title) && !empty($error_title)) {{ $error_title }} @else ERROR 756 @endif </h1>
                            <h4 style="font-family: 'Courier New'"> @if(isset($error_message) && !empty($error_message)) {{ $error_message }} @else Unknown error happened. Sorry! @endif </h4>
                        </div>
                        <div class="col-md-4">
                            <img style="display: block; margin-left: auto; margin-right: auto" class="img-responsive" src="{{ url('/img/e.png') }}">
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-offset-4 col-sm-4">
                            <button style="width: 100%" class="btn btn-success btn-lg" onclick="window.history.back(); return;"><i class="fa fa-arrow-left fa-lg"></i> {{ Lang::get('messages.tbk') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection