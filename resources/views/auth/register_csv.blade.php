@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">@lang('messages.add.user.csv')</div>
                    <div class="panel-body">
                        <p>Your CSV format should be like <kbd>firstName, lastName, Password, Username</kbd>.</p><p></p>Please check out our <a target="_blank" href="https://github.com/Becca4Eva/Aria-Leecher/wiki/Register-User-Using-CSV-File">wiki</a> to get example and more help!</p>
                        <hr />
                        @if (Session::has('message'))
                            <div class="alert alert-success">
                                <strong>Yaaay!</strong> {{ Session::get('message') }}
                            </div>
                        @endif
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="csv_file">CSV File</label>
                                <div class="col-md-4">
                                    <input id="csv_file" name="csv_file" class="input-file" type="file">
                                </div>
                            </div>

                            <!-- Multiple Checkboxes -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="user_options">User Options</label>
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label for="user_options-0">
                                            <input type="checkbox" name="torrent" id="user_options-0" value="torrent">
                                            Add torrent files
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label for="user_options-1">
                                            <input type="checkbox" name="public" id="user_options-1" value="public">
                                            Make files public
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label for="user_options-2">
                                            <input type="checkbox" name="active" id="user_options-2" value="active" checked>
                                            Activate
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Multiple Radios -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="role_radio">User Role</label>
                                <div class="col-md-4">
                                    <div class="radio">
                                        <label for="role_radio-0">
                                            <input type="radio" name="role_radio" id="role_radio-0" value="1" checked="checked">
                                            User
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label for="role_radio-1">
                                            <input type="radio" name="role_radio" id="role_radio-1" value="2">
                                            Admin
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Credit (GB)</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="credit" value="{{ old('credit') ? old('credit') : 2 }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Register
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
