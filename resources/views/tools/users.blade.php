@extends('app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">User Management</div>
                <div class="panel-body">
                    <div class="table-responsive" dir="ltr">
                        <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
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
                                    </td>
                                    <td>{{ $user->active ? "YES" : "NO" }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-1">
                            <a href="{{ url('/tools/register') }}" class="btn btn-success"><i
                                        class="fa fa-plus fa-lg"></i> Add New User</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
