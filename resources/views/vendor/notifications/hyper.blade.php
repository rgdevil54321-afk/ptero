<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<?php
$variables = $themeConfig['variables'] ?? [];

$emailSettings = $themeConfig['site']['email'] ?? [];
$logoSettings = $themeConfig['site']['logos'] ?? [];
$emailColors = $emailSettings['colors'] ?? [];

$colors = [
    'primary' => !empty($emailColors['primary']) ? $emailColors['primary'] : ($variables['--hyper-primary'] ?? '#df3050'),
    'accent' => !empty($emailColors['primary']) ? $emailColors['primary'] : ($variables['--hyper-accent'] ?? $variables['--hyper-primary'] ?? '#df3050'), // default to primary
    'background' => !empty($emailColors['background']) ? $emailColors['background'] : ($variables['--hyper-background'] ?? '#0c0a09'),
    'card' => !empty($emailColors['card']) ? $emailColors['card'] : ($variables['--hyper-surface'] ?? $variables['--hyper-card'] ?? '#1c1917'),
    'text_primary' => !empty($emailColors['text_primary']) ? $emailColors['text_primary'] : ($variables['--hyper-text-primary'] ?? '#ffffff'),
    'text_secondary' => !empty($emailColors['text_secondary']) ? $emailColors['text_secondary'] : ($variables['--hyper-text-secondary'] ?? '#fafafa'),
    'text_muted' => !empty($emailColors['text_muted']) ? $emailColors['text_muted'] : ($variables['--hyper-text-muted'] ?? '#a1a1aa'),
    'border' => !empty($emailColors['border']) ? $emailColors['border'] : ($variables['--hyper-border-default'] ?? '#27272a'),
    'font_family' => $variables['--hyper-font-family'] ?? "'Poppins', sans-serif",
    'font_url' => $variables['--hyper-font-url'] ?? url('/assets/css/fonts.css'),
];

$resolveUrl = function($url) {
    if (empty($url)) return null;
    return $url;
};

$logoUrl = $resolveUrl($emailSettings['logo'] ?? null) ?? $resolveUrl($logoSettings['largeUrl'] ?? null);
if (empty($logoUrl)) {
    $baseUrl = rtrim(config('app.url'), '/');
    $logoUrl = $baseUrl . '/logo/large.png';
}

$logoText = $emailSettings['text'] ?? $logoSettings['largeText'] ?? config('app.name');
$showBoth = $emailSettings['showLogoAndText'] ?? false;

$logoWidth = $emailSettings['logoWidth'] ?? $logoSettings['largeWidth'] ?? '118';
$logoHeight = $emailSettings['logoHeight'] ?? $logoSettings['largeHeight'] ?? '24';

if (is_numeric($logoWidth)) $logoWidth .= 'px';
if (is_numeric($logoWidth)) $logoWidth .= 'px';

$links = $themeConfig['site']['links'] ?? [];
$websiteLink = $links['website'] ?? null;
$supportEmail = $links['supportEmail'] ?? null;
$socials = array_filter([
    'instagram' => $links['instagram'] ?? null,
    'twitter' => $links['twitter'] ?? null,
    'youtube' => $links['youtube'] ?? null,
]);
if (is_numeric($logoHeight)) $logoHeight .= 'px';

$alignment = $emailSettings['alignment'] ?? 'center';

$imgTag = "<img src=\"{$logoUrl}\" alt=\"Logo\" width=\"{$logoWidth}\" height=\"{$logoHeight}\" style=\"width: {$logoWidth}; height: {$logoHeight}; min-height: 24px; max-width: 100%; border: 0; line-height: 100%; vertical-align: middle;\">";
$textTag = htmlspecialchars($logoText);

$copyright = $themeConfig['site']['copyright'] ?? [];
$ownerName = $copyright['ownerName'] ?? 'Hyper';
$ownerUrl = $copyright['ownerUrl'] ?? url('/');
$startYear = $copyright['startYear'] ?? date('Y');
$currentYear = date('Y');
$suffix = $copyright['suffix'] ?? '®';
$yearDisplay = ($startYear < $currentYear) ? "{$startYear} - {$currentYear}" : $currentYear;


if (!filter_var($colors['font_url'], FILTER_VALIDATE_URL)) {
     $colors['font_url'] = url('/assets/css/fonts.css');
}

