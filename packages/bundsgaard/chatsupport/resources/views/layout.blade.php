<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Chatsupport example</title>
        <link href="{{ asset(mix('app.css', 'vendor/chatsupport')) }}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="app" class="my-5 container-fluid">
            @yield('content')
        </div>

        <script src="{{ asset(mix('manifest.js', 'vendor/chatsupport')) }}"></script>
        <script src="{{ asset(mix('vendor.js', 'vendor/chatsupport')) }}"></script>
        <script src="{{ asset(mix('app.js', 'vendor/chatsupport')) }}"></script>
        @yield('script')
    </body>
</html>
