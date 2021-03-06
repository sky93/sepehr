@extends('app')

@section('title', Lang::get('messages.file_list') . ' - ')

@section('content')
<link href="{{ asset('/assets/iCheck/skins/square/red.css') }}" rel="stylesheet">
<script type="text/javascript" src="{{ asset('/assets/iCheck/icheck.min.js') }}"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">@lang('messages.files.list')</div>
            <form class="form-horizontal" role="form" method="POST" action="">
                <div class="panel-body">
                    @if (Config::get('leech.files_show_message'))
                        <div class="alert alert-info" dir="{{Config::get('leech.files_dir')}}">
                            {{Config::get('leech.files_message')}}
                        </div><hr />
                    @endif
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <ul>
                                @foreach ($errors->all() as $error_message)
                                    <li>{{ $error_message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (isset($messages) && count($messages) > 0)
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>@lang('messages.yaay')</strong> @choice('messages.file.completed', count($messages))<br><br>
                            <ul>
                                @foreach ($messages as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (isset($error) && count($error) > 0)
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>@lang('messages.wops')</strong> @choice('messages.file.not.completed', count($error))<br><br>
                            <ul>
                                @foreach ($error as $erro)
                                    <li>{{ $erro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive" dir="ltr">
                                <table class="dl-list table table-hover table-bordered enFonts table-striped tableCenter">
                                    <thead>
                                    <tr class="warning">
                                        <th style="width: 5%">@lang('messages.file.select')</th>
                                        <th style="width: 45%">@lang('messages.file.name')</th>
                                        <th style="width: 10%">@lang('messages.size')</th>
                                        <th style="width: 15%">@lang('messages.date')</th>
                                        @if(Config::get('leech.auto_delete'))
                                            <th style="width: 7%">@lang('messages.delete_in')</th>
                                        @endif
                                        <th style="width: 25%">@lang('messages.comments')</th>
                                        <th style="width: 85px">@lang('messages.details')</th>
                                    </tr>
                                    </thead>
                                    @foreach($files as $file)
                                        <tr<?= ($file->downloads > 0) ? ' class="success"' : '' ?>>
                                            <td>
                                                <input type="checkbox" name="files[]" id="file_{{ $file->id }}" value="{{ $file->id }}" />
                                            </td>
                                            <td>
                                                <a target="_blank" href="{{ asset('/' . 'link' . '/' . $file->id . '_' . $file->file_name) }}">{{ $file->file_name }}</a>
                                            </td>
                                            <td>{{ $main->formatBytes($file->length,1) }}</td>
                                            <td><time class="timeago" datetime="{{ date( DATE_ISO8601, strtotime( $file->date_added ) ) }}">{{ date( 'd/m/Y H:i', strtotime( $file->date_added ) ) }}</time></td>
                                            @if(Config::get('leech.auto_delete'))
                                                @if($file->keep)
                                                    <td>Never</td>
                                                @else
                                                    <td>{{ $main->hours2day(Config::get('leech.auto_delete_time') - ((time() - strtotime($file->date_completed))/60/60)) }}</td>
                                                @endif
                                            @endif
                                            <td>{{ $file->comment }}</td>
                                            <td>
                                                <a style="width: 100%; padding:0 5px 0 5px; margin-bottom: 1px;" href="{{ url('/files/' . $file->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-info"></i> @lang('messages.details')</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if(count($files) > 0)
                                        <tr>
                                            <td>
                                                <div class="btn-group" data-toggle="buttons">
                                                    <label id="call" class="btn btn-warning btn-sm">
                                                        <i class="fa fa-check-square-o fa-lg"></i>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div style="padding: 5px" class="col-md-offset-4 col-md-2">
                            <button id="copy" style="width: 100%" class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="@lang('messages.copy.tooltip')"><i class="fa fa-clipboard fa-lg"></i> @lang('messages.copy')</button>
                        </div>
                        <div style="padding: 5px" class="col-md-2">
                            <button style="width: 100%" type="submit" name="action"{{(((Auth::user()->role == 2) || (Auth::user()->role != 2 && Config::get('leech.keep') == 'all')) ? ' ':' disabled ')}}value="never" class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="@lang('messages.keep.tooltip')"><i class="fa fa-chain-broken fa-lg"></i> @lang('messages.keep')</button>
                        </div>
                        <div style="padding: 5px" class="col-md-2">
                            <button style="width: 100%" type="submit" name="action"{{((Auth::user()->public == 1) ? ' ':' disabled ')}}value="public" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="@lang('messages.public.tooltip')"><i class="fa fa-globe fa-lg"></i> @lang('messages.public')</button>
                        </div>
                        <div style="padding: 5px" class="col-md-2">
                            <button style="width: 100%" type="submit" name="action" value="delete" class="btn btn-danger"><i class="fa fa-trash-o fa-lg"></i> @lang('messages.delete')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
<script>
$(document).ready(function() {
    $('#copy').click(function (e) {
        e.preventDefault();
        var links = '';
        $('td input[id^="file_"]:checked').each(function () {
            links += $(this).closest('td').next('td').find('a')[0].href + "\n";
        });
        if (links == '') links = 'Nothing selected...';
        setTimeout(function() {$('#links').select();},600);
        bootbox.dialog({
            title: "@lang('messages.cp_links')",
            message: '<div class="row">  ' +
            '<div class="form-group"> ' +
            '<div class="col-md-12"> ' +
            '<textarea style="max-width: 100%; max-height: 260px; height: 260px" class="form-control courier_font" id="links"  wrap="off">' + links + '</textarea>' +
            '</div> ' +
            '</div></div>',
            buttons: {
                success: {
                    label: '<i class="fa fa-check"></i> Ok',
                    className: "btn-success"
                }
            }
        }
        );
        $('#links').dblclick(function(){
            $(this)[0].select();
        });

    });



    var check = true;
    $('#call').click(function(){
        if (check){
            $('td [id^="file_"]').prop('checked', check).parent().addClass('checked');
        }else{
            $('td [id^="file_"]').prop('checked', check).parent().removeClass('checked');
        }

        check = !check;
    });

    $("time.timeago").timeago();

    $('[data-toggle="tooltip"]').tooltip();
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-red',
        radioClass: 'iradio_square-red'
    });
    $("[id^=file_]").parent().parent().click(function(){
        $(this).children('div').toggleClass('checked');
        var chkbx = $(this).children('div').children('input');
        chkbx.prop("checked", !chkbx.prop("checked"));
    });
    $('tr').each(function(){
        if (!$(this).hasClass('success')){
            $(this).children('td').children('div').addClass('checked');
            $(this).children('td').children('div').children('input').prop('checked', true);
        }
    });
});
</script>
@endsection