$style = [

    'body' => 'margin: 0; padding: 0; width: 100%; background-color: ' . $colors['background'] . ';',
    'email-wrapper' => 'width: 100%; margin: 0; padding: 0; background-color: ' . $colors['background'] . ';',


    'email-masthead' => 'padding: 25px 0; text-align: center;',
    'email-masthead_name' => 'font-size: 16px; font-weight: bold; color: ' . $colors['text_primary'] . '; text-decoration: none; text-shadow: 0 1px 0 rgba(0,0,0,0.2);',

    'email-body' => 'width: 100%; margin: 0; padding: 0; background-color: transparent;',
    'email-body_inner' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0; background-color: ' . $colors['card'] . '; border: 1px solid ' . $colors['border'] . '; border-radius: 8px;',
    'email-body_cell' => 'padding: 35px;',

    'email-footer' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0; text-align: center;',
    'email-footer_cell' => 'color: ' . $colors['text_muted'] . '; padding: 35px; text-align: center;',
    'footer-pill' => 'display: inline-block; padding: 8px 16px; background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; font-weight: 500; font-size: 12px; color: ' . $colors['text_primary'] . '; text-align: center;',
    'footer-link' => 'color: ' . $colors['primary'] . '; text-decoration: none;',


    'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
    'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
    'body_sub' => 'width: 100%; margin-top: 25px; padding: 20px; background-color: ' . $colors['background'] . '; border-radius: 8px; border: 1px solid ' . $colors['border'] . ';',
    'body_sub_text' => 'color: ' . $colors['text_muted'] . '; font-size: 12px; line-height: 1.5em; word-break: break-all;',
    'highlight_text' => 'color: ' . $colors['primary'] . '; font-weight: 600;',


    'anchor' => 'color: ' . $colors['primary'] . ';',
    'header-1' => 'margin-top: 0; color: ' . $colors['text_primary'] . '; font-size: 19px; font-weight: bold; text-align: left;',
    'paragraph' => 'margin-top: 0; color: ' . $colors['text_secondary'] . '; font-size: 14px; line-height: 1.5em;',
    'paragraph-sub' => 'margin-top: 0; color: ' . $colors['text_muted'] . '; font-size: 12px; line-height: 1.5em;',
    'paragraph-center' => 'text-align: center;',


    'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                 background-color: ' . $colors['primary'] . '; border-radius: 6px; color: #ffffff; font-size: 15px; line-height: 25px;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none; font-weight: 500;
                 box-shadow: inset 0px 2px 0px rgba(255, 255, 255, 0.3);',

    'button--green' => 'background-color: ' . $colors['primary'] . ';',
    'button--red' => 'background-color: #dc4d2f;',
    'button--blue' => 'background-color: ' . $colors['primary'] . ';',
];
?>

<?php $fontFamily = "font-family: {$colors['font_family']}, Arial, 'Helvetica Neue', Helvetica, sans-serif;"; ?>

