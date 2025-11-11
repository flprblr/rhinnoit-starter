<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="format-detection" content="telephone=no">

        {{-- SEO Meta Tags --}}
        <meta name="description" content="{{ config('app.description') }}">
        <meta name="keywords" content="{{ config('app.keywords') }}">
        <meta name="author" content="{{ config('app.name') }}">
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">

        {{-- Open Graph / Facebook - Only for public pages (uncomment when needed) --}}
        {{-- Use WebP format (og-image.webp) with PNG fallback (og-image.png) --}}
        {{-- <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name') }}">
        <meta property="og:description" content="{{ config('app.description') }}">
        <meta property="og:image" content="{{ asset('img/seo/og-image.webp') }}">
        <meta property="og:image:secure_url" content="{{ asset('img/seo/og-image.webp') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ config('app.name') }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
        <meta property="og:site_name" content="{{ config('app.name') }}"> --}}

        {{-- Twitter Card - Only for public pages (uncomment when needed) --}}
        {{-- Use WebP format (twitter-image.webp) with PNG fallback (twitter-image.png) --}}
        {{-- <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="{{ config('app.name') }}">
        <meta name="twitter:description" content="{{ config('app.description') }}">
        <meta name="twitter:image" content="{{ asset('img/seo/twitter-image.webp') }}">
        <meta name="twitter:image:alt" content="{{ config('app.name') }}"> --}}

        {{-- Canonical URL --}}
        <link rel="canonical" href="{{ url()->current() }}">

        {{-- Structured Data (JSON-LD) - Only for public pages (uncomment when needed) --}}
        {{-- <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "{{ config('app.name') }}",
            "description": "{{ config('app.description') }}",
            "url": "{{ config('app.url') }}"
        }
        </script> --}}

        {{-- Favicons --}}
        <link rel="icon" href="{{ asset('img/favicon/favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('img/favicon/favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('img/favicon/favicon-32x32.png') }}" sizes="32x32" type="image/png">
        <link rel="icon" href="{{ asset('img/favicon/favicon-16x16.png') }}" sizes="16x16" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('img/favicon/apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('img/favicon/site.webmanifest') }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name') }}</title>
        
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
