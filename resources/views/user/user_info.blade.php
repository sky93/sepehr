@extends('app')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">{{ $user->first_name . ' ' . $user->last_name }}</div>
            <div class="panel-body">
                @if (Session::has('message'))
                    <div class="alert alert-success">
                        <strong>@lang('messages.yaay')</strong> {{ Session::get('message') }}
                    </div>
                @endif
                @if(isset($_GET['first']))
                    <div class="alert alert-danger">
                        <strong>{{ Lang::get('messages.hello')}} {{ $user->first_name }}!</strong><br><br>
                        @lang('messages.valid_email')
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>{{ Lang::get('messages.wops')}}</strong> {{ Lang::get('messages.inputError' )}}<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    <form class="form-horizontal" role="form" method="POST" action="">
                        <legend>User <kbd>{{ $user->username }}</kbd></legend>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group">
                            <label class="col-md-4 control-label">Name</label>

                            <div class="col-md-3">
                                <input style="text-align: center" type="text" class="form-control" name="first_name" disabled value="{{ $user->first_name }}">
                            </div>
                            <div class="col-md-3">
                                <input style="text-align: center" type="text" class="form-control" name="last_name" disabled value="{{ $user->last_name }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Username</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="username" disabled value="{{ $user->username }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">E-Mail Address</label>
                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" value="{{ old('email') ? old('email') : $user->email }}">
                                <span class="help-block">@lang('messages.valid_mail')</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Password</label>
                            <div class="col-md-3">
                                <a href="{{ url('user/'. $user->username .'/password')}}" class="btn btn-sm btn-warning">@lang('messages.change_password')</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Credit (GB)</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="credit" disabled value="{{ round($user->credit / 1024 / 1024 /1024,3) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-2 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">@lang('messages.save_changes')</button>
                            </div>
                        </div>
                    </form>
                @if(Auth::user()->torrent != 1)
                    <br/>
                    <div class="alert alert-info" role="alert" style="text-align: center"><span style="font-weight: bold"><i class="fa fa-exclamation"></i> @lang('messages.notice'): </span>@lang('messages.torrent_disabled')</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
