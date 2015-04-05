@extends('app')

@section('title', Lang::get('messages.usr_details') . ' - ')

@section('content')
    <style>
        @media (min-width : 800px) {
            .per20{
                width: 30%;
            }
        }
        @media (min-width : 991px) {
            .per20{
                width: 30%;
            }
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">{{ $user->name }}</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <img style="display: block; margin-left: auto; margin-right: auto" class="img-responsive img-thumbnail" src="{{ url('/img/default_avi.png') }}"><br />
                            @if($user->active == 1)
                                <p style="text-align: center">User is <span style="color: #2ca02c; font-weight: bold">Active</span>.</p>
                            @else
                                <p style="text-align: center">User is <span style="color: #b92c28; font-weight: bold">Inactive</span>.</p>
                            @endif
                        </div>
                        <div class="col-sm-9">
                            <h4>General Info:</h4><legend></legend>
                            <table style="width:100%" class="ud">
                                <thead>
                                <tr>
                                    <th class="per20"></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tr>
                                    <td>ID:</td>
                                    <td class="bld">{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td>Name:</td>
                                    <td class="bld">{{ $user->first_name . ' ' . $user->last_name }}</td>
                                </tr>
                                <tr>
                                    <td>Username:</td>
                                    <td class="bld">{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td>E-mail:</td>
                                    <td class="bld">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td>Credits:</td>
                                    <td class="bld">{{ $main->formatBytes($user->credit,1) }} <span style="font-size: x-small">({{ $user->credit }} bytes)</span></td>
                                </tr>
                                <tr>
                                    <td>Queue Credits:</td>
                                    <td class="bld">{{ $main->formatBytes($user->queue_credit,1) }} <span style="font-size: x-small">({{ $user->queue_credit }} bytes)</span></td>
                                </tr>
                                <tr>
                                    <td>Role:</td>
                                    <td class="bld">
                                        @if($user->role == 1)
                                            User
                                        @elseif($user->role == 2)
                                            Admin
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table><br /><br />
                            <h4>Bandwidth Info:</h4><legend></legend>
                            <table style="width:100%" class="ud">
                                <thead>
                                <tr>
                                    <th style="width: 30%;"></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tr>
                                    <td>Total Download Request:</td>
                                    <td class="bld">{{ $main->formatBytes($userd->length_sum,1) }} <span style="font-size: x-small">({{ $userd->length_sum }} bytes)</span></td>
                                </tr>
                                <tr>
                                    <td>Total Bandwidth Used:</td>
                                    <td class="bld">{{ $main->formatBytes($userd->completed_length_sum,1) }} <span style="font-size: x-small">({{ $userd->completed_length_sum }} bytes)</span></td>
                                </tr>
                            </table><br /><br />
                            <h4>Files:</h4><legend></legend>
                            <table style="width:100%" class="ud">
                                <thead>
                                <tr>
                                    <th style="width: 30%;"></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tr>
                                    <td>Total Transload Requests:</td>
                                    <td class="bld">{{ $userd->total_files }}<span style="font-size: x-small"> ({{ $userd->total_files_deleted }} including deleted)</span></td>
                                </tr>
                                <tr>
                                    <td>Total Errors:</td>
                                    <td class="bld">{{ $userd->total_error_files }}<span style="font-size: x-small"> ({{ $userd->total_error_files_deleted }} including deleted)</span></td>
                                </tr>
                                <tr>
                                    <td>Total Downloads In Queue:</td>
                                    <td class="bld">{{ $userd->total_download_queue }}</td>
                                </tr>
                            </table>
                        </div>
                    </div><br /><br />
                    <h4>Files Downloaded by {{ $user->name }}:</h4><legend></legend>
                    <div class="table-responsive" dir="ltr">
                        <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                            <thead>
                            <tr class="warning">
                                <th style="width: 45%">@lang('messages.file.name')</th>
                                <th style="width: 15%">@lang('messages.size')</th>
                                <th style="width: 15%">@lang('messages.date')</th>
                                <th style="width: 5%">@lang('messages.deleted')</th>
                                <th style="width: 5%">@lang('messages.status')</th>
                                <th style="width: 25%">@lang('messages.comments')</th>
                            </tr>
                            </thead>
                            @foreach($user_files as $file)
                                <tr>
                                    <td>{{ $file->file_name }}</td>
                                    <td>{{ $main->formatBytes($file->completed_length,1) . ' / ' . $main->formatBytes($file->length,1) }}</td>
                                    <td>{{ date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</td>
                                    <td>{{ $file->deleted ? 'YES' : 'NO' }}</td>
                                    <td>{{ $file->state === NULL ? 'NULL' : $file->state }}</td>
                                    <td>{{ $file->comment }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>

                </div>
                <div class="panel-footer">
                    <form class="form-horizontal" role="form" method="POST" action="">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="row">
                            <div style="padding: 5px" class="col-md-offset-6 col-md-2">
                                <a  href="{{ asset('tools/users/' . $user->username . '/credits') }}" style=" width: 100%" class="btn btn-warning"><i class="fa fa-bars fa-lg"></i> @lang('messages.clog')</a>
                            </div>
                            <div style="padding: 5px" class="col-md-2">
                                <button type="submit" name="action" value="delete" style="width: 100%" class="btn btn-danger"><i class="fa fa-trash-o fa-lg"></i> Delete User</button>
                            </div>
                            <div style="padding: 5px" class="col-md-2">
                                <button type="submit" name="action" value="ban" style="width: 100%" class="btn {{ ($user->active == 1 ? 'btn-danger' : 'btn-success') }}"><i class="fa fa-lg {{ ($user->active == 1 ? 'fa-ban' : 'fa-check') }}"></i> {{ ($user->active == 1 ? 'Ban' : 'Active') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
