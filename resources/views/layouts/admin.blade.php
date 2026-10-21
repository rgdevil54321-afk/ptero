<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ $appName ?? config('app.name', 'Pterodactyl') }} - @yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="_token" content="{{ csrf_token() }}">

        @php
            $themeMeta = null;
            $themeVars = null;
            $themeColor = '#df3050';
            $adminTheme = 'default';
            $appName = config('app.name', 'Pterodactyl');
            try {
                $settingsRepository = app(\Pterodactyl\Repositories\Eloquent\SettingsRepository::class);
                $raw = $settingsRepository->get('settings::app:theme:hyperv2', '{}');
                $decoded = json_decode($raw ?: '{}', true, 512, JSON_THROW_ON_ERROR);
                $themeMeta = $decoded['site']['meta'] ?? null;
                $themeVars = $decoded['variables'] ?? null;
                $themeColor = $themeMeta && !empty($themeMeta['color'])
                    ? $themeMeta['color']
                    : (!empty($themeVars['--hyper-primary']) ? $themeVars['--hyper-primary'] : '#df3050');
                
                $adminTheme = $settingsRepository->get('settings::app:admin_theme', 'default');
                $appName = $settingsRepository->get('settings::app:name', $appName);
            } catch (\Throwable $e) {}
        @endphp

        @if($themeMeta && isset($themeMeta['faviconUrl']) && !empty($themeMeta['faviconUrl']))
            <link rel="icon" href="{{ $themeMeta['faviconUrl'] }}">
        @else
            <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
            <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
            <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
            <link rel="manifest" href="/favicons/manifest.json">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
            <link rel="shortcut icon" href="/favicons/favicon.ico">
            <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        @endif

        <meta name="theme-color" content="{{ $themeColor }}">

        @php
            $fontsV = @filemtime(public_path('assets/css/fonts.css')) ?: time();
        @endphp
        <link rel="stylesheet" href="/assets/css/fonts.css?t={{ $fontsV }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">


        @include('layouts.scripts')

        @section('scripts')
            {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/colors/skin-blue.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
            {!! Theme::css('css/pterodactyl.css?t={cache-version}') !!}
            @if($adminTheme === 'hyperv2')
                @php
                    $hyperCssV  = @filemtime(public_path('assets/css/hyper.css')) ?: time();
                    $adminHyperV = @filemtime(public_path('themes/pterodactyl/css/admin-hyper.css')) ?: time();
                @endphp
                <link rel="stylesheet" href="/assets/css/hyper.css?t={{ $hyperCssV }}">
                {!! Theme::css('css/admin-hyper.css?t=' . $adminHyperV) !!}
            @endif


            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        @show

        @php
            $settingsRepository = app(\Pterodactyl\Repositories\Eloquent\SettingsRepository::class);
            $raw = $settingsRepository->get('settings::app:theme:hyperv2', '{}');
            $decoded = json_decode($raw ?: '{}', true, 512, JSON_THROW_ON_ERROR);
            $themeVars = $decoded['variables'] ?? null;
            $themeEnforce = $decoded['enforce'] ?? false;
        @endphp

        @if($themeVars && is_array($themeVars) && count($themeVars) > 0)
            <style id="hyper-theme-vars">
                :root {
                    @foreach($themeVars as $key => $value)
                        @if(Str::startsWith($key, '--hyper-') && !empty($value) && !Str::endsWith($key, '-rgb'))
                            {{ $key }}: {{ $value }}{{ $themeEnforce ? ' !important' : '' }};
                        @endif
                    @endforeach
                    @if(isset($themeVars['--hyper-primary']) && preg_match('/^#([a-f0-9]{6})(?:[a-f0-9]{2})?$/i', $themeVars['--hyper-primary'], $m))
                        --hyper-primary-rgb: {{ hexdec(substr($m[1], 0, 2)) }}, {{ hexdec(substr($m[1], 2, 2)) }}, {{ hexdec(substr($m[1], 4, 2)) }}{{ $themeEnforce ? ' !important' : '' }};
                    @endif
                    @if(isset($themeVars['--hyper-background']) && preg_match('/^#([a-f0-9]{6})(?:[a-f0-9]{2})?$/i', $themeVars['--hyper-background'], $m))
                        --hyper-background-rgb: {{ hexdec(substr($m[1], 0, 2)) }}, {{ hexdec(substr($m[1], 2, 2)) }}, {{ hexdec(substr($m[1], 4, 2)) }}{{ $themeEnforce ? ' !important' : '' }};
                    @endif
                }
            </style>
            @if(isset($themeVars['--hyper-font-url']) && !empty($themeVars['--hyper-font-url']))
                <link rel="stylesheet" href="{{ $themeVars['--hyper-font-url'] }}" media="print" onload="this.media='all'">
                <noscript>
                    <link rel="stylesheet" href="{{ $themeVars['--hyper-font-url'] }}">
                </noscript>
            @endif
        @endif
        <style>
            :root {
                --hyper-font-family: 'Poppins', sans-serif !important;
            }
            body *:not(.fa):not(.fas):not(.far):not(.fab):not(.glyphicon):not([class^="ion-"]):not([class*=" ion-"]):not(.xterm):not(.xterm *),
            body {
                font-family: 'Poppins', sans-serif !important;
            }
        </style>
    </head>
    <body class="hold-transition skin-blue fixed sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ route('index') }}" class="logo">
                    <span>{{ $appName ?? config('app.name', 'Pterodactyl') }}</span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="user-menu">
                                <a href="{{ route('account') }}">
                                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{ Auth::user()->name_first }} {{ Auth::user()->name_last }}</span>
                                </a>
                            </li>
                            <li>
                                <li><a href="{{ route('index') }}" data-toggle="tooltip" data-placement="bottom" title="Exit Admin Control"><i class="fa fa-server"></i></a></li>
                            </li>
                            <li>
                                <li><a href="{{ route('auth.logout') }}" id="logoutButton" data-toggle="tooltip" data-placement="bottom" title="Logout"><i class="fa fa-sign-out"></i></a></li>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        <li class="header">BASIC ADMINISTRATION</li>
                        <li class="{{ Route::currentRouteName() !== 'admin.index' ?: 'active' }}">
                            <a href="{{ route('admin.index') }}">
                                <i class="fa fa-home"></i> <span>Overview</span>
                            </a>
                        </li>
                        @if(Auth::user()->root_admin)
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.statistics') ?: 'active' }}">
                            <a href="{{ route('admin.statistics') }}">
                                <i class="fa fa-cogs"></i> <span>Statistics</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.settings.general'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.settings') ?: 'active' }}">
                            <a href="{{ route('admin.settings')}}">
                                <i class="fa fa-wrench"></i> <span>Settings</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.api.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.api') ?: 'active' }}">
                            <a href="{{ route('admin.api.index')}}">
                                <i class="fa fa-gamepad"></i> <span>Application API</span>
                            </a>
                        </li>
                        @endif
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.audit-log') ?: 'active' }}">
                            <a href="{{ route('admin.audit-log') }}">
                                <i class="fa fa-list-alt"></i> <span>Audit Log</span>
                            </a>
                        </li>
                        @if(Auth::user()->root_admin)
                        <li class="{{ starts_with((string) Route::currentRouteName(), 'admin.playit') ? 'active' : '' }}">
                            <a href="{{ Route::has('admin.playit') ? route('admin.playit') : url('/admin/playit') }}">
                                <i class="fa fa-magic"></i> <span>Playit</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin)
                        <li class="{{ starts_with((string) Route::currentRouteName(), 'admin.local') ? 'active' : '' }}">
                            <a href="{{ Route::has('admin.local') ? route('admin.local') : url('/admin/local') }}">
                                <i class="fa fa-server"></i> <span>Local Servers</span>
                            </a>
                        </li>
                        @endif
                        @php($showPanelLogs = Auth::user()->root_admin)
                        @if($showPanelLogs)
                        @php($panelLogsRouteAvailable = Route::has('admin.panel-logs'))
                        <li class="{{ $panelLogsRouteAvailable && starts_with((string) Route::currentRouteName(), 'admin.panel-logs') ? 'active' : '' }}">
                            <a href="{{ $panelLogsRouteAvailable ? route('admin.panel-logs') : url('/admin/panel-logs') }}">
                                <i class="fa fa-file-text-o"></i> <span>Panel Logs</span>
                            </a>
                        </li>
                        @endif
                        <li class="header">MANAGEMENT</li>
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.servers.database'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.databases') ?: 'active' }}">
                            <a href="{{ route('admin.databases') }}">
                                <i class="fa fa-database"></i> <span>Databases</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.locations.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.locations') ?: 'active' }}">
                            <a href="{{ route('admin.locations') }}">
                                <i class="fa fa-globe"></i> <span>Locations</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nodes.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nodes') ?: 'active' }}">
                            <a href="{{ route('admin.nodes') }}">
                                <i class="fa fa-sitemap"></i> <span>Nodes</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.servers.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.servers') ?: 'active' }}">
                            <a href="{{ route('admin.servers') }}">
                                <i class="fa fa-server"></i> <span>Servers</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.users.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.users') ?: 'active' }}">
                            <a href="{{ route('admin.users') }}">
                                <i class="fa fa-users"></i> <span>Users</span>
                            </a>
                        </li>
                        @endif
                        <li class="header">SERVICE MANAGEMENT</li>
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.servers.mount'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.mounts') ?: 'active' }}">
                            <a href="{{ route('admin.mounts') }}">
                                <i class="fa fa-magic"></i> <span>Mounts</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nests') ?: 'active' }}">
                            <a href="{{ route('admin.nests') }}">
                                <i class="fa fa-th-large"></i> <span>Nests</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    @yield('content-header')
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    There was an error validating the data provided.<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @foreach (Alert::getMessages() as $type => $messages)
                                @foreach ($messages as $message)
                                    <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                        {{ $message }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right small text-gray" style="margin-right:10px;margin-top:-7px;">
                    <strong><i class="fa fa-fw {{ $appIsGit ? 'fa-git-square' : 'fa-code-fork' }}"></i></strong> {{ $appVersion }}<br />
                    <strong><i class="fa fa-fw fa-clock-o"></i></strong> {{ round(microtime(true) - (defined('LARAVEL_START') ? LARAVEL_START : ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))), 3) }}s
                </div>
                Copyright &copy; 2015 - {{ date('Y') }} <a href="https://pterodactyl.io/">Pterodactyl Software</a>.
            </footer>
        </div>
        @section('footer-scripts')
            <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
            <script>keyboardeventKeyPolyfill.polyfill();</script>

            {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
            {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
            <script src="/js/autocomplete.js" type="application/javascript"></script>

            @if(Auth::user()->root_admin)
                <script>
                    $('#logoutButton').on('click', function (event) {
                        event.preventDefault();

                        var that = this;
                        swal({
                            title: 'Do you want to log out?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d9534f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Log out'
                        }, function () {
                             $.ajax({
                                type: 'POST',
                                url: '{{ route('auth.logout') }}',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },complete: function () {
                                    window.location.href = '{{route('auth.login')}}';
                                }
                        });
                    });
                });
                </script>
            @endif

            <script>
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                })
            </script>
        @show
        
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/service-worker.js', { scope: '/', updateViaCache: 'none' })
                        .then(function(registration) {
                            registration.update().catch(function() {});

                            fetch('/api/public/pwa/sw-config.js', { cache: 'no-store' })
                                .then(function(response) {
                                    if (!response.ok) {
                                        throw new Error('HTTP ' + response.status);
                                    }
                                    return response.json();
                                })
                                .then(function(config) {
                                    var postConfig = function() {
                                        var worker = registration.active || navigator.serviceWorker.controller;
                                        if (worker) {
                                            worker.postMessage({
                                                type: 'PWA_CONFIG',
                                                config: config,
                                            });
                                        }
                                    };

                                    postConfig();

                                    if (registration.installing) {
                                        registration.installing.addEventListener('statechange', postConfig);
                                    }
                                })
                                .catch(function() {});
                        })
                        .catch(function() {});
                });
            }
        </script>
    </body>
</html>
