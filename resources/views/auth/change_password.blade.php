@extends('app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">@lang('messages.change_password')</div>
				<div class="panel-body">
                    <br />
				    @if (Session::has('message'))
                        <div class="alert alert-success">
                            <strong>@lang('messages.yaay')</strong> {{ Session::get('message') }}
                        </div>
                        <script>
                            setTimeout(function(){
                                window.location = '{{ url('logout') }}';
                            }, 1000);
                        </script>
				    @endif
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>@lang('messages.wops')</strong>@lang('messages.inputError')<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
					<form class="form-horizontal" role="form" method="POST" action="">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="form-group">
                            <label class="col-md-4 control-label">@lang('messages.old_password')</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" name="old_password" value="{{ old('old_password') }}">
                            </div>
                        </div>
                        <hr />

						<div class="form-group">
							<label class="col-md-4 control-label">@lang('messages.new_password')</label>
							<div class="col-md-6">
								<input type="password" class="form-control" name="new_password" value="{{ old('new_password') }}">
							</div>
						</div>

                        <div class="form-group">
							<label class="col-md-4 control-label">@lang('messages.new_password') (@lang('messages.again'))</label>
							<div class="col-md-6">
								<input type="password" class="form-control" name="new_password_confirmation" value="{{ old('new_password_confirmation') }}">
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
                                    @lang('messages.change')
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
