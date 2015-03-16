@extends('app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.files.list')</div>
                <div class="panel-body">
                    <div class="table-responsive" dir="ltr">
                        <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                            <thead>
                            <tr class="warning">
                                <th style="width: 47%">ID</th>
                                <th style="width: 8%">Username</th>
                                <th style="width: 8%">Name</th>
                                <th style="width: 15%">Credits</th>
                                <th style="width: 10%">Queue Credits</th>
                                <th style="width: 12%">E-mail</th>
                                <th style="width: 12%">Active</th>
                            </tr>
                            </thead>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                            @endforeach
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
