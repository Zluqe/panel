<!DOCTYPE html>
<html>
    <head>
        <title>{{ config('app.name', 'Jexactyl') }}</title>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="robots" content="noindex">
            <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
            <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
            <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
            <link rel="manifest" href="/favicons/manifest.json">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
            <link rel="shortcut icon" href="/favicons/favicon.ico">
            <meta name="msapplication-config" content="/favicons/browserconfig.xml">
            <meta name="theme-color" content="#0e4688">
            <meta name="title" content="Zluqe Panel | Free Bot Hosting">
            <meta name="description" content="Experience hassle-free bot hosting with Zluqe. Our platform offers free hosting for both Python, Java and JavaScript bots, making it the one-stop solution for all your Discord needs.">
            <meta property="og:type" content="website">
            <meta property="og:url" content="https://panel.zluqe.org/">
            <meta property="og:title" content="Zluqe Panel | Free Bot Hosting">
            <meta property="og:description" content="Experience hassle-free bot hosting with Zluqe. Our platform offers free hosting for both Python, Java and JavaScript bots, making it the one-stop solution for all your Discord needs.">
            <meta name="theme-color" content="#030ad8" />
            <meta property="twitter:card" content="summary_large_image">
            <meta property="twitter:url" content="https://panel.zluqe.org/">
            <meta property="twitter:title" content="Zluqe Panel | Free Bot Hosting">
            <meta property="twitter:description" content="Experience hassle-free bot hosting with Zluqe. Our platform offers free hosting for both Python, Java and JavaScript bots, making it the one-stop solution for all your Discord needs.">
        @show

        @section('user-data')
            @if(!is_null(Auth::user()))
                <script>
                    window.JexactylUser = {!! json_encode(Auth::user()->toVueObject()) !!};
                </script>
            @endif
            @if(!empty($siteConfiguration))
                <script>
                    window.SiteConfiguration = {!! json_encode($siteConfiguration) !!};
                </script>
            @endif
            @if(!empty($storeConfiguration))
                <script>
                    window.StoreConfiguration = {!! json_encode($storeConfiguration) !!};
                </script>
            @endif
        @show

        @if(!empty($siteConfiguration['background']))
            <style>
                body {
                    background-image: url({!! $siteConfiguration['background'] !!});
                    background-repeat: no-repeat;
                    background-attachment: fixed;
                    background-size: cover;
                }
            </style>
        @endif

        <style>
            @import url('//fonts.googleapis.com/css?family=Rubik:300,400,500&display=swap');
            @import url('//fonts.googleapis.com/css?family=IBM+Plex+Mono|IBM+Plex+Sans:500&display=swap');
        </style>

        @yield('assets')

        @include('layouts.scripts')
    </head>
    <body>
        @section('content')
            @yield('above-container')
            @yield('container')
            @yield('below-container')
        @show
        @section('scripts')
            {!! $asset->js('main.js') !!}
        @show
    </body>
</html>
