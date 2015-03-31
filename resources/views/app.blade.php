<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Sepehr Mohaghegh | BECCA4EVA@live.com">
    <title>{{ Lang::get('messages.title') }}</title>
    <script src="{{ asset('/js/pace.min.js') }}"></script>
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/bootstrap-theme.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/main.css') }}" rel="stylesheet">
    <link href="favicon.ico?v=1" type="image/x-icon" rel="favicon">
    <link href="{{ asset('/css/bootstrap-combobox.css') }}" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="{{ asset('/js/html5shiv.min.js') }}"></script>
    <script src="{{ asset('/js/respond.js') }}"></script>

    <![endif]-->
    <script src="{{ asset('/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/js/bootbox.min.js') }}"></script>
    <script src="{{ asset('/js/bootstrap-combobox.js') }}"></script>
</head>
<body>
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
                <?php $sin = array('egg', 'mahi', 'quran', 'sabze', 'samanoo', 'seke', 'senjed', 'sham', 'sib', 'sir', 'somagh', 'ayne'); $img = $sin[array_rand($sin)];?>

                <a class="navbar-brand" rel="home" href="https://www.google.com/#q=happy+new+persian+year" title="Happy New Persian Year (1394)">
                    <img style="max-width:50px; margin-top: -17px; margin-right: -10px"
                         src="{{ asset('/img/' . $img . '.png') }}">
                </a>
                <a class="navbar-brand" href="{{ asset('') }}">@lang('messages.mainTitle')</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    @if (!Auth::guest())
                        <li><a href="{{ asset('') }}"><i class="fa fa-download"></i> @lang('messages.home')</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-file-o"></i> @lang('messages.files')<span class="caret"></span></a>
                            <ul class="dropdown-menu bw" role="menu">
                                <li><a href="{{ url('/downloads') }}"><i class="fa fa-tasks"></i> @lang('messages.dl.list')</a></li>
                                <li><a href="{{ url('/files') }}"><i
                                                class="fa fa-list"></i> @lang('messages.files.list')</a></li>
                                <li class="divider"></li>
                                <li><a href="{{ url('/public') }}"><i class="fa fa-globe"></i> @lang('messages.files.public')</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    @if (Auth::guest())
                        <li><a href="{{ url('login') }}">@lang('messages.login')</a></li>
                    @else
                        @if (Auth::user()->role == 2)
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-cog"></i> @lang('messages.tools') <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-header">@lang('messages.user.management')</li>
                                    <li><a href="{{ url('/tools/register') }}"><i class="fa fa-plus"></i> @lang('messages.add.user')</a></li>
                                    <li><a href="{{ url('/tools/users') }}"><i class="fa fa-users"></i> @lang('messages.manage.users')</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ url('/tools/status') }}"><i
                                                    class="fa fa-area-chart"></i> @lang('messages.gband')</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ url('/tools/aria2console') }}"><i class="fa fa-terminal"></i> Aria2
                                            Console</a></li>
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
                                    <a href="{{ url('credit') }}">
                                        <i class="fa fa-usd"></i> @lang('messages.credits'): <span style="margin-top: 2px" class="label{{ Auth::user()->credit / 1024 / 1024 /1024 < 10 ? ' label-danger ': ' label-success '  }}pull-right">{{ round(Auth::user()->credit / 1024 / 1024 /1024,1) }} GB</span>
                                    </a>
                                </li>
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
                <p class="text-muted">&copy; {{date("Y")}} - <a style="text-decoration: none !important;color: #777777" target="_blank" href="{{ Lang::get('messages.cp_link') }}">{{ Lang::get('messages.cp') }}</a></p>
            </div>
            <div class="col-md-7">
                <div class=" pull-right">
                    <p class="text-muted small">@lang('messages.ver'): {{ env('VERSION', '?') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
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
</body>
</html>
