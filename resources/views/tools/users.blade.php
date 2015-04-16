@extends('app')

@section('title', Lang::get('messages.users') . ' - ')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/jquery.twbsPagination.min.js') }}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">User Management</div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="users dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                            <thead>
                            <tr class="warning">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Credits</th>
                                <th>E-mail</th>
                                <th>Role</th>
                                <th>Active</th>
                                <th style="width: 85px">@lang('messages.details')</th>
                            </tr>
                            </thead>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->first_name . ' ' . $user->last_name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $main->formatBytes($user->credit,1) }}</td>
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
                                    <td>{{ $user->active ? 'YES' : 'NO' }}</td>
                                    <td>
                                        <a style="width: 100%; padding:0 5px 0 5px; margin-bottom: 1px;"
                                           href="{{ url('/tools/users/' . $user->username) }}" class="btn btn-sm btn-primary"><i class="fa fa-info"></i> @lang('messages.details')
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <div style="width: 100%;  text-align: center;">
                            <ul id="page" style="display: table; margin: 0 auto;" class="pagination-sm pagination-demo"></ul>
                            <a style="display: table; margin: 0 auto;" href="?page=all">show all users</a>
                        </div>
                        <script>
                            $('.pagination-demo').twbsPagination({
                                totalPages: {{ ceil ($users_count / 20) }},
                                visiblePages: 10,
                                href: <?= "'?page={{number}}#page'" ?>
                            });
                        </script>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">
                            <a style="width: 100%" href="{{ url('/tools/register') }}" class="btn btn-success"><i class="fa fa-user-plus fa-lg"></i> Add New User</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
