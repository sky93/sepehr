@extends('app')

@section('content')
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Login</div>
				<div class="panel-body">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>{{ Lang::get('messages.wops' )}}</strong> {{ Lang::get('messages.inputError' )}}<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<div class="form-group">
							<label class="col-md-4 control-label">{{ Lang::get('messages.user' )}}</label>
							<div class="col-md-5">
								<input type="text" class="form-control" name="username" value="{{ old('username') }}">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">{{ Lang::get('messages.pass' )}}</label>
							<div class="col-md-5">
								<input type="password" class="form-control" name="password" value="{{ old('password') }}" autocomplete="off">
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<div class="checkbox">
									<label>
										<input type="checkbox"  name="remember" <?php echo old('remember') ? 'checked' : ''; ?>>{{ Lang::get('messages.remember' )}}
									</label>
								</div>
							</div>
						</div>
                        @if (!$main->trusted_ip($_SERVER['REMOTE_ADDR']))
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                {!! app('captcha')->display(); !!}
                            </div>
                        </div>
                        @endif
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">{{ Lang::get('messages.login')}}</button>
								<a class="btn btn-link" href="{{ url('/password/email') }}">{{ Lang::get('messages.forgot' )}}</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection
