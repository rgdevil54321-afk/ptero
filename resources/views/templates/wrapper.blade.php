<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        @php
            $themeRaw    = '{}';
            $addonsRaw   = '{}';
            $themeMeta   = null;
            $pwaSettings = null;
            $adsSettings = null;
            $themeData   = [];
            $themeDecoded = [];
            $themeVars   = null;
            $themeEnforce = false;
            $addonsDecoded = [];

            $__tScope = request()->getHost();

            $__remember = function (string $key, int $ttlSeconds, callable $callback) {
                return \Illuminate\Support\Facades\Cache::remember(
                    $key,
                    now()->addSeconds($ttlSeconds),
                    $callback
                );
            };

            try {


                $repo = app(\Pterodactyl\Repositories\Eloquent\SettingsRepository::class);
                $themeRaw = $repo->get('settings::app:theme:hyperv2', '{}');
                $themeDecoded = json_decode($themeRaw ?: '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
                $themeMeta    = $themeDecoded['site']['meta'] ?? null;
                $themeVars    = $themeDecoded['variables'] ?? null;
                $themeEnforce = (bool) ($themeDecoded['enforce'] ?? false);
                $themeData    = $themeDecoded;

                $addonsRaw = $repo->get('settings::app:addons:hyperv2', '{}');
                $addonsDecoded = json_decode($addonsRaw ?: '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
                $pwaSettings   = $addonsDecoded['addons']['pwa'] ?? null;
                $adsSettings   = $addonsDecoded['addons']['ads-layout'] ?? null;
            } catch (\Throwable $e) {
            }

            $pwaEnabled = $pwaSettings['enabled'] ?? false;
            $swInlineConfig = [
                'enabled' => (bool) ($pwaSettings['enabled'] ?? false),
                'cacheStrategy' => $pwaSettings['cache_strategy'] ?? 'cache-first',
                'cacheAssets' => (bool) ($pwaSettings['cache_assets'] ?? true),
                'cacheApi' => (bool) ($pwaSettings['cache_api'] ?? false),
                'cacheMaxAge' => ((int) ($pwaSettings['cache_max_age'] ?? 24)) * 60 * 60,
                'offlineEnabled' => (bool) ($pwaSettings['offline_enabled'] ?? true),
                'offlinePageTitle' => $pwaSettings['offline_page_title'] ?? 'You are offline',
                'offlinePageMessage' => $pwaSettings['offline_page_message'] ?? 'Please check your internet connection and try again.',
            ];

            if ($pwaEnabled && !empty($pwaSettings['app_name'])) {
                $appTitle = $pwaSettings['app_name'];
            } elseif ($themeMeta && !empty($themeMeta['title'])) {
                $appTitle = $themeMeta['title'];
            } else {
                $appTitle = config('app.name', 'Pterodactyl');
            }

            $themeColor = $themeMeta && !empty($themeMeta['color'])
                ? $themeMeta['color']
                : (!empty($themeVars['--hyper-primary'])
                    ? $themeVars['--hyper-primary']
                    : (($pwaEnabled && !empty($pwaSettings['theme_color'])) ? $pwaSettings['theme_color'] : '#df3050'));
        @endphp

        <title>{{ $appTitle }}</title>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="robots" content="index, follow">
            <meta name="application-name" content="{{ $appTitle }}">

            @if($themeMeta && isset($themeMeta['faviconUrl']) && !empty($themeMeta['faviconUrl']))
                <link rel="icon" href="{{ $themeMeta['faviconUrl'] }}">
            @else
                <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
                <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
                <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
                <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
                <link rel="shortcut icon" href="/favicons/favicon.ico">
                <meta name="msapplication-config" content="/favicons/browserconfig.xml">
            @endif

            @if($pwaEnabled)
                @if(!\Illuminate\Support\Str::startsWith(request()->path(), 'auth'))
                    <meta name="hyper-pwa-manifest" content="/api/public/pwa/manifest.json?v=3">
                @endif
                <meta name="mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="{{ $pwaSettings['status_bar_style'] ?? 'default' }}">
                <meta name="apple-mobile-web-app-title" content="{{ $pwaSettings['app_short_name'] ?? $appTitle }}">
            @endif

            @if($themeMeta && isset($themeMeta['description']) && !empty($themeMeta['description']))
                <meta name="description" content="{{ Str::limit($themeMeta['description'], 300) }}">
            @endif

            @if($themeMeta && isset($themeMeta['image']) && !empty($themeMeta['image']))
                <meta property="og:image" content="{{ $themeMeta['image'] }}">
                <meta property="og:image:width" content="1200">
                <meta property="og:image:height" content="630">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:image" content="{{ $themeMeta['image'] }}">
            @endif

            @if($themeMeta && isset($themeMeta['title']) && !empty($themeMeta['title']))
                <meta property="og:title" content="{{ $themeMeta['title'] }}">
                <meta name="twitter:title" content="{{ $themeMeta['title'] }}">
            @else
                <meta property="og:title" content="{{ config('app.name', 'Pterodactyl') }}">
                <meta name="twitter:title" content="{{ config('app.name', 'Pterodactyl') }}">
            @endif

            @if($themeMeta && isset($themeMeta['description']) && !empty($themeMeta['description']))
                <meta property="og:description" content="{{ Str::limit($themeMeta['description'], 300) }}">
                <meta name="twitter:description" content="{{ Str::limit($themeMeta['description'], 300) }}">
            @endif

            <meta name="theme-color" content="{{ $themeColor }}">

            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ url()->current() }}">
        @show
        @section('user-data')
            @php
                $__authUser = Auth::user();
                $__vueUserJson = null;
                if ($__authUser) {

                    $__vueUserJson = $__remember(
                        'wrap:vue-user:' . $__tScope . ':' . $__authUser->id,
                        120,
                        function () use ($__authUser) {
                            $__authUser->loadMissing('permissionRole');
                            return json_encode($__authUser->toVueObject(), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
                        }
                    );
                }
                $__siteConfigJson = !empty($siteConfiguration)
                    ? $__remember(
                        'wrap:site-config:' . $__tScope . ':' . (config('app.locale') ?? 'en') . ':' . ($siteConfigurationFingerprint ?? 'none'),
                        300,
                        fn() => json_encode($siteConfiguration, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE)
                    )
                    : null;
            @endphp
            @if($__vueUserJson !== null)
                <script data-cfasync="false">
                    window.PterodactylUser = {!! $__vueUserJson !!};
                </script>
            @endif
            @if($__siteConfigJson !== null)
                <script data-cfasync="false">
                    window.SiteConfiguration = {!! $__siteConfigJson !!};
                </script>
            @endif

            @php
                $__inlineAuthAddonsJson = null;
                $__inlineAuthAddonsForAuth = \Illuminate\Support\Str::startsWith(request()->path(), 'auth')
                    || \Illuminate\Support\Facades\Auth::guest();
                if ($__inlineAuthAddonsForAuth) {
                    try {
                        $__authAddonPayload = app(\Pterodactyl\Services\HyperV2PublicAuthSettingsService::class)
                            ->build(is_array($addonsDecoded) ? $addonsDecoded : []);
                        $__encodedAuthAddonsJson = json_encode(
                            $__authAddonPayload,
                            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                        $__inlineAuthAddonsJson = is_string($__encodedAuthAddonsJson) ? $__encodedAuthAddonsJson : null;
                    } catch (\Throwable $e) {
                    }
                }

                $themeSettingsAddon = $addonsDecoded['addons']['theme-settings'] ?? [];
                $userPermissions = $themeSettingsAddon['userPermissions'] ?? [];
                $defaults = $themeSettingsAddon['defaults'] ?? [];

                if (!isset($themeData['site'])) {
                    $themeData['site'] = [];
                }

                $themeData['site']['userPermissions'] = [
                    'colors' => isset($userPermissions['colors']) ? (bool) $userPermissions['colors'] : true,
                    'background' => isset($userPermissions['background']) ? (bool) $userPermissions['background'] : true,
                    'notifications' => isset($userPermissions['notifications']) ? (bool) $userPermissions['notifications'] : true,
                    'privacy' => isset($userPermissions['privacy']) ? (bool) $userPermissions['privacy'] : true,
                ];
                $themeData['site']['defaults'] = [
                    'privacy' => [
                        'blur' => isset($defaults['privacy']['blur']) ? (bool) $defaults['privacy']['blur'] : false,
                    ],
                    'performance' => [
                        'blurEnabled' => isset($defaults['performance']['blurEnabled']) ? (bool) $defaults['performance']['blurEnabled'] : false,
                        'blurAmount'  => isset($defaults['performance']['blurAmount']) ? (int) $defaults['performance']['blurAmount'] : 0,
                        'radiusAmount' => isset($defaults['performance']['radiusAmount']) ? (int) $defaults['performance']['radiusAmount'] : -1,
                    ],
                ];

                $__inlineThemeJson = null;
                $__inlineThemeForClient = \Illuminate\Support\Str::startsWith(request()->path(), 'auth')
                    || \Illuminate\Support\Facades\Auth::guest()
                    || \Illuminate\Support\Facades\Auth::check();
                if ($__inlineThemeForClient) {
                    $__themeSite = is_array($themeData['site'] ?? null) ? $themeData['site'] : [];
                    $__themeSidebar = is_array($__themeSite['sidebar'] ?? null) ? $__themeSite['sidebar'] : [];
                    $__inlineThemeData = [
                        'enforce' => (bool) ($themeData['enforce'] ?? false),
                        'variables' => is_array($themeData['variables'] ?? null) ? $themeData['variables'] : [],
                        'site' => [
                            'meta' => is_array($__themeSite['meta'] ?? null) ? $__themeSite['meta'] : null,
                            'logos' => is_array($__themeSite['logos'] ?? null) ? $__themeSite['logos'] : null,
                            'buttons' => is_array($__themeSite['buttons'] ?? null) ? $__themeSite['buttons'] : [],
                            'auth' => is_array($__themeSite['auth'] ?? null) ? $__themeSite['auth'] : [],
                            'background' => is_array($__themeSite['background'] ?? null) ? $__themeSite['background'] : [],
                            'sidebar' => [
                                'expanded' => isset($__themeSidebar['expanded']) ? (bool) $__themeSidebar['expanded'] : false,
                                'layout' => in_array(($__themeSidebar['layout'] ?? 'hyper-default'), ['hyper-default', 'hyper-minimal'], true)
                                    ? ($__themeSidebar['layout'] ?? 'hyper-default')
                                    : 'hyper-default',
                                'collapseMode' => in_array(($__themeSidebar['collapseMode'] ?? 'collapsible'), ['collapsible', 'open', 'closed'], true)
                                    ? ($__themeSidebar['collapseMode'] ?? 'collapsible')
                                    : 'collapsible',
                                'activeButtonStyle' => in_array(($__themeSidebar['activeButtonStyle'] ?? 'default'), ['default', 'solid-accent', 'soft-fill', 'icon-accent', 'accent-pill', 'soft-pill', 'gradient-accent', 'gradient-clean'], true)
                                    ? ($__themeSidebar['activeButtonStyle'] ?? 'default')
                                    : 'default',
                                'activeButtonFullCover' => isset($__themeSidebar['activeButtonFullCover']) ? (bool) $__themeSidebar['activeButtonFullCover'] : false,
                                'categoryLayout' => in_array(($__themeSidebar['categoryLayout'] ?? null), ['default', 'compact', 'hidden', 'text'], true)
                                    ? $__themeSidebar['categoryLayout']
                                    : (!empty($__themeSidebar['hideCategories']) ? 'hidden' : 'default'),
                                'floatingLayout' => !empty($__themeSidebar['floatingLayout']),
                                'fontSize' => isset($__themeSidebar['fontSize']) ? max(0, min(24, (int) $__themeSidebar['fontSize'])) : 0,
                                'itemPadding' => isset($__themeSidebar['itemPadding']) ? max(-1, min(24, (int) $__themeSidebar['itemPadding'])) : -1,
                                'itemSpacing' => isset($__themeSidebar['itemSpacing']) ? max(-1, min(24, (int) $__themeSidebar['itemSpacing'])) : -1,
                                'itemHeight' => isset($__themeSidebar['itemHeight']) ? max(0, min(80, (int) $__themeSidebar['itemHeight'])) : 0,
                                'hideCategories' => isset($__themeSidebar['hideCategories']) ? (bool) $__themeSidebar['hideCategories'] : false,
                                'serverNavigationMode' => in_array(($__themeSidebar['serverNavigationMode'] ?? 'default'), ['default', 'sidebar', 'navbar'], true)
                                    ? ($__themeSidebar['serverNavigationMode'] ?? 'default')
                                    : 'default',
                                'serverNavigationSticky' => array_key_exists('serverNavigationSticky', $__themeSidebar)
                                    ? (bool) $__themeSidebar['serverNavigationSticky']
                                    : true,
                                'category_order' => isset($__themeSidebar['category_order']) && is_array($__themeSidebar['category_order']) ? $__themeSidebar['category_order'] : [],
                                'button_order' => isset($__themeSidebar['button_order']) && is_array($__themeSidebar['button_order']) ? $__themeSidebar['button_order'] : [],
                                'custom_categories' => isset($__themeSidebar['custom_categories']) && is_array($__themeSidebar['custom_categories']) ? $__themeSidebar['custom_categories'] : [],
                                'button_category_overrides' => isset($__themeSidebar['button_category_overrides']) && is_array($__themeSidebar['button_category_overrides']) ? $__themeSidebar['button_category_overrides'] : [],
                                'showPageTitle' => array_key_exists('showPageTitle', $__themeSidebar) ? (bool) $__themeSidebar['showPageTitle'] : true,
                            ],
                            'serverImages' => is_array($__themeSite['serverImages'] ?? null) ? $__themeSite['serverImages'] : [],
                            'serverLayout' => is_array($__themeSite['serverLayout'] ?? null) ? $__themeSite['serverLayout'] : [],
                            'serverCardView' => is_array($__themeSite['serverCardView'] ?? null) ? $__themeSite['serverCardView'] : [],
                            'serverRowVisibility' => is_array($__themeSite['serverRowVisibility'] ?? null) ? $__themeSite['serverRowVisibility'] : [],
                            'workspace' => [
                                'enabled' => array_key_exists('enabled', $__themeSite['workspace'] ?? [])
                                    ? (bool) $__themeSite['workspace']['enabled']
                                    : true,
                            ],
                            'winterMode' => is_array($__themeSite['winterMode'] ?? null) ? $__themeSite['winterMode'] : null,
                            'animations' => is_array($__themeSite['animations'] ?? null) ? $__themeSite['animations'] : null,
                            'copyright' => is_array($__themeSite['copyright'] ?? null) ? $__themeSite['copyright'] : null,
                            'defaults' => is_array($__themeSite['defaults'] ?? null) ? $__themeSite['defaults'] : [],
                            'userPermissions' => is_array($__themeSite['userPermissions'] ?? null) ? $__themeSite['userPermissions'] : [],
                        ],
                        'updated_at' => $themeData['updated_at'] ?? null,
                    ];
                    $__stripInlineTheme = function (array $data) use (&$__stripInlineTheme): array {
                        $clean = [];
                        foreach ($data as $key => $value) {
                            if (is_array($value)) {
                                $nested = $__stripInlineTheme($value);
                                if (!empty($nested)) {
                                    $clean[$key] = $nested;
                                }
                            } elseif ($value !== null && $value !== '') {
                                $clean[$key] = $value;
                            }
                        }
                        return $clean;
                    };
                    $__inlineThemeData = $__stripInlineTheme($__inlineThemeData);

                    $__encodedThemeJson = json_encode(
                        $__inlineThemeData,
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                    $__inlineThemeJson = is_string($__encodedThemeJson) ? $__encodedThemeJson : null;
                }
            @endphp
            <script data-cfasync="false">
                (function () {
                    try {
                        var params = new URLSearchParams(window.location.search);
                        if (params.get('hyper_editor_preview') !== '1') {
                            return;
                        }

                        var raw = window.sessionStorage && window.sessionStorage.getItem('hyperv2:editor-preview-theme');
                        if (!raw) {
                            return;
                        }

                        var parsed = JSON.parse(raw);
                        var theme = parsed && parsed.theme ? parsed.theme : parsed;
                        if (theme && typeof theme === 'object') {
                            window.HyperV2Theme = theme;

                            var vars = theme.variables || {};
                            var root = document.documentElement;
                            Object.keys(vars).forEach(function (key) {
                                if (key.indexOf('--hyper-') === 0 && typeof vars[key] === 'string') {
                                    root.style.setProperty(key, vars[key], 'important');
                                }
                            });
                        }

                        if (parsed && parsed.addons && typeof parsed.addons === 'object') {
                            window.HyperV2AuthAddons = {
                                addons: parsed.addons,
                                updated_at: null,
                                app_url: window.location.origin
                            };
                        }
                    } catch (e) {
                    }
                })();
            </script>
            @if($__inlineThemeJson !== null)
                <script data-cfasync="false">
                    if (!window.HyperV2Theme) {
                        window.HyperV2Theme = {!! $__inlineThemeJson !!};
                    }
                </script>
            @endif
            @if($__inlineAuthAddonsJson !== null)
                <script data-cfasync="false">
                    if (!window.HyperV2AuthAddons) {
                        window.HyperV2AuthAddons = {!! $__inlineAuthAddonsJson !!};
                    }
                </script>
            @endif
            <script data-cfasync="false">
                window.HyperV2AddonVersion = {!! json_encode($addonsDecoded['updated_at'] ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
            </script>
            @php
                $languageAddon = $addonsDecoded['addons']['LanguageTranslations'] ?? [];
                $guestDefaultLang = null;
                if (\Illuminate\Support\Facades\Auth::guest() && !empty($languageAddon['enabled']) && !empty($languageAddon['defaultLanguage'])) {
                    $guestDefaultLang = (string) $languageAddon['defaultLanguage'];
                }

                $userLang = auth()->user()?->language
                    ?? session('locale')
                    ?? $guestDefaultLang
                    ?? config('app.locale', 'en');
                $userLang = preg_replace('/[^a-z0-9_-]/i', '', $userLang) ?: 'en';
                $langPath = public_path("DGEN/themes/Hyperv2/lang/{$userLang}.json");
                if (!file_exists($langPath)) {
                    $userLang = 'en';
                    $langPath = public_path("DGEN/themes/Hyperv2/lang/en.json");
                }

                $langVersion = file_exists($langPath) ? (string) filemtime($langPath) : '0';
                $inlineDictJson = $__remember(
                    "wrap:lang:dict:full:v4:{$userLang}:{$langVersion}",
                    3600,
                    function () use ($langPath) {
                        if (!file_exists($langPath)) return null;
                        $data = json_decode(file_get_contents($langPath), true);
                        if (!is_array($data)) return null;

                        return json_encode(
                            $data,
                            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                    }
                );
                $inlineLocaleResourcesJson = null;

            @endphp
            <script data-cfasync="false">
                window.__I18N_LANG__ = {!! json_encode($userLang) !!};
            </script>

            @if($inlineDictJson !== null)
            <script data-cfasync="false">
                window.__I18N_DICT__ = {!! $inlineDictJson !!};
                window.__I18N_LANG_INLINE__ = {!! json_encode($userLang, JSON_HEX_TAG) !!};
            </script>
            @endif

            @if($inlineLocaleResourcesJson !== null)
            <script data-cfasync="false">
                window.__I18N_RESOURCES__ = {!! $inlineLocaleResourcesJson !!};
            </script>
            @endif

            @php
                $__dashboardServersJson = null;
                $__dashboardServersCacheKeyJson = null;
                if (\Illuminate\Support\Facades\Auth::check() && trim(request()->path(), '/') === '') {
                    $__requestedDashboardPage = (int) request()->query('page', 1);
                    if ($__requestedDashboardPage <= 1) {
                        $__dashboardServerRequestParams = [
                            'filter[*]' => null,
                            'page' => 1,
                            'per_page' => 10,
                            'sort' => 'nest_id',
                            'type' => null,
                            'view' => 'dashboard',
                        ];
                        $__dashboardServerCachePairs = [];
                        $__dashboardServerCacheParams = $__dashboardServerRequestParams;
                        ksort($__dashboardServerCacheParams);
                        foreach ($__dashboardServerCacheParams as $__dashboardServerCacheParamKey => $__dashboardServerCacheParamValue) {
                            $__dashboardServerCachePairs[] = [$__dashboardServerCacheParamKey, $__dashboardServerCacheParamValue];
                        }
                        $__dashboardServerCacheKey = json_encode($__dashboardServerCachePairs, JSON_UNESCAPED_SLASHES);
                        $__dashboardServersCacheKeyJson = json_encode($__dashboardServerCacheKey, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES);

                        try {
                            $__dashboardServersPayload = $__remember(
                                'wrap:dashboard:servers:v1:' . $__tScope . ':' . \Illuminate\Support\Facades\Auth::id(),
                                20,
                                function () use ($__dashboardServerRequestParams) {
                                    $__dashboardServerRequest = request()->duplicate();
                                    $__dashboardServerRequest->query->replace($__dashboardServerRequestParams);
                                    $__dashboardServerRequest->setUserResolver(fn() => \Illuminate\Support\Facades\Auth::user());
                                    if (method_exists(request(), 'getRouteResolver')) {
                                        $__dashboardServerRequest->setRouteResolver(request()->getRouteResolver());
                                    }

                                    return app(\Pterodactyl\Http\Controllers\Api\Client\ClientController::class)
                                        ->dashboardBootstrap($__dashboardServerRequest);
                                }
                            );

                            if (is_array($__dashboardServersPayload)) {
                                $__encodedDashboardServersJson = json_encode(
                                    $__dashboardServersPayload,
                                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                );
                                $__dashboardServersJson = is_string($__encodedDashboardServersJson) ? $__encodedDashboardServersJson : null;
                            }
                        } catch (\Throwable $e) {
                            $__dashboardServersJson = null;
                            $__dashboardServersCacheKeyJson = null;
                        }
                    }
                }
            @endphp
            @if($__dashboardServersJson !== null && $__dashboardServersCacheKeyJson !== null)
            <script data-cfasync="false">
                window.__HYPER_DASHBOARD_SERVERS__ = {
                    cacheKey: {!! $__dashboardServersCacheKeyJson !!},
                    data: {!! $__dashboardServersJson !!}
                };
            </script>
            @endif

            @if(isset($errors) && $errors->any())
                <script data-cfasync="false">
                    window.__SERVER_ERRORS__ = {!! json_encode($errors->all()) !!};
                </script>
            @endif
        @show

        @php
            // LCP optimization for auth: preload the exact image that can become
            // the first viewport's LCP. Split auth layouts use the media <img>;
            // default layouts can still use the configured background image.
            $__authRoutePath = trim(request()->path(), '/');
            $__authShellKind = match (true) {
                $__authRoutePath === 'auth' || $__authRoutePath === 'auth/login' => 'login',
                $__authRoutePath === 'auth/register' => 'register',
                $__authRoutePath === 'auth/password' => 'forgot',
                default => null,
            };
            $__isAuthLoginRoute = $__authShellKind === 'login';
            // Keep the auth shell markup disabled for now. Auth routes still use
            // the auth-only React app, but we no longer show the temporary Blade
            // form while slow connections are loading the real page.
            $__hasAuthShell = false;
            $__onAuthView = \Illuminate\Support\Str::startsWith(request()->path(), 'auth')
                || \Illuminate\Support\Facades\Auth::guest();
            $__authExp = $__onAuthView && is_array($themeDecoded['site']['auth'] ?? null)
                ? $themeDecoded['site']['auth']
                : [];
            $__safeAuthAssetUrl = function ($url): ?string {
                if (!is_string($url)) return null;
                $url = trim($url);
                if ($url === '') return null;
                if (preg_match('#^https?://#i', $url)) return $url;
                if (str_starts_with($url, '/') && !str_starts_with($url, '//')) return $url;
                return null;
            };
            $__authSplitImageUrl = null;
            if (in_array(($__authExp['layout'] ?? null), ['split', 'full-split'], true)
                && ($__authExp['splitMediaType'] ?? 'image') === 'image') {
                $__authSplitImageUrl = $__safeAuthAssetUrl($__authExp['splitMediaUrl'] ?? null);
            }

            $__authLogoImageUrl = null;
            $__authLogoPlacement = ($__authExp['logoPlacement'] ?? 'navigation') === 'form' ? 'form' : 'navigation';
            $__authLogoVisible = $__authLogoPlacement === 'form' || empty($__authExp['disableAuthNavigation']);
            if ($__onAuthView && $__authLogoVisible) {
                $__siteLogosForPreload = is_array($themeDecoded['site']['logos'] ?? null)
                    ? $themeDecoded['site']['logos']
                    : [];
                $__authLogosForPreload = !empty($__authExp['useCustomAuthLogo']) && is_array($__authExp['logos'] ?? null)
                    ? $__authExp['logos']
                    : $__siteLogosForPreload;
                if (($__authLogosForPreload['largeMode'] ?? 'image') !== 'text') {
                    $__authLogoImageUrl = $__safeAuthAssetUrl($__authLogosForPreload['largeUrl'] ?? null);
                }
            }

            $__authBgUrl = null;
            $__authBgOverlay = false;
            if ($__onAuthView && empty($__authExp['disableBackgroundImage'])) {
                $__authBg = $__authExp['background']['custom'] ?? null;
                if (!empty($__authBg['enabled']) && !empty($__authBg['url'])
                    && ($__authBg['type'] ?? 'image') === 'image') {
                    $__authBgUrl = $__safeAuthAssetUrl($__authBg['url']);
                    $__authBgOverlay = (($__authBg['overlay'] ?? false) === true);
                } else {
                    $__siteBg = $themeDecoded['site']['background']['custom'] ?? null;
                    if (!empty($__siteBg['enabled']) && !empty($__siteBg['url'])
                        && ($__siteBg['type'] ?? 'image') === 'image') {
                        $__authBgUrl = $__safeAuthAssetUrl($__siteBg['url']);
                        $__authBgOverlay = (($__siteBg['overlay'] ?? false) === true);
                    }
                }
            }

            $__authLcpImageUrl = $__authSplitImageUrl ?: $__authBgUrl;
        @endphp
        @if($__authLcpImageUrl)
            <link rel="preload" as="image" href="{{ $__authLcpImageUrl }}" fetchpriority="high" @if($__authSplitImageUrl || $__authLogoImageUrl) media="(min-width: 1024px)" @endif>
        @endif
        @if($__authLogoImageUrl)
            <link rel="preload" as="image" href="{{ $__authLogoImageUrl }}" fetchpriority="high" media="(max-width: 1023px)">
        @endif
        @php
            $__dashboardLcpImageUrl = null;
            $__onDashboardView = trim(request()->path(), '/') === '' && \Illuminate\Support\Facades\Auth::check();
            if ($__onDashboardView) {
                $__serverLayoutSettings = is_array($themeDecoded['site']['serverLayout'] ?? null)
                    ? $themeDecoded['site']['serverLayout']
                    : [];
                $__serverSorterAddon = is_array($addonsDecoded['addons']['ServerSorter'] ?? null)
                    ? $addonsDecoded['addons']['ServerSorter']
                    : [];
                $__serverImages = is_array($themeDecoded['site']['serverImages'] ?? null)
                    ? $themeDecoded['site']['serverImages']
                    : [];

                $__allowedServerLayouts = ['card', 'banner', 'row'];
                $__forcedLayout = !empty($__serverLayoutSettings['force'])
                    && in_array(($__serverLayoutSettings['layout'] ?? ''), $__allowedServerLayouts, true);
                $__defaultServerLayout = !empty($__serverSorterAddon['enabled'])
                    && in_array(($__serverSorterAddon['defaultServerDesign'] ?? ''), $__allowedServerLayouts, true)
                        ? $__serverSorterAddon['defaultServerDesign']
                        : 'card';
                $__dashboardServerLayout = $__forcedLayout
                    ? $__serverLayoutSettings['layout']
                    : $__defaultServerLayout;
                $__dashboardFirstServerAttributes = null;
                if (isset($__dashboardServersPayload['data'][0]['attributes'])
                    && is_array($__dashboardServersPayload['data'][0]['attributes'])) {
                    $__dashboardFirstServerAttributes = $__dashboardServersPayload['data'][0]['attributes'];
                }
                $__dashboardFirstEggId = (int) ($__dashboardFirstServerAttributes['egg_id'] ?? 0);
                $__dashboardFirstNestId = (int) ($__dashboardFirstServerAttributes['nest_id'] ?? 0);
                $__dashboardFirstEggSlug = strtolower(preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($__dashboardFirstServerAttributes['egg_name'] ?? 'default')));
                $__dashboardFirstEggSlug = trim($__dashboardFirstEggSlug, '-') ?: 'default';
                $__dashboardFirstNestSlug = strtolower(preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($__dashboardFirstServerAttributes['nest_name'] ?? 'default')));
                $__dashboardFirstNestSlug = trim($__dashboardFirstNestSlug, '-') ?: 'default';

                $__serverImageEntry = function (array $collection, int $id, string $kind): ?string {
                    if ($id <= 0) {
                        return null;
                    }
                    $entry = $collection[$id] ?? $collection[(string) $id] ?? null;
                    return is_array($entry) && !empty($entry[$kind]) ? (string) $entry[$kind] : null;
                };
                $__serverImageDefault = function (array $images, string $kind): ?string {
                    $default = $images['default'] ?? null;
                    return is_array($default) && !empty($default[$kind]) ? (string) $default[$kind] : null;
                };

                if ($__dashboardServerLayout === 'banner') {
                    $__dashboardLcpImageUrl =
                        $__serverImageEntry($__serverImages['eggs'] ?? [], $__dashboardFirstEggId, 'banner')
                        ?: $__serverImageEntry($__serverImages['nests'] ?? [], $__dashboardFirstNestId, 'banner')
                        ?: $__serverImageDefault($__serverImages, 'banner')
                        ?: '/DGEN/themes/Hyperv2/server/banner/' . $__dashboardFirstNestSlug . '.webp';
                } elseif ($__dashboardServerLayout === 'row') {
                    $__dashboardLcpImageUrl =
                        $__serverImageEntry($__serverImages['eggs'] ?? [], $__dashboardFirstEggId, 'row')
                        ?: $__serverImageEntry($__serverImages['nests'] ?? [], $__dashboardFirstNestId, 'row')
                        ?: $__serverImageEntry($__serverImages['eggs'] ?? [], $__dashboardFirstEggId, 'banner')
                        ?: $__serverImageEntry($__serverImages['nests'] ?? [], $__dashboardFirstNestId, 'banner')
                        ?: $__serverImageDefault($__serverImages, 'row')
                        ?: $__serverImageDefault($__serverImages, 'banner')
                        ?: '/DGEN/themes/Hyperv2/server/banner/' . $__dashboardFirstNestSlug . '.webp';
                } else {
                    $__dashboardLcpImageUrl =
                        $__serverImageEntry($__serverImages['eggs'] ?? [], $__dashboardFirstEggId, 'card')
                        ?: $__serverImageEntry($__serverImages['nests'] ?? [], $__dashboardFirstNestId, 'card')
                        ?: $__serverImageDefault($__serverImages, 'card')
                        ?: '/DGEN/themes/Hyperv2/server/card/' . $__dashboardFirstEggSlug . '.webp';
                }

                $__dashboardLcpImageUrl = $__safeAuthAssetUrl($__dashboardLcpImageUrl);
            }
        @endphp
        @if($__dashboardLcpImageUrl)
            <link rel="preload" as="image" href="{{ $__dashboardLcpImageUrl }}" fetchpriority="high">
        @endif
        @php
            $__serverRouteLcpImageUrl = null;
            $__serverRoutePath = trim(request()->path(), '/');
            if (!$__onDashboardView
                && \Illuminate\Support\Facades\Auth::check()
                && preg_match('#^server/(?:console/)?([a-zA-Z0-9-]+)(?:/|$)#', $__serverRoutePath, $__serverRouteMatch)) {
                $__serverRouteIdentifier = preg_replace('/[^a-zA-Z0-9-]/', '', $__serverRouteMatch[1] ?? '');
                if (is_string($__serverRouteIdentifier) && $__serverRouteIdentifier !== '') {
                    try {
                        $__serverRouteServer = $__remember(
                            'wrap:server:lcp:v1:' . $__tScope . ':' . $__serverRouteIdentifier,
                            60,
                            function () use ($__serverRouteIdentifier) {
                                return \Pterodactyl\Models\Server::query()
                                    ->select(['id', 'uuid', 'egg_id', 'nest_id'])
                                    ->with(['egg:id,name', 'nest:id,name'])
                                    ->where('uuid', 'like', $__serverRouteIdentifier . '%')
                                    ->first();
                            }
                        );

                        if ($__serverRouteServer) {
                            $__serverImages = is_array($themeDecoded['site']['serverImages'] ?? null)
                                ? $themeDecoded['site']['serverImages']
                                : [];
                            $__serverImageEntry = function (array $collection, int $id, string $kind): ?string {
                                if ($id <= 0) {
                                    return null;
                                }
                                $entry = $collection[$id] ?? $collection[(string) $id] ?? null;
                                return is_array($entry) && !empty($entry[$kind]) ? (string) $entry[$kind] : null;
                            };
                            $__serverImageDefault = function (array $images, string $kind): ?string {
                                $default = $images['default'] ?? null;
                                return is_array($default) && !empty($default[$kind]) ? (string) $default[$kind] : null;
                            };
                            $__serverRouteEggId = (int) ($__serverRouteServer->egg_id ?? 0);
                            $__serverRouteNestId = (int) ($__serverRouteServer->nest_id ?? 0);
                            $__serverRouteNestName = (string) (optional($__serverRouteServer->nest)->name ?? 'default');
                            $__serverRouteNestSlug = strtolower(preg_replace('/[^a-z0-9_-]+/i', '-', $__serverRouteNestName));
                            $__serverRouteNestSlug = trim($__serverRouteNestSlug, '-') ?: 'default';

                            $__serverRouteLcpImageUrl =
                                $__serverImageEntry($__serverImages['eggs'] ?? [], $__serverRouteEggId, 'row')
                                ?: $__serverImageEntry($__serverImages['nests'] ?? [], $__serverRouteNestId, 'row')
                                ?: $__serverImageEntry($__serverImages['eggs'] ?? [], $__serverRouteEggId, 'banner')
                                ?: $__serverImageEntry($__serverImages['nests'] ?? [], $__serverRouteNestId, 'banner')
                                ?: $__serverImageDefault($__serverImages, 'row')
                                ?: $__serverImageDefault($__serverImages, 'banner')
                                ?: '/DGEN/themes/Hyperv2/server/banner/' . $__serverRouteNestSlug . '.webp';

                            $__serverRouteLcpImageUrl = $__safeAuthAssetUrl($__serverRouteLcpImageUrl);
                        }
                    } catch (\Throwable $e) {
                        $__serverRouteLcpImageUrl = null;
                    }
                }
            }
        @endphp
        @if($__serverRouteLcpImageUrl)
            <link rel="preload" as="image" href="{{ $__serverRouteLcpImageUrl }}" fetchpriority="high">
        @endif

        @php $__fontsCssV = @filemtime(public_path('assets/css/fonts.css')) ?: 0; @endphp
        <link rel="stylesheet" href="/assets/css/fonts.css?v={{ $__fontsCssV }}" media="print" onload="this.media='all'">

        @php
            $__isAuth   = Str::startsWith(request()->path(), 'auth');
            $__isDashboardShell = !$__isAuth && Auth::check() && trim(request()->path(), '/') === '';
            $__manifestPath = public_path('assets/manifest.json');
            $__manifestVer = ((string) (@filemtime($__manifestPath) ?: 0)) . ':' . ((string) (@filesize($__manifestPath) ?: 0));
            $__preloadMode = $__isAuth ? 'auth' : ($__isDashboardShell ? 'dashboard' : 'core');
            $__plKey    = 'wrap:preloads:v7:' . $__preloadMode . ':' . ($__hasAuthShell ? 'shell' : 'noshell') . ':' . substr($asset->integrity('main.js') ?: 'x', 7, 12) . ':' . $__manifestVer;
            $__preloadsHtml = $__remember($__plKey, 86400, function() use ($asset, $__isAuth, $__hasAuthShell, $__isDashboardShell) {
                $list = $__isAuth
                    ? $asset->authPreloads()
                    : ($__isDashboardShell ? $asset->dashboardPreloads() : $asset->preloads());
                $out  = '';
                foreach ($list as $key => $p) {
                    $path = parse_url($p['src'], PHP_URL_PATH) ?: $p['src'];
                    if ($__hasAuthShell && !str_ends_with($path, '.css')) {
                        continue;
                    }
                    if (str_ends_with($path, '.css')) {
                        $out .= '<link rel="preload" href="' . $p['src'] . '" as="style" crossorigin="anonymous">' . "\n        ";
                        if ($__isAuth && preg_match('/^AuthApp\.[a-z0-9]+\.css$/i', (string) $key) === 1) {
                            $out .= '<link rel="stylesheet" href="' . $p['src'] . '" media="print" onload="this.media=\'all\'" crossorigin="anonymous">' . "\n        ";
                            $out .= '<noscript><link rel="stylesheet" href="' . $p['src'] . '" crossorigin="anonymous"></noscript>' . "\n        ";
                        }
                    } else {
                        $out .= '<link rel="modulepreload" href="' . $p['src'] . '" crossorigin="anonymous">' . "\n        ";
                    }
                }
                return $out;
            });
        @endphp
        @if($__isAuth)
            <link rel="modulepreload" href="{!! $asset->url('main.js') !!}" crossorigin="anonymous" integrity="{!! $asset->integrity('main.js') !!}">
        @endif
        {!! $__preloadsHtml !!}
        
        <noscript>
            <link rel="stylesheet" href="/assets/css/fonts.css?v={{ $__fontsCssV }}">
        </noscript>

        @php
            $__themeCssHtml = '';
            if ($themeVars && is_array($themeVars) && count($themeVars) > 0) {
                $__themeCssKey  = 'wrap:theme-css:' . $__tScope . ':' . substr(md5($themeRaw), 0, 16);
                $__themeCssHtml = $__remember($__themeCssKey, 3600, function() use ($themeVars, $themeEnforce) {
                    $parseRgb = function(?string $c): ?string {
                        if (!$c) return null; $c = trim($c);
                        if (preg_match('/^#([a-f0-9]{6})(?:[a-f0-9]{2})?$/i', $c, $m)) return hexdec(substr($m[1],0,2)) . ', ' . hexdec(substr($m[1],2,2)) . ', ' . hexdec(substr($m[1],4,2));
                        if (preg_match('/^#([a-f0-9]{3})$/i', $c, $m)) return hexdec(str_repeat($m[1][0],2)) . ', ' . hexdec(str_repeat($m[1][1],2)) . ', ' . hexdec(str_repeat($m[1][2],2));
                        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/i', $c, $m)) return ((int)$m[1]) . ', ' . ((int)$m[2]) . ', ' . ((int)$m[3]);
                        return null;
                    };
                    $imp = $themeEnforce ? ' !important' : '';
                    $css = '<style id="hyper-theme-vars">:root{';
                    foreach ($themeVars as $k => $v) {
                        if (str_starts_with($k, '--hyper-') && !empty($v) && !str_ends_with($k, '-rgb')) {
                            $css .= $k . ':' . $v . $imp . ';';
                        }
                    }
                    $pr = $parseRgb($themeVars['--hyper-primary'] ?? null);
                    $bg = $parseRgb($themeVars['--hyper-background'] ?? null);
                    if ($pr) $css .= '--hyper-primary-rgb:' . $pr . $imp . ';';
                    if ($bg) $css .= '--hyper-background-rgb:' . $bg . $imp . ';';
                    $css .= '}</style>';
                    if (!empty($themeVars['--hyper-font-url'])) {
                        $fu  = htmlspecialchars($themeVars['--hyper-font-url'], ENT_QUOTES);
                        $css .= '<link rel="stylesheet" href="' . $fu . '" media="print" onload="this.media=\'all\'">';
                        $css .= '<noscript><link rel="stylesheet" href="' . $fu . '"></noscript>';
                    }
                    return $css;
                });
            }
        @endphp
        @php
            $__hyperCssV = @filemtime(public_path('assets/css/hyper.css')) ?: 0;
            $__mainCssHtml = $__remember(
                'wrap:maincss-links:v3:' . substr($asset->integrity('main.css') ?: 'x', 7, 12)
                    . ':' . ($__isAuth ? 'auth' : 'core') . ':' . $__hyperCssV . ':' . $__manifestVer,
                86400,
                function () use ($asset, $__hyperCssV, $__isAuth) {
                    $out = '';

                    // The dashboard bundle CSS is large and not needed by the auth-only
                    // React entry. Keep it off auth routes so it cannot compete with the
                    // mobile LCP image or AuthApp.css on cold loads.
                    $url = $asset->url('main.css');
                    if (!$__isAuth && $url !== 'main.css') {
                        $int = $asset->integrity('main.css');
                        $out .= '<link rel="preload" href="' . $url . '" as="style" crossorigin="anonymous" integrity="' . $int . '">' . "\n"
                             .  '            <link rel="stylesheet" href="' . $url . '" media="print" onload="this.media=\'all\'" crossorigin="anonymous" integrity="' . $int . '">' . "\n"
                             .  '            <noscript><link rel="stylesheet" href="' . $url . '" crossorigin="anonymous" integrity="' . $int . '"></noscript>';
                    }

                    // hyper.css carries the Hyper utility layer (bg-hyper-card,
                    // bg-hyper-primary, radius/blur overrides, etc.).
                    if ($__hyperCssV) {
                        $hyperUrl = '/assets/css/hyper.css?t=' . $__hyperCssV;
                        if ($__isAuth) {
                            $out .= "\n            " . '<link rel="preload" href="' . $hyperUrl . '" as="style">';
                            $out .= "\n            " . '<link rel="stylesheet" href="' . $hyperUrl . '" media="print" onload="this.media=\'all\'">';
                            $out .= "\n            " . '<noscript><link rel="stylesheet" href="' . $hyperUrl . '"></noscript>';
                        } else {
                            $hyperCssPath = public_path('assets/css/hyper.css');
                            $hyperCss = is_readable($hyperCssPath) ? file_get_contents($hyperCssPath) : '';
                            if ($hyperCss !== false && $hyperCss !== '') {
                                $out .= "\n            " . '<style id="hyper-utility-css">' . str_replace('</style', '<\/style', $hyperCss) . '</style>';
                            } else {
                                $out .= "\n            " . '<link rel="stylesheet" href="' . $hyperUrl . '">';
                            }
                        }
                    }

                    return $out;
                }
            );
        @endphp
        {!! $__mainCssHtml !!}
        {!! $__themeCssHtml !!}
        <style>
            html,body{font-family:var(--hyper-font-family,'Poppins',sans-serif)}
            body{background-color:var(--hyper-background,#0c0a09);color:var(--hyper-text-primary,#fff);margin:0}
            @if($__isAuth)
                *,:before,:after{box-sizing:border-box}button,input,select,textarea{font:inherit;color:inherit}img,svg,video{display:block;max-width:100%;height:auto}a{color:inherit;text-decoration:none}button{appearance:button;border:0;background:transparent}.block{display:block}.inline-block{display:inline-block}.inline-flex{display:inline-flex}.flex{display:flex}.grid{display:grid}.hidden{display:none}.relative{position:relative}.absolute{position:absolute}.fixed{position:fixed}.sticky{position:sticky}.inset-0{inset:0}.top-0{top:0}.left-0{left:0}.right-0{right:0}.z-10{z-index:10}.z-\[210\]{z-index:210}.min-h-screen{min-height:100vh}.h-full{height:100%}.h-4{height:1rem}.h-5{height:1.25rem}.h-6{height:1.5rem}.h-8{height:2rem}.h-10{height:2.5rem}.h-11{height:2.75rem}.h-\[48px\]{height:48px}.min-h-\[48px\]{min-height:48px}.w-full{width:100%}.w-auto{width:auto}.w-fit{width:fit-content}.w-4{width:1rem}.w-5{width:1.25rem}.w-6{width:1.5rem}.w-8{width:2rem}.w-10{width:2.5rem}.w-11{width:2.75rem}.min-w-0{min-width:0}.min-w-\[40px\]{min-width:40px}.max-w-md{max-width:28rem}.max-w-4xl{max-width:56rem}.max-w-6xl{max-width:72rem}.max-h-10{max-height:2.5rem}.flex-1{flex:1 1 0%}.flex-col{flex-direction:column}.flex-wrap{flex-wrap:wrap}.flex-shrink-0{flex-shrink:0}.items-center{align-items:center}.justify-center{justify-content:center}.justify-between{justify-content:space-between}.gap-1{gap:.25rem}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}.gap-8{gap:2rem}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}.overflow-hidden{overflow:hidden}.overflow-visible{overflow:visible}.object-cover{object-fit:cover}.object-contain{object-fit:contain}.appearance-none{appearance:none}.resize-none{resize:none}.cursor-pointer{cursor:pointer}.rounded-lg{border-radius:var(--hyper-component-radius-amount,.5rem)}.rounded-full{border-radius:9999px}.border{border:1px solid}.border-2{border:2px solid}.border-t{border-top:1px solid}.border-b{border-bottom:1px solid}.border-none{border-style:none}.border-transparent{border-color:transparent}.border-hyper-primary{border-color:var(--hyper-primary)}.border-hyper-secondary{border-color:var(--hyper-secondary)}.border-hyper-subtle{border-color:var(--hyper-border-subtle)}.border-hyper-accent{border-color:var(--hyper-accent)}.bg-transparent{background-color:transparent}.bg-black{background-color:#131a20}.bg-black\/10{background-color:rgba(19,26,32,.1)}.bg-hyper-background{background-color:var(--hyper-background)}.bg-hyper-card{background-color:var(--hyper-card)}.bg-hyper-sidebar{background-color:var(--hyper-sidebar)}.bg-hyper-muted{background-color:var(--hyper-muted)}.bg-hyper-secondary{background-color:var(--hyper-secondary)}.bg-hyper-primary{background-color:var(--hyper-primary)}.bg-hyper-primary-10,.bg-hyper-accent\/10{background-color:rgba(var(--hyper-primary-rgb),.1)}.bg-hyper-glass{background-color:var(--hyper-glass);backdrop-filter:blur(var(--hyper-component-blur-default,16px));-webkit-backdrop-filter:blur(var(--hyper-component-blur-default,16px))}.text-transparent{color:transparent}.text-hyper-primary{color:var(--hyper-primary)}.text-hyper-accent{color:var(--hyper-accent)}.text-hyper-foreground,.text-hyper-text-primary{color:var(--hyper-text-primary)}.text-hyper-muted-foreground{color:var(--hyper-text-muted)}.text-white{color:#fff}.text-xs{font-size:.75rem;line-height:1rem}.text-sm{font-size:.875rem;line-height:1.25rem}.text-lg{font-size:1.125rem;line-height:1.75rem}.font-medium{font-weight:500}.font-semibold{font-weight:600}.uppercase{text-transform:uppercase}.tracking-wide{letter-spacing:.025em}.leading-tight{line-height:1.25}.opacity-60{opacity:.6}.p-0{padding:0}.p-3{padding:.75rem}.p-4{padding:1rem}.p-6{padding:1.5rem}.px-1{padding-left:.25rem;padding-right:.25rem}.px-2{padding-left:.5rem;padding-right:.5rem}.px-3{padding-left:.75rem;padding-right:.75rem}.px-4{padding-left:1rem;padding-right:1rem}.py-0\.5{padding-top:.125rem;padding-bottom:.125rem}.py-1{padding-top:.25rem;padding-bottom:.25rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.py-3{padding-top:.75rem;padding-bottom:.75rem}.py-6{padding-top:1.5rem;padding-bottom:1.5rem}.pl-3{padding-left:.75rem}.pr-3{padding-right:.75rem}.pr-10{padding-right:2.5rem}.mt-2{margin-top:.5rem}.mt-4{margin-top:1rem}.mt-6{margin-top:1.5rem}.mt-8{margin-top:2rem}.mb-1{margin-bottom:.25rem}.mb-4{margin-bottom:1rem}.mb-6{margin-bottom:1.5rem}.my-6{margin-top:1.5rem;margin-bottom:1.5rem}.mx-2{margin-left:.5rem;margin-right:.5rem}.mx-auto{margin-left:auto;margin-right:auto}.space-y-4>:not([hidden])~:not([hidden]){margin-top:1rem}.shadow-2xl{box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}.shadow-none{box-shadow:none}.shadow-\[0_20px_45px_rgba\(0\,0\,0\,0\.25\)\]{box-shadow:0 20px 45px rgba(0,0,0,.25)}.backdrop-blur-lg{backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}.backdrop-blur-xl{backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px)}.mix-blend-overlay{mix-blend-mode:overlay}.outline-none{outline:2px solid transparent;outline-offset:2px}.transition-all{transition-property:all;transition-duration:.15s}.transition-colors{transition-property:color,background-color,border-color,text-decoration-color,fill,stroke;transition-duration:.15s}.duration-150{transition-duration:.15s}.duration-200{transition-duration:.2s}.duration-300{transition-duration:.3s}.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.hyper-input{background:var(--hyper-background);color:var(--hyper-text-primary);border-color:var(--hyper-primary);font-weight:500}.hyper-input:hover{border-color:var(--hyper-accent)}.hyper-input:focus{border-width:2px;outline:0}.button-xlarge{height:2.5rem;width:100%;padding:.5rem 1rem;font-size:.875rem}.button-large{padding:1rem;font-size:.875rem}.button-small{padding:.5rem 1rem}.button-xsmall{padding:.25rem .5rem;font-size:.75rem}.button-xlarge,.button-large,.button-small,.button-xsmall{position:relative;display:inline-block;border:1px solid var(--hyper-button-primary-border,var(--hyper-primary));border-radius:var(--hyper-component-radius-amount,.5rem);background:var(--hyper-button-primary-bg,var(--hyper-primary));color:var(--hyper-button-primary-text,#fff);box-shadow:inset 0 2px 0 rgba(255,255,255,.3);font-weight:500;text-align:center}.button-xlarge>span,.button-large>span,.button-small>span,.button-xsmall>span{display:flex;align-items:center;justify-content:center;gap:.25rem;font-weight:500}.button-xlarge[disabled],.button-large[disabled],.button-small[disabled],.button-xsmall[disabled]{opacity:.55;cursor:default}.fixed.w-full.h-full{width:100%;height:100%}.hyper-floating-nav-top-blur{position:fixed;top:0;left:0;right:0;height:72px;z-index:200;pointer-events:none}.bg-hyper-card.backdrop-blur-lg,.bg-hyper-sidebar.backdrop-blur-xl{background-color:var(--hyper-card)}@media (min-width:768px){.md\:mx-4{margin-left:1rem;margin-right:1rem}.md\:flex{display:flex}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:block{display:block}.lg\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.lg\:p-10{padding:2.5rem}.lg\:hidden{display:none}}@supports (height:100dvh){.min-h-screen{min-height:100dvh}}
                html.hyper-no-blur [class*="backdrop-blur"]:not(.hyper-blur-keep),html.hyper-no-blur .bg-hyper-glass,html.hyper-no-blur .hyper-floating-nav-top-blur{backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
                html.hyper-radius-override .rounded-lg{border-radius:var(--hyper-component-radius-amount,8px)!important}
                html.hyper-animations-off *,html.hyper-animations-off *:before,html.hyper-animations-off *:after{animation-delay:0ms!important;animation-duration:1ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important;transition-delay:0ms!important;transition-duration:1ms!important}
                html.hyper-animations-off [class*="animate-spin"]{animation-duration:900ms!important;animation-iteration-count:infinite!important}
                .bg-hyper-glass{background-color:var(--hyper-glass,rgba(255,255,255,.08))!important;backdrop-filter:blur(var(--hyper-component-blur-default,16px));-webkit-backdrop-filter:blur(var(--hyper-component-blur-default,16px))}
            @endif
        </style>
        @if($pwaEnabled && !\Illuminate\Support\Str::startsWith(request()->path(), 'auth'))
            <script data-cfasync="false">
                window.addEventListener('load', function () {
                    window.setTimeout(function () {
                        if (document.querySelector('link[rel="manifest"]')) return;
                        var manifest = document.querySelector('meta[name="hyper-pwa-manifest"]');
                        if (!manifest || !manifest.content) return;
                        var link = document.createElement('link');
                        link.rel = 'manifest';
                        link.href = manifest.content;
                        document.head.appendChild(link);
                    }, 2000);
                }, { once: true });
            </script>
        @endif

        @yield('assets')

        @if(!empty($adsSettings['header_script']))
            {!! $adsSettings['header_script'] !!}
        @endif

    </head>
    <body class="{{ $css['body'] ?? 'bg-neutral-50' }}">
        @section('content')
            @yield('above-container')
            @if($__hasAuthShell)
                @php
                    $__authShellDict = [];
                    if (isset($inlineDictJson) && is_string($inlineDictJson) && $inlineDictJson !== '') {
                        $__decodedAuthShellDict = json_decode($inlineDictJson, true);
                        $__authShellDict = is_array($__decodedAuthShellDict) ? $__decodedAuthShellDict : [];
                    }
                    $__authShellText = function (string $key, string $fallback) use ($__authShellDict): string {
                        $value = $__authShellDict[$key] ?? null;
                        return is_string($value) && $value !== '' ? $value : $fallback;
                    };

                    $__shellAuth = is_array($__authExp ?? null) ? $__authExp : [];
                    $__shellKind = $__authShellKind ?? 'login';
                    $__shellTitleKey = $__shellKind === 'register'
                        ? 'common.createAccount'
                        : ($__shellKind === 'forgot' ? 'auth.requestPasswordReset' : 'common.login');
                    $__shellTitleFallback = $__shellKind === 'register'
                        ? 'Create Account'
                        : ($__shellKind === 'forgot' ? 'Request Password Reset' : 'Login');
                    $__shellSubtitleKey = $__shellKind === 'register'
                        ? 'register.subtitle'
                        : ($__shellKind === 'forgot' ? 'auth.forgotSubtitle' : 'auth.loginSubtitle');
                    $__shellSubtitleFallback = $__shellKind === 'register'
                        ? 'Join our community'
                        : ($__shellKind === 'forgot' ? 'Enter your email to reset your password' : 'Sign in to your account');
                    $__shellIconText = $__shellKind === 'register' ? '+' : ($__shellKind === 'forgot' ? '?' : '>');
                    $__shellSplit = ($__shellAuth['layout'] ?? null) === 'split';
                    $__shellFullSplit = ($__shellAuth['layout'] ?? null) === 'full-split';
                    $__shellFloatingFullSplit = $__shellFullSplit && !empty($themeDecoded['site']['sidebar']['floatingLayout']);
                    $__shellUsesSplitMedia = $__shellSplit || $__shellFullSplit;
                    $__shellMediaSide = ($__shellAuth['splitMediaSide'] ?? 'right') === 'left' ? 'left' : 'right';
                    $__shellMediaUrl = $__shellUsesSplitMedia ? ($__authSplitImageUrl ?? null) : null;
                    $__shellBackgroundUrl = $__authBgUrl ?? null;
                    $__shellBackgroundOverlay = ($__authBgOverlay ?? false) === true;
                    $__shellMediaClickUrl = $__safeAuthAssetUrl($__shellAuth['mediaClickUrl'] ?? null);
                    $__shellLogoPlacement = $__shellAuth['logoPlacement'] ?? 'navigation';
                    $__siteLogos = is_array($themeDecoded['site']['logos'] ?? null) ? $themeDecoded['site']['logos'] : [];
                    $__authLogos = !empty($__shellAuth['useCustomAuthLogo']) && is_array($__shellAuth['logos'] ?? null)
                        ? $__shellAuth['logos']
                        : $__siteLogos;
                    $__shellLogoMode = ($__authLogos['largeMode'] ?? 'image') === 'text' ? 'text' : 'image';
                    $__shellLogoUrl = $__safeAuthAssetUrl($__authLogos['largeUrl'] ?? '/logo/large.png');
                    $__shellLogoText = (string) ($__authLogos['largeText'] ?? 'TURTLE');
                    $__shellLogoWidth = isset($__authLogos['largeWidth']) ? max(1, min(320, (int) $__authLogos['largeWidth'])) : 117;
                    $__shellLogoHeight = isset($__authLogos['largeHeight']) ? max(12, min(96, (int) $__authLogos['largeHeight'])) : 24;
                    $__shellPrimaryButton = is_array($__shellAuth['primaryButton'] ?? null) ? $__shellAuth['primaryButton'] : null;
                    $__shellPrimaryName = is_array($__shellPrimaryButton) ? trim((string) ($__shellPrimaryButton['name'] ?? '')) : '';
                    $__shellPrimaryLink = $__shellPrimaryButton ? ($__safeAuthAssetUrl($__shellPrimaryButton['link'] ?? null) ?: '#') : '#';
                    $__shellPrimaryNewTab = !empty($__shellPrimaryButton['newTab']);
                    $__addonsForShell = is_array($addonsDecoded['addons'] ?? null) ? $addonsDecoded['addons'] : [];
                    $__shellLanguageAddon = is_array($__addonsForShell['LanguageTranslations'] ?? null) ? $__addonsForShell['LanguageTranslations'] : [];
                    $__shellLanguages = is_array($siteConfiguration['languages'] ?? null) ? $siteConfiguration['languages'] : ['en' => 'English'];
                    $__shellLangCode = preg_replace('/[^a-z0-9_-]/i', '', $userLang ?? 'en') ?: 'en';
                    $__shellLangLabel = strtoupper(substr($__shellLangCode, 0, 2));
                    $__shellShowLanguage = !empty($__shellLanguageAddon['enabled']) && count($__shellLanguages) > 0;
                    $__shellRegisterEnabled = array_key_exists('UserRegister', $__addonsForShell)
                        ? !empty($__addonsForShell['UserRegister']['enabled'])
                        : true;
                    $__shellCustomRegisterEnabled = !empty($__shellAuth['enableCustomRegistrationLink'])
                        && !empty($__shellAuth['customRegistrationLink']);
                    $__shellRegisterLink = $__shellCustomRegisterEnabled
                        ? ($__safeAuthAssetUrl($__shellAuth['customRegistrationLink']) ?: '/auth/register')
                        : '/auth/register';
                    $__shellShowRegister = $__shellRegisterEnabled || $__shellCustomRegisterEnabled;
                    $__shellForgotLink = !empty($__shellAuth['enableCustomForgotPasswordLink']) && !empty($__shellAuth['customForgotPasswordLink'])
                        ? ($__safeAuthAssetUrl($__shellAuth['customForgotPasswordLink']) ?: '/auth/password')
                        : '/auth/password';
                    $__shellDemo = is_array($__addonsForShell['demo-mode'] ?? null) ? $__addonsForShell['demo-mode'] : [];
                    $__shellDemoEnabled = !empty($__shellDemo['enabled']);
                    $__shellDemoHidden = !empty($__shellDemo['hide_login_alert']);
                    $__shellDemoAutofill = $__shellDemoEnabled && (($__shellDemo['auto_fill_credentials'] ?? true) !== false);
                    $__shellDemoValue = $__shellDemoAutofill ? 'demo' : '';
                    $__shellSso = is_array($__addonsForShell['sso-login'] ?? null) ? $__addonsForShell['sso-login'] : [];
                    $__shellSsoProviders = [];
                    $__pushShellSsoProvider = function (string $name, string $label, string $color) use (&$__shellSsoProviders): void {
                        $__shellSsoProviders[] = compact('name', 'label', 'color');
                    };
                    if (!empty($__shellSso['discord_enabled']) && !empty($__shellSso['discord_client_id'])) {
                        $__pushShellSsoProvider('discord', 'Discord', '#5865F2');
                    }
                    if (!empty($__shellSso['discord_enabled']) && !empty($__shellSso['dgen_enabled']) && !empty($__shellSso['dgen_client_id'])) {
                        $__pushShellSsoProvider('dgen', 'DGEN', '#16a34a');
                    }
                    if (!empty($__shellSso['google_enabled']) && !empty($__shellSso['google_client_id'])) {
                        $__pushShellSsoProvider('google', 'Google', '#DB4437');
                    }
                    if (!empty($__shellSso['github_enabled']) && !empty($__shellSso['github_client_id'])) {
                        $__pushShellSsoProvider('github', 'GitHub', '#333333');
                    }
                    if (!empty($__shellSso['whmcs_enabled']) && !empty($__shellSso['whmcs_url']) && !empty($__shellSso['whmcs_client_id'])) {
                        $__pushShellSsoProvider('whmcs', trim((string) ($__shellSso['whmcs_custom_name'] ?? '')) ?: 'WHMCS', '#2563eb');
                    }
                    if (!empty($__shellSso['paymenter_enabled']) && !empty($__shellSso['paymenter_url']) && !empty($__shellSso['paymenter_client_id'])) {
                        $__pushShellSsoProvider('paymenter', trim((string) ($__shellSso['paymenter_custom_name'] ?? '')) ?: 'Paymenter', '#10b981');
                    }
                    $__shellUseTwoStepFlow = !empty($__shellSso['enabled']) && count($__shellSsoProviders) > 0;
                    $__shellSsoIconOnly = ($__shellAuth['ssoButtonStyle'] ?? 'full') === 'icon';
                    $__shellCopyright = is_array($themeDecoded['site']['copyright'] ?? null) ? $themeDecoded['site']['copyright'] : [];
                    $__shellCopyrightOwner = trim((string) ($__shellCopyright['ownerName'] ?? '')) ?: 'Turtle';
                    $__shellCopyrightSuffix = array_key_exists('suffix', $__shellCopyright) ? (string) $__shellCopyright['suffix'] : '®';
                    $__shellCopyrightUrl = $__safeAuthAssetUrl($__shellCopyright['ownerUrl'] ?? null) ?: '/';
                    $__shellCopyrightStartYear = isset($__shellCopyright['startYear']) ? (int) $__shellCopyright['startYear'] : 2024;
                    $__shellCopyrightCurrentYear = (int) date('Y');
                    $__shellCopyrightYears = $__shellCopyrightStartYear > 0 && $__shellCopyrightStartYear < $__shellCopyrightCurrentYear
                        ? $__shellCopyrightStartYear . ' - ' . $__shellCopyrightCurrentYear
                        : (string) $__shellCopyrightCurrentYear;
                @endphp
                <style id="hyper-auth-shell-critical">
                    .hyper-auth-shell{position:relative;min-height:100vh;display:flex;flex-direction:column;overflow:hidden;background:var(--hyper-background,#0a0f0e);color:var(--hyper-text-primary,#f8fafc)}
                    .hyper-auth-shell a{color:inherit;text-decoration:none}
                    .hyper-auth-shell__bg,.hyper-auth-shell__shade{position:fixed;inset:0;z-index:0;pointer-events:none}
                    .hyper-auth-shell__bg img{display:block;width:100%;height:100%;object-fit:cover}
                    .hyper-auth-shell__bg-overlay{position:absolute;inset:0;background:var(--hyper-primary,#20ac6b);mix-blend-mode:overlay}
                    .hyper-auth-shell__shade{background:#131a20;opacity:.6}
                    .hyper-auth-shell__nav{position:relative;z-index:1;min-height:56px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 12px;background:var(--hyper-sidebar,var(--hyper-card,#111827));border-bottom:1px solid var(--hyper-primary,#20ac6b)}
                    .hyper-auth-shell__logo{display:flex;align-items:center;min-width:0;flex-shrink:0;font-weight:700;color:var(--hyper-text-primary,#fff);line-height:1}
                    .hyper-auth-shell__logo img{display:block;object-fit:contain;max-width:32vw}
                    .hyper-auth-shell__nav-actions{display:flex;align-items:center;gap:8px;min-width:0}
                    .hyper-auth-shell__language{width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 40px;border:1px solid var(--hyper-border-default,rgba(255,255,255,.14));border-radius:8px;background:var(--hyper-card,rgba(255,255,255,.08));color:var(--hyper-text-primary,#fff);font-size:12px;font-weight:700;text-transform:uppercase}
                    .hyper-auth-shell__nav-button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border-radius:8px;background:var(--hyper-primary,#20ac6b);color:var(--hyper-button-primary-text,#fff);font-size:14px;font-weight:600;white-space:nowrap}
                    .hyper-auth-shell__main{position:relative;z-index:1;flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
                    .hyper-auth-shell__card{width:min(100%,896px);min-height:480px;display:grid;grid-template-columns:1fr;overflow:hidden;border:1px solid var(--hyper-primary,#20ac6b);border-radius:8px;background:var(--hyper-card,#111827);box-shadow:0 24px 70px rgba(0,0,0,.38)}
                    .hyper-auth-shell__media{position:relative;display:none;background:linear-gradient(135deg,var(--hyper-surface,#0f172a),var(--hyper-background,#020617))}
                    .hyper-auth-shell__media img{display:block;width:100%;height:100%;object-fit:cover}
                    .hyper-auth-shell__media:after{content:"";position:absolute;inset:0;background:rgba(0,0,0,.1);pointer-events:none}
                    .hyper-auth-shell__media-link{position:absolute;inset:0;z-index:2}
                    .hyper-auth-shell__form{min-width:0;display:flex;flex-direction:column;justify-content:center;padding:40px}
                    .hyper-auth-shell__form-logo{display:flex;justify-content:center;margin:0 0 24px}
                    .hyper-auth-shell__heading{display:flex;align-items:center;gap:12px;margin:0 0 16px}
                    .hyper-auth-shell__icon{width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:rgba(var(--hyper-primary-rgb,32,172,107),.1);color:var(--hyper-accent,var(--hyper-primary,#20ac6b));font-weight:700}
                    .hyper-auth-shell__title{margin:0;color:var(--hyper-text-primary,#fff);font-size:18px;line-height:1.25;font-weight:700}
                    .hyper-auth-shell__subtitle{margin:2px 0 0;color:var(--hyper-accent,var(--hyper-text-secondary,#cbd5e1));font-size:14px;line-height:1.35}
                    .hyper-auth-shell__alert{margin:0 0 16px;padding:12px 14px;border-radius:8px;border:1px solid rgba(234,179,8,.7);background:rgba(234,179,8,.1);color:#eab308;font-size:13px}
                    .hyper-auth-shell__field{margin-top:16px}
                    .hyper-auth-shell__field:first-child{margin-top:0}
                    .hyper-auth-shell__grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px}
                    .hyper-auth-shell__grid:first-child{margin-top:0}
                    .hyper-auth-shell__description{margin:-2px 0 8px;color:var(--hyper-text-secondary,#cbd5e1);font-size:13px;line-height:1.4}
                    .hyper-auth-shell__label-row{min-height:22px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;color:var(--hyper-text-primary,#fff);font-size:14px;font-weight:600}
                    .hyper-auth-shell__forgot{font-size:12px;font-weight:600;color:var(--hyper-accent,var(--hyper-primary,#20ac6b))}
                    .hyper-auth-shell__input{box-sizing:border-box;width:100%;height:40px;border-radius:8px;border:1px solid var(--hyper-primary,#20ac6b);background:var(--hyper-background,#0f172a);color:var(--hyper-text-primary,#fff);padding:0 12px;font:inherit}
                    .hyper-auth-shell__submit{width:100%;height:40px;margin-top:24px;border:1px solid var(--hyper-button-primary-border,var(--hyper-primary,#20ac6b));border-radius:8px;background:var(--hyper-button-primary-bg,var(--hyper-primary,#20ac6b));color:var(--hyper-button-primary-text,#fff);font-size:14px;font-weight:700}
                    .hyper-auth-shell__divider{display:flex;align-items:center;gap:10px;margin:22px 0 16px;color:var(--hyper-text-muted,#94a3b8);font-size:12px;font-weight:700;text-transform:uppercase}
                    .hyper-auth-shell__divider:before,.hyper-auth-shell__divider:after{content:"";height:1px;flex:1;background:var(--hyper-secondary,#136144)}
                    .hyper-auth-shell__secondary{display:flex;align-items:center;justify-content:center;width:100%;height:40px;border-radius:8px;border:1px solid var(--hyper-button-secondary-border,var(--hyper-border-default,#334155));background:var(--hyper-button-secondary-bg,#136144);color:var(--hyper-button-secondary-text,#fff);font-size:14px;font-weight:700}
                    .hyper-auth-shell__text-link{display:block;margin-top:12px;text-align:center;color:var(--hyper-text-secondary,#cbd5e1);font-size:12px;font-weight:600}
                    .hyper-auth-shell__sso{display:grid;grid-template-columns:1fr;gap:12px;margin-top:8px}
                    .hyper-auth-shell__sso.is-icon{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:12px}
                    .hyper-auth-shell__sso-button{min-height:40px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:8px;padding:0 14px;color:#fff;font-size:14px;font-weight:700}
                    .hyper-auth-shell__sso-button.is-icon{width:44px;height:44px;min-height:44px;padding:0;font-size:12px}
                    .hyper-auth-shell__copyright{align-self:center;min-width:138px;min-height:48px;display:flex;align-items:center;justify-content:center;margin:32px 0 0;padding:8px 16px;border-radius:8px;background:var(--hyper-glass,rgba(255,255,255,.08));color:var(--hyper-text-secondary,#cbd5e1);font-size:12px;font-weight:600;text-align:center}
                    .hyper-auth-shell__copyright a{color:var(--hyper-accent,var(--hyper-primary,#20ac6b));text-decoration:none}
                    .hyper-auth-shell__copyright-credit{display:block;color:var(--hyper-primary,#20ac6b)}
                    .hyper-auth-shell__copyright-credit strong{color:var(--hyper-accent,var(--hyper-primary,#20ac6b))}
                    .hyper-auth-shell__card.is-full-split{width:100%;min-height:100%;border:0;border-radius:0;box-shadow:none}
                    .hyper-auth-shell__card.is-full-split.is-floating{background:var(--hyper-background,#0a0f0e)}
                    .hyper-auth-shell__card.is-full-split.is-floating .hyper-auth-shell__media{margin:16px;border-radius:8px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}
                    @media (min-width:1024px){.hyper-auth-shell__card.is-split,.hyper-auth-shell__card.is-full-split{grid-template-columns:1fr 1fr}.hyper-auth-shell__media{display:block}}
                    @media (min-width:768px){.hyper-auth-shell__sso:not(.is-icon){grid-template-columns:repeat(2,minmax(0,1fr))}.hyper-auth-shell__sso-button.is-last-odd{grid-column:1/-1}}
                    @media (max-width:640px){.hyper-auth-shell__main{padding:16px}.hyper-auth-shell__form{padding:24px}.hyper-auth-shell__card{min-height:0}.hyper-auth-shell__grid{grid-template-columns:1fr;gap:0}.hyper-auth-shell__nav-button{max-width:42vw;overflow:hidden;text-overflow:ellipsis}}
                </style>
                <div id="app" data-auth-shell="{{ $__shellKind }}">
                    <div class="hyper-auth-shell" aria-busy="true">
                        @if($__shellBackgroundUrl)
                            <div class="hyper-auth-shell__bg" aria-hidden="true">
                                <img src="{{ $__shellBackgroundUrl }}" alt="" width="1920" height="1080" loading="eager" decoding="async" fetchpriority="high">
                                @if($__shellBackgroundOverlay)
                                    <div class="hyper-auth-shell__bg-overlay"></div>
                                @endif
                            </div>
                        @endif
                        <div class="hyper-auth-shell__shade" aria-hidden="true"></div>
                        @if(empty($__shellAuth['disableAuthNavigation']))
                            <header class="hyper-auth-shell__nav">
                                @if($__shellLogoPlacement === 'navigation')
                                    <a class="hyper-auth-shell__logo" href="/" aria-label="{{ $__authShellText('nav.home', 'Home') }}">
                                        @if($__shellLogoMode === 'text' || !$__shellLogoUrl)
                                            <span style="height:{{ $__shellLogoHeight }}px;line-height:{{ $__shellLogoHeight }}px;font-size:{{ max(12, (int) round($__shellLogoHeight * .85)) }}px">{{ $__shellLogoText }}</span>
                                        @else
                                            <img src="{{ $__shellLogoUrl }}" alt="{{ $__authShellText('common.logo', 'Logo') }}" width="{{ $__shellLogoWidth }}" height="{{ $__shellLogoHeight }}" decoding="async" style="width:{{ $__shellLogoWidth }}px;height:{{ $__shellLogoHeight }}px;aspect-ratio:{{ $__shellLogoWidth }}/{{ $__shellLogoHeight }};">
                                        @endif
                                    </a>
                                @else
                                    <div></div>
                                @endif
                                <div class="hyper-auth-shell__nav-actions">
                                    @if($__shellShowLanguage)
                                        <span class="hyper-auth-shell__language" aria-label="{{ $__authShellText('lang.selectLanguage', 'Select Language') }}">{{ $__shellLangLabel }}</span>
                                    @endif
                                    @if($__shellPrimaryName !== '')
                                        <a class="hyper-auth-shell__nav-button" href="{{ $__shellPrimaryLink }}" @if($__shellPrimaryNewTab) target="_blank" rel="noopener noreferrer" @endif>{{ $__shellPrimaryName }}</a>
                                    @endif
                                </div>
                            </header>
                        @endif
                        <main class="hyper-auth-shell__main">
                            <section class="hyper-auth-shell__card @if($__shellSplit) is-split @endif @if($__shellFullSplit) is-full-split @endif @if($__shellFloatingFullSplit) is-floating @endif">
                                @if($__shellUsesSplitMedia && $__shellMediaSide === 'left')
                                    <div class="hyper-auth-shell__media">
                                        @if($__shellMediaUrl)
                                            <img src="{{ $__shellMediaUrl }}" alt="" aria-hidden="true" width="960" height="960" loading="eager" decoding="async" fetchpriority="high">
                                        @endif
                                        @if($__shellMediaClickUrl)
                                            <a class="hyper-auth-shell__media-link" href="{{ $__shellMediaClickUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $__authShellText('auth.openMediaLink', 'Open auth media link') }}"></a>
                                        @endif
                                    </div>
                                @endif
                                <div class="hyper-auth-shell__form">
                                    @if($__shellLogoPlacement === 'form')
                                        <a class="hyper-auth-shell__form-logo hyper-auth-shell__logo" href="/" aria-label="{{ $__authShellText('nav.home', 'Home') }}">
                                            @if($__shellLogoMode === 'text' || !$__shellLogoUrl)
                                                <span style="height:{{ $__shellLogoHeight }}px;line-height:{{ $__shellLogoHeight }}px;font-size:{{ max(12, (int) round($__shellLogoHeight * .85)) }}px">{{ $__shellLogoText }}</span>
                                            @else
                                                <img src="{{ $__shellLogoUrl }}" alt="{{ $__authShellText('common.logo', 'Logo') }}" width="{{ $__shellLogoWidth }}" height="{{ $__shellLogoHeight }}" decoding="async" style="width:{{ $__shellLogoWidth }}px;height:{{ $__shellLogoHeight }}px;aspect-ratio:{{ $__shellLogoWidth }}/{{ $__shellLogoHeight }};">
                                            @endif
                                        </a>
                                    @endif
                                    <div class="hyper-auth-shell__heading">
                                        <div class="hyper-auth-shell__icon" aria-hidden="true">{{ $__shellIconText }}</div>
                                        <div>
                                            <h1 class="hyper-auth-shell__title">{{ $__authShellText($__shellTitleKey, $__shellTitleFallback) }}</h1>
                                            <p class="hyper-auth-shell__subtitle">{{ $__authShellText($__shellSubtitleKey, $__shellSubtitleFallback) }}</p>
                                        </div>
                                    </div>
                                    @if($__shellKind === 'login' && $__shellDemoEnabled && !$__shellDemoHidden)
                                        <div class="hyper-auth-shell__alert">{{ $__authShellText('demo.loginNotice', 'Demo mode is enabled for this panel.') }}</div>
                                    @endif
                                    @if($__shellKind === 'register')
                                        <form>
                                            <div class="hyper-auth-shell__grid">
                                                <div>
                                                    <label class="hyper-auth-shell__label-row" for="auth-shell-first-name">{{ $__authShellText('common.firstName', 'First Name') }}</label>
                                                    <input class="hyper-auth-shell__input" id="auth-shell-first-name" name="first_name" type="text" autocomplete="given-name">
                                                </div>
                                                <div>
                                                    <label class="hyper-auth-shell__label-row" for="auth-shell-last-name">{{ $__authShellText('common.lastName', 'Last Name') }}</label>
                                                    <input class="hyper-auth-shell__input" id="auth-shell-last-name" name="last_name" type="text" autocomplete="family-name">
                                                </div>
                                            </div>
                                            <div class="hyper-auth-shell__field">
                                                <label class="hyper-auth-shell__label-row" for="auth-shell-register-username">{{ $__authShellText('common.username', 'Username') }}</label>
                                                <input class="hyper-auth-shell__input" id="auth-shell-register-username" name="username" type="text" autocomplete="username">
                                            </div>
                                            <div class="hyper-auth-shell__field">
                                                <label class="hyper-auth-shell__label-row" for="auth-shell-register-email">{{ $__authShellText('common.emailAddress', 'Email Address') }}</label>
                                                <input class="hyper-auth-shell__input" id="auth-shell-register-email" name="email" type="email" autocomplete="email">
                                            </div>
                                            <button class="hyper-auth-shell__submit" type="button">{{ $__authShellText('common.continue', 'Continue') }}</button>
                                        </form>
                                        <div class="hyper-auth-shell__divider">{{ $__authShellText('common.or', 'OR') }}</div>
                                        <a class="hyper-auth-shell__secondary" href="/auth/login">{{ $__authShellText('common.login', 'Login') }}</a>
                                        <a class="hyper-auth-shell__text-link" href="{{ $__shellForgotLink }}">{{ $__authShellText('common.forgetPassword', 'Forget Password?') }}</a>
                                    @elseif($__shellKind === 'forgot')
                                        <form>
                                            <div class="hyper-auth-shell__field">
                                                <label class="hyper-auth-shell__label-row" for="auth-shell-email">{{ $__authShellText('auth.email', 'Email') }}</label>
                                                <p class="hyper-auth-shell__description">{{ $__authShellText('auth.emailInstructions', 'Enter your account email address to receive instructions on resetting your password.') }}</p>
                                                <input class="hyper-auth-shell__input" id="auth-shell-email" name="email" type="email" autocomplete="email">
                                            </div>
                                            <button class="hyper-auth-shell__submit" type="button">{{ $__authShellText('auth.sendEmail', 'Send Email') }}</button>
                                        </form>
                                        <a class="hyper-auth-shell__text-link" href="/auth/login">{{ $__authShellText('auth.returnToLogin', 'Return to Login') }}</a>
                                    @else
                                        <form>
                                            <div class="hyper-auth-shell__field">
                                                <label class="hyper-auth-shell__label-row" for="auth-shell-username">{{ $__authShellText('auth.email', 'Email or Username') }}</label>
                                                <input class="hyper-auth-shell__input" id="auth-shell-username" name="username" type="text" autocomplete="username" value="{{ $__shellDemoValue }}">
                                            </div>
                                            @if($__shellUseTwoStepFlow)
                                                <button class="hyper-auth-shell__submit" type="button">{{ $__authShellText('common.continue', 'Continue') }}</button>
                                            @else
                                                <div class="hyper-auth-shell__field">
                                                    <div class="hyper-auth-shell__label-row">
                                                        <label for="auth-shell-password">{{ $__authShellText('common.password', 'Password') }}</label>
                                                        <a class="hyper-auth-shell__forgot" href="{{ $__shellForgotLink }}">{{ $__authShellText('common.forgetPassword', 'Forget Password?') }}</a>
                                                    </div>
                                                    <input class="hyper-auth-shell__input" id="auth-shell-password" name="password" type="password" autocomplete="current-password" value="{{ $__shellDemoValue }}">
                                                </div>
                                                <button class="hyper-auth-shell__submit" type="button">{{ $__authShellText('common.login', 'Login') }}</button>
                                            @endif
                                        </form>
                                        @if(!empty($__shellSso['enabled']) && count($__shellSsoProviders) > 0)
                                            <div class="hyper-auth-shell__divider">{{ $__shellUseTwoStepFlow ? $__authShellText('sso.loginWithSso', 'Or with SSO') : $__authShellText('sso.loginWith', 'Or login with') }}</div>
                                            <div class="hyper-auth-shell__sso @if($__shellSsoIconOnly) is-icon @endif">
                                                @foreach($__shellSsoProviders as $__shellSsoIndex => $__shellProvider)
                                                    @php
                                                        $__isLastOddShellProvider = !$__shellSsoIconOnly
                                                            && $__shellSsoIndex === count($__shellSsoProviders) - 1
                                                            && count($__shellSsoProviders) % 2 !== 0
                                                            && count($__shellSsoProviders) > 1;
                                                    @endphp
                                                    <a
                                                        class="hyper-auth-shell__sso-button @if($__shellSsoIconOnly) is-icon @endif @if($__isLastOddShellProvider) is-last-odd @endif"
                                                        href="/auth/login/sso/{{ $__shellProvider['name'] }}"
                                                        style="background-color:{{ $__shellProvider['color'] }};border-color:{{ $__shellProvider['color'] }};"
                                                        aria-label="{{ $__shellProvider['label'] }}"
                                                        title="{{ $__shellProvider['label'] }}"
                                                    >
                                                        {{ $__shellSsoIconOnly ? substr($__shellProvider['label'], 0, 1) : $__shellProvider['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($__shellShowRegister)
                                            <div class="hyper-auth-shell__divider">{{ $__authShellText('common.or', 'OR') }}</div>
                                            <a class="hyper-auth-shell__secondary" href="{{ $__shellRegisterLink }}">{{ $__authShellText('auth.register', 'Register') }}</a>
                                        @endif
                                    @endif
                                </div>
                                @if($__shellUsesSplitMedia && $__shellMediaSide === 'right')
                                    <div class="hyper-auth-shell__media">
                                        @if($__shellMediaUrl)
                                            <img src="{{ $__shellMediaUrl }}" alt="" aria-hidden="true" width="960" height="960" loading="eager" decoding="async" fetchpriority="high">
                                        @endif
                                        @if($__shellMediaClickUrl)
                                            <a class="hyper-auth-shell__media-link" href="{{ $__shellMediaClickUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $__authShellText('auth.openMediaLink', 'Open auth media link') }}"></a>
                                        @endif
                                    </div>
                                @endif
                            </section>
                            <div class="hyper-auth-shell__copyright">
                                <div>
                                    <div>
                                        <a rel="noopener nofollow noreferrer" href="{{ $__shellCopyrightUrl }}" target="_blank">
                                            {{ $__shellCopyrightOwner }}{{ $__shellCopyrightSuffix }}
                                        </a>
                                        &nbsp;{{ $__shellCopyrightYears }}
                                    </div>
                                    <a rel="noopener nofollow noreferrer" href="https://discord.dgenx.net/" target="_blank" class="hyper-auth-shell__copyright-credit">
                                        Developed By&nbsp;<strong>DGEN</strong>
                                    </a>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            @else
                @yield('container')
            @endif
            @yield('below-container')
        @show
        @section('scripts')
            @if(!$__isAuth || $__hasAuthShell)
                <script>window.HyperV2PanelEntryUrl=@json($asset->urlByPrefix('panelEntry.', '.js'));</script>
            @endif
            <script type="module" defer src="{!! $asset->url('main.js') !!}" crossorigin="anonymous" integrity="{!! $asset->integrity('main.js') !!}"></script>
        @show
        
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    @if($pwaEnabled)
                    navigator.serviceWorker.register('/service-worker.js', { scope: '/', updateViaCache: 'none' })
                        .then(function(registration) {
                            registration.update().catch(function() {});

                            var SW_CONFIG_URL = '/api/public/pwa/sw-config.js?v=3';
                            var SW_CONFIG_VERSION = '{{ $addonsDecoded['updated_at'] ?? '0' }}';
                            var SW_CONFIG_CACHE_KEY = 'hyper:pwa:sw-config:v3:' + SW_CONFIG_VERSION;
                            var SW_INLINE_CONFIG = @json($swInlineConfig);

                            var postConfigToServiceWorker = function(config) {
                                if (!config) return;

                                if (registration.active) {
                                    registration.active.postMessage({
                                        type: 'PWA_CONFIG',
                                        config: config,
                                    });
                                    return;
                                }

                                navigator.serviceWorker.ready
                                    .then(function(readyRegistration) {
                                        if (readyRegistration.active) {
                                            readyRegistration.active.postMessage({
                                                type: 'PWA_CONFIG',
                                                config: config,
                                            });
                                        }
                                    })
                                    .catch(function() {
                                    });
                            };

                            var readCachedConfig = function() {
                                try {
                                    var rawConfig = localStorage.getItem(SW_CONFIG_CACHE_KEY);
                                    if (!rawConfig) return null;

                                    return JSON.parse(rawConfig);
                                } catch (_e) {
                                    return null;
                                }
                            };

                            var writeCachedConfig = function(config) {
                                try {
                                    localStorage.setItem(SW_CONFIG_CACHE_KEY, JSON.stringify(config));
                                } catch (_e) {
                                }
                            };

                            var cached = readCachedConfig();
                            if (cached) {
                                postConfigToServiceWorker(cached);
                                return;
                            }

                            if (SW_INLINE_CONFIG && typeof SW_INLINE_CONFIG === 'object') {
                                writeCachedConfig(SW_INLINE_CONFIG);
                                postConfigToServiceWorker(SW_INLINE_CONFIG);
                                return;
                            }

                            fetch(SW_CONFIG_URL + '&cfg=' + encodeURIComponent(SW_CONFIG_VERSION), { cache: 'no-store' })
                                .then(function(response) {
                                    if (!response.ok) {
                                        throw new Error('HTTP ' + response.status);
                                    }
                                    return response.json();
                                })
                                .then(function(config) {
                                    writeCachedConfig(config);
                                    postConfigToServiceWorker(config);
                                })
                                .catch(function() {
                                });
                        })
                        .catch(function() {
                        });
                    @else
                    navigator.serviceWorker.register('/service-worker.js', { scope: '/', updateViaCache: 'none' })
                        .then(function(registration) {
                            registration.update().catch(function() {});
                        })
                        .catch(function() {
                        });
                    @endif
                });
            }
        </script>

        @if(!empty($adsSettings['body_script']))
            {!! $adsSettings['body_script'] !!}
        @endif
    </body>
</html>
