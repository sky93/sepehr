<?php
$main = new main();
$show_message = false;
// Only users can show new messages.
if (!Auth::guest()){
    $message = Request::cookie('change_log');
    if (Config::get('leech.show_change_message') == true && ($message == null || $message != substr(md5(Config::get('leech.change_message')),0, 6))){
        $show_message = true;
        $message_content = Config::get('leech.change_message');
        $change_title1 = Config::get('leech.change_title1');
        $change_title2 = Config::get('leech.change_title2');
        Cookie::queue('change_log', substr(md5(Config::get('leech.change_message')), 0, 6), 129600); //2592000 = 60 * 24 * 30 * 3 = 3 months
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Sepehr Mohaghegh | BECCA4EVA@live.com AND Pejman Yaghmaie | yaghmaie.p@gmail.com">
    <title>@yield('title'){{ env('APP_TITLE', 'Sepehr') }}</title>
    <script src="{{ asset('/js/pace.min.js') }}"></script>
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet">
    {{--<link href="{{ asset('/css/bootstrap-theme.min.css') }}" rel="stylesheet">--}}
    <link href="{{ asset('/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/main.css?v=5') }}" rel="stylesheet">
    <link href="{{ asset('/favicon.ico?v=1') }}" type="image/x-icon" rel="favicon">
    <link href="{{ asset('/css/toastr.min.css') }}" rel="stylesheet">

    <!--[if lt IE 9]>
    <script type="text/javascript" src="{{ asset('/js/html5shiv.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/respond.js') }}"></script>

    <![endif]-->


    <script type="text/javascript" src="{{ asset('/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/bootbox.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('/js/smoothie.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/toastr.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery.timeago.js') }}"></script>
</head>
<body>
@if($show_message == true)
    <script>
        bootbox.dialog({
            title: "{{$change_title1}}",
            message: '<img style="display: block; margin-left: auto; margin-right: auto" class="img-responsive" src="{{ asset(Config::get('leech.logo_address')) }}"/><br/><h4><strong>{{$change_title2}}</strong><h4><hr /> <span style="font-size:15px; line-height: 160%;"><?=$message_content?></span>'
        });
    </script>
@endif
<div class="container">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" rel="home" href="{{ asset('') }}" title="@lang('messages.slogan')"><img style="max-width:50px; margin-top: -17px; margin-right: -10px" src="{{ asset('/img/logo_small.png') }}"></a>
                <a class="navbar-brand" href="{{ asset('') }}" title="@lang('messages.slogan')">{{ env('APP_NAME', 'SEPEHR') }}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    @if (!Auth::guest())
                        @if (Auth::user()->role != 2)
                        <li><a href="{{ asset('') }}"><i class="fa fa-download"></i> @lang('messages.home')</a></li>
                        @endif
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-file-o"></i> @lang('messages.files')<span class="caret"></span></a>
                            <ul class="dropdown-menu bw" role="menu">
                                <li><a href="{{ url('/downloads') }}"><i class="fa fa-tasks"></i> @lang('messages.dl.list')</a></li>
                                <li><a href="{{ url('/files') }}"><i class="fa fa-list"></i> @lang('messages.files.list')</a></li>
                                <li class="divider"></li>
                                <li><a href="{{ url('/public') }}"><i class="fa fa-globe"></i> @lang('messages.files.public')</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    @if (!Auth::guest())
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false"><span>Credits </span><span class="user-credit label{{ Auth::user()->credit / 1024 / 1024 /1024 < 10 ? ' label-danger ': ' label-success '  }}maincredit">{{ $main->formatBytes(Auth::user()->credit, 1) }}</span><span class="caret"></span></a>
                            <ul class="dropdown-menu bw" role="menu">
                                {{--<li class="divider"></li>--}}
                                <li><a href="{{  url('user/'. Auth::user()->username) . '/payments/history' }}"><i class="fa fa-credit-card"></i> @lang('messages.peyment_history')</a></li>
                                <li><a href="{{ url('user/'. Auth::user()->username) . '/credit/history' }}"><i class="fa fa-history"></i> @lang('messages.credit_history')</a></li>
                                <li class="divider"></li>
                                <li><a href="{{ url('buy') }}"><i class="fa fa-money"></i> @lang('messages.credit_buy')</a></li>
                            </ul>
                        </li>
                        @if (Auth::user()->role == 2)
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-cog"></i> @lang('messages.tools') <span class="caret"></span></a>
                                <ul class="dropdown-menu bw" role="menu">
                                    <li class="dropdown-header">@lang('messages.user.management')</li>
                                    <li><a href="{{ url('/tools/register') }}"><i class="fa fa-plus"></i> @lang('messages.add.user')</a></li>
                                    <li><a href="{{ url('/tools/register-csv') }}"><i class="fa fa-plus"></i> @lang('messages.add.user.csv')</a></li>
                                    <li><a href="{{ url('/tools/users') }}"><i class="fa fa-users"></i> @lang('messages.manage.users')</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ url('/tools/status') }}"><i class="fa fa-area-chart"></i> @lang('messages.gband')</a></li>
                                    <li><a href="{{ url('/tools/aria2console') }}"><i class="fa fa-terminal"></i> @lang('messages.a2console')</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ url('/tools/files') }}"><i class="fa fa-file-text-o"></i> @lang('messages.all_files')</a></li>
                                    <li><a href="{{ url('/tools/payments') }}"><i class="fa fa-usd"></i> @lang('messages.all.payments')</a></li>
                                </ul>
                            </li>
                        @endif
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false"><i
                                        class="fa fa-user"></i> {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu bw" role="menu">
                                <li class="dropdown-header">Credits</li>
                                <li>
                                    <a href="{{ url('buy') }}">
                                        <i class="fa fa-usd"></i> @lang('messages.credits'): <span style="margin-top: 2px" class="user-credit label{{ Auth::user()->credit / 1024 / 1024 /1024 < 10 ? ' label-danger ': ' label-success '  }}pull-right">{{ $main->formatBytes(Auth::user()->credit, 1) }}</span>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li><a href="{{ url('user/'. Auth::user()->username) }}"><i class="fa fa-user"></i> @lang('messages.personal_info')</a></li>
                                <li><a href="{{ url('user/'. Auth::user()->username .'/password') }}"><i class="fa fa-unlock-alt"></i> @lang('messages.change_password')</a></li>
                                <li class="divider"></li>
                                <li><a href="{{ url('logout') }}"><i class="fa fa-sign-out"></i> @lang('messages.logout')</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    <div class="container1" style=" height: 100%">
        @yield('content')
    </div>
</div>
<div id="footer">
    <div class="container"  style="padding-right: 35px">
        <div class="row">
            <div class="col-md-5">
                <p class="text-muted">&copy; {{date("Y")}} - <a style="text-decoration: none !important;color: #777777" target="_blank" href="{{ env('FOOTER_LINK', '') }}">{{ env('FOOTER_TEXT', 'SEPEHR') }}</a></p>
            </div>
            <div class="col-md-7">
                <div class=" pull-right">
                    <p class="text-muted small">@lang('messages.ver'): {{ env('VERSION', '?') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@if(!$main->ip_is_private($_SERVER['REMOTE_ADDR']))
<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', '{{ Config::get('leech.GA') }}', 'auto');
    ga('send', 'pageview');
</script>
@endif

</body>
</html>
