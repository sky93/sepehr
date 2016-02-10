@extends('app')

@section('title', Lang::get('messages.users') . ' - ')

@section('content')
<script type="text/javascript" src="{{ asset('/assets/twbs-pagination/jquery.twbsPagination.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('/assets/tablesorter/jquery.tablesorter.min.js') }}"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">User Management</div>
            <div class="panel-body">
                <form method="POST" action="" novalidate="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div class="col-md-offset-1 col-md-1">
                            <input id="link" name="id" type="text" placeholder="{{ Lang::get('messages.id') }}" class="form-control input-sm" value="{{ isset($id) ? $id : ''}}">
                        </div>
                        <div class="col-md-2">
                            <input id="link" name="username" type="text" placeholder="{{ Lang::get('messages.username') }}" class="form-control input-sm" value="{{ isset($username) ? $username : ''}}">
                        </div>
                        <div class="col-md-2">
                            <input id="link" name="first_name" type="text" placeholder="{{ Lang::get('messages.firstname') }}" class="form-control input-sm" value="{{ isset($first_name) ? $first_name : ''}}">
                        </div>
                        <div class="col-md-2">
                            <input id="link" name="last_name" type="text" placeholder="{{ Lang::get('messages.lastname') }}" class="form-control input-sm" value="{{ isset($last_name) ? $last_name : ''}}">
                        </div>
                        <div class="col-md-3">
                            <input  id="link" name="email" type="text" placeholder="{{ Lang::get('messages.email') }}" class="form-control input-sm" value="{{ isset($email) ? $email : ''}}">
                        </div>
                    </div>
                    <br />
                    <div class="row">
                        <div class="col-md-offset-1 col-md-2">
                            <select name="active" class="form-control">
                                <option value="1"{{isset($active) ? ($active == '1' ? ' selected' : '') : ''}}>-</option>
                                <option value="2"{{isset($active) ? ($active == '2' ? ' selected' : '') : ''}}>Active</option>
                                <option value="3"{{isset($active) ? ($active == '3' ? ' selected' : '') : ''}}>Not Active</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="torrent" class="form-control">
                                <option value="1"{{isset($torrent) ? ($torrent == '1' ? ' selected' : '') : ''}}>-</option>
                                <option value="2"{{isset($torrent) ? ($torrent == '2' ? ' selected' : '') : ''}}>Torrent Enabled</option>
                                <option value="3"{{isset($torrent) ? ($torrent == '3' ? ' selected' : '') : ''}}>Torrent Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="public" class="form-control">
                                <option value="1"{{isset($public) ? ($public == '1' ? ' selected' : '') : ''}}>-</option>
                                <option value="2"{{isset($public) ? ($public == '2' ? ' selected' : '') : ''}}>Public Files Enabled</option>
                                <option value="3"{{isset($public) ? ($public == '3' ? ' selected' : '') : ''}}>Public Files Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-offset-1 col-md-2">
                            <button id="transload" name="transload" class="btn btn-success pull-right"><i class="fa fa-search"></i> {{ Lang::get('messages.search') }}</button>
                        </div>
                    </div>
                </form>
                {{--<br />--}}
                <hr />
                <div class="table-responsive">
                    <table id="userTable" class="users dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                        <thead>
                        <tr class="warning">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Credits</th>
                            <th>E-mail</th>
                            <th>Last Seen</th>
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
                                <td data-text="{{ $user->credit }}">{{ $main->formatBytes($user->credit,1) }}</td>
                                <td>{{ $user->email }}</td>
                                <td data-text="{{ strtotime( $user->last_seen ) }}">
                                    @if (time() - strtotime($user->last_seen) <= 30)
                                        <span style="color: #2ca02c; font-weight: bold">Online</span>
                                    @elseif ($user->last_seen)
                                        <time class="timeago" datetime="{{ date( DATE_ISO8601, strtotime( $user->last_seen ) ) }}">{{ date( 'd/m/Y H:i', strtotime( $user->last_seen ) ) }}</time>
                                    @else
                                        Never
                                    @endif
                                </td>
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
                    @if (!isset($page))
                    <div style="width: 100%;  text-align: center;">
                        <ul id="page" style="display: table; margin: 0 auto;" class="pagination-sm pagination-demo"></ul>
                        <a style="display: table; margin: 0 auto;" href="?page=all">@lang('messages.show_all_users')</a>
                    </div>
                    <script>
                        $('.pagination-demo').twbsPagination({
                            totalPages: {{ ceil ($users_count / 20) }},
                            visiblePages: 10,
                            href: <?= "'?page={{number}}#page'" ?>
                        });
                    </script>
                    @else
                        <div style="width: 100%;  text-align: center;">
                            <a href="{{ url('/tools/users') }}"><i class="fa fa-chevron-left"></i> @lang('messages.bk_to_users')</a>
                        </div>
                    @endif
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
<script>
$(document).ready(function() {
    $("#userTable").tablesorter();
    $("time.timeago").timeago();
});
</script>
@endsection
