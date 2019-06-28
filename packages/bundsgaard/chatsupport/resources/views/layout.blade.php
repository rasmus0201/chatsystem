<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>Chatsupport example</title>

        <script src="https://cdn.jsdelivr.net/npm/vue"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        {{-- <link rel="stylesheet" href="{{ mix('chatsupport') }}"> --}}
        <link rel="stylesheet" href="/css/app.css">
        <script type="text/javascript">
            Vue.config.devtools = true;
        </script>
    </head>
    <body>
        <div id="app" class="my-5 container-fluid">
            @yield('content')
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js" charset="utf-8"></script>
        @yield('script')
    </body>
</html>
