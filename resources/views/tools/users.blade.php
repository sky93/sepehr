@extends('app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">User Management</div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table style="font-size: 5px"
                               class="users dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                            <thead>
                            <tr class="warning">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Credits</th>
                                <th>Queue Credits</th>
                                <th>E-mail</th>
                                <th>Role</th>
                                <th>Active</th>
                                <th style="width: 20px">@lang('messages.active')</th>
                                <th style="width: 20px">@lang('messages.delete')</th>
                            </tr>
                            </thead>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $main->formatBytes($user->credit,1) }}</td>
                                    <td>{{ $main->formatBytes($user->queue_credit,1) }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role == 1)
                                            User
                                        @elseif($user->role == 2)
                                            Admin
                                        @else
                                            N/A
                                        @endif
                                            {{--//o.w.--}}
                                    </td>
                                    <td>{{ $user->active ? 'YES' : 'NO' }}</td>
                                    <td>
                                        <a style="width: 80px; padding:0 5px 0 5px; margin-bottom: 1px;"
                                           href="{{ url('/tools/register') }}"
                                           class="btn btn-sm {{ ($user->active == 1 ? 'btn-danger' : 'btn-success') }}"><i
                                                    class="fa {{ ($user->active == 1 ? 'fa-ban' : 'fa-check') }}"></i>
                                            {{ ($user->active == 1 ? 'Ban' : 'Release') }}
                                        </a>
                                    </td>
                                    <td>
                                        <a style="width: 80px; padding:0 5px 0 5px; margin-bottom: 1px;"
                                           href="{{ url('/tools/register') }}" class="btn btn-sm btn-danger"><i
                                                    class="fa fa-remove"></i>
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">

                            <a style="width: 100%" href="{{ url('/tools/register') }}" class="btn btn-success"><i
                                        class="fa fa-user-plus fa-lg"></i> Add New User</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