<body style="{{ $style['body'] }}">
    <!-- Import Font -->
    <!--[if !mso]><!-->
    <link href="{{ $colors['font_url'] }}" rel="stylesheet">
    <style type="text/css">
        @import url('{{ $colors['font_url'] }}');
    </style>
    <!--<![endif]-->
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="{{ $style['email-wrapper'] }}" align="center">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <!-- Logo -->
                    <tr>
                        <td style="{{ $style['email-masthead'] }}">
                            <!-- Centered container matching body width -->
                            <table align="center" width="570" cellpadding="0" cellspacing="0" style="width: 570px; max-width: 570px; margin: 0 auto;">
                                <tr>
                                    <td style="text-align: {{ $alignment }};">
                                        <a style="{{ $fontFamily }} {{ $style['email-masthead_name'] }}" href="{{ url('/') }}" target="_blank">
                                            @if($showBoth)
                                                <!-- Flex-like behavior with tables -->
                                                <table align="{{ $alignment }}" border="0" cellpadding="0" cellspacing="0" style="display: inline-table;">
                                                    <tr>
                                                        <td style="padding-right: 12px;">{!! $imgTag !!}</td>
                                                        <td style="{{ $fontFamily }} {{ $style['email-masthead_name'] }} color: {{ $colors['text_primary'] }}; display: flex; align-items: center;">{{ $logoText }}</td>
                                                    </tr>
                                                </table>
                                            @else
                                                @if(!empty($logoUrl))
                                                    {!! $imgTag !!}
                                                @else
                                                    {!! $textTag !!}
                                                @endif
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td style="{{ $style['email-body'] }}" width="100%">
                            <table style="{{ $style['email-body_inner'] }}" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">
                                        <!-- Greeting -->
                                        <h1 style="{{ $style['header-1'] }}">
                                            @if (! empty($greeting))
                                                <?php
                                                    $parts = explode(' ', $greeting, 2);
                                                    if (count($parts) === 2) {
                                                        echo e($parts[0]) . ' <span style="color: ' . $colors['primary'] . ';">' . e($parts[1]) . '</span>';
                                                    } else {
                                                        echo e($greeting);
                                                    }
                                                ?>
                                            @else
                                                @if ($level == 'error')
                                                    Whoops!
                                                @else
                                                    Hello!
                                                @endif
                                            @endif
                                        </h1>

                                        <!-- Intro -->
                                        @foreach ($introLines as $line)
                                            <p style="{{ $style['paragraph'] }}">
                                                {{ $line }}
                                            </p>
                                        @endforeach

                                        <!-- Action Button -->
                                        @if (isset($actionText))
                                            <table style="{{ $style['body_action'] }}" align="center" width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td align="center">
                                                        <?php
                                                            switch ($level) {
                                                                case 'success':
                                                                    $actionColor = 'button--green';
                                                                    break;
                                                                case 'error':
                                                                    $actionColor = 'button--red';
                                                                    break;
                                                                default:
                                                                    $actionColor = 'button--blue';
                                                            }
                                                        ?>

                                                        <a href="{{ $actionUrl }}"
                                                            style="{{ $fontFamily }} {{ $style['button'] }} {{ $style[$actionColor] }}"
                                                            class="button"
                                                            target="_blank">
                                                            {{ $actionText }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                        <!-- Outro -->
                                        @foreach ($outroLines as $line)
                                            <p style="{{ $style['paragraph'] }}">
                                                {{ $line }}
                                            </p>
                                        @endforeach

                                        <!-- Salutation -->
                                        <p style="{{ $style['paragraph'] }}">
                                            Regards,<br>{{ config('app.name') }}
                                        </p>

                                        <!-- Sub Copy -->
                                        @if (isset($actionText))
                                            <table style="{{ $style['body_sub'] }}">
                                                <tr>
                                                    <td style="{{ $fontFamily }}">
                                                        <p style="{{ $style['body_sub_text'] }}">
                                                            If you’re having trouble clicking the "<span style="{{ $style['highlight_text'] }}">{{ $actionText }}</span>" button,
                                                            copy and paste the URL below into your web browser:
                                                        </p>

                                                        <p style="{{ $style['body_sub_text'] }}">
                                                            <a style="{{ $style['anchor'] }}" href="{{ $actionUrl }}" target="_blank">
                                                                {{ $actionUrl }}
                                                            </a>
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                        @endif

                                        <!-- Social & Links -->
                                        @if($websiteLink || $supportEmail || !empty($socials))
                                            <table width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid {{ $colors['border'] }}; padding-top: 20px; margin-top: 20px;">
                                                <tr>
                                                    <td width="60%" valign="top" style="{{ $fontFamily }}">
                                                        @if($websiteLink)
                                                            <div style="margin-bottom: 5px;">
                                                                <a href="{{ $websiteLink }}" target="_blank" style="color: {{ $colors['primary'] }}; text-decoration: none; font-size: 14px; font-weight: 500; display: block;">
                                                                    {{ parse_url($websiteLink, PHP_URL_HOST) ?? 'Website' }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                        @if($supportEmail)
                                                            <div style="margin-top: 5px;">
                                                                <a href="mailto:{{ $supportEmail }}" style="color: {{ $colors['text_muted'] }}; text-decoration: none; font-size: 13px; display: block;">
                                                                    {{ $supportEmail }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td width="40%" valign="top" align="right" style="text-align: right;">
                                                        @if(isset($socials['instagram']))
                                                            <a href="{{ $socials['instagram'] }}" target="_blank" style="display: inline-block; margin-left: 10px; text-decoration: none;">
                                                                <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" width="20" height="20" alt="Instagram" style="border: 0; line-height: 100%; vertical-align: middle;">
                                                            </a>
                                                        @endif
                                                        @if(isset($socials['twitter']))
                                                            <a href="{{ $socials['twitter'] }}" target="_blank" style="display: inline-block; margin-left: 10px; text-decoration: none;">
                                                                <img src="https://img.icons8.com/ios-filled/50/ffffff/twitter.png" width="20" height="20" alt="Twitter" style="border: 0; line-height: 100%; vertical-align: middle;">
                                                            </a>
                                                        @endif
                                                        @if(isset($socials['youtube']))
                                                            <a href="{{ $socials['youtube'] }}" target="_blank" style="display: inline-block; margin-left: 10px; text-decoration: none;">
                                                                <img src="https://img.icons8.com/ios-filled/50/ffffff/youtube-play.png" width="20" height="20" alt="YouTube" style="border: 0; line-height: 100%; vertical-align: middle;">
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td>
                            <table style="{{ $style['email-footer'] }}" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-footer_cell'] }}">
                                        <div style="{{ $style['footer-pill'] }}">
                                            <a style="{{ $style['footer-link'] }}" href="{{ $ownerUrl }}" target="_blank">{{ $ownerName }}{{ $suffix }}</a>
                                            {{ $yearDisplay }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
