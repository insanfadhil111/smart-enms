<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/gedung.png') }}">
    <title>
        Smart EnMS
    </title>
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link rel="stylesheet" id="pagestyle" href="{{ asset('assets/css/nucleo-icons.css') }}">
    <link rel="stylesheet" id="pagestyle" href="{{ asset('assets/css/nucleo-svg.css') }}">
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/aaa1eaf0f7.js" crossorigin="anonymous"></script>
    <!-- Argon Dashboard CSS -->
    <link rel="stylesheet" id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css') }}">
</head>

<body class="{{ $class ?? '' }}">
    @guest
        @yield('content')
    @endguest

    @auth
        {{-- Menentukan Background untuk Beberapa Halaman --}}
        @if (in_array(request()->route()->getName(), ['sign-in-static', 'sign-up-static', 'login', 'register', 'recover-password', 'rtl', 'virtual-reality']))
            @yield('content')
        @else
            @if (!in_array(request()->route()->getName(), ['profile', 'profile-static']))
                <div class="min-height-200 bg-primary position-absolute w-100"></div>
            @elseif (in_array(request()->route()->getName(), ['profile-static', 'profile']))
                <div class="position-absolute w-100 min-height-300 top-0"
                    style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif

            {{-- Pilihan Sidenav Berdasarkan Halaman --}}
            @if (request()->is('dashboard-*')) {{-- Memeriksa jika berada di path dashboard gedung baru --}}
                {{-- Gunakan Sidenav Khusus yang Baru Dibuat --}}
                @include('layouts.navbars.auth.new-sidenav')
            @else
                {{-- Sidenav Default --}}
                @include('layouts.navbars.auth.sidenav')
            @endif

            {{-- Main Content --}}
            <main class="main-content border-radius-lg">
                @yield('content')
            </main>

            {{-- Fixed Plugin (Optional) --}}
            @include('components.fixed-plugin')
        @endif
    @endauth

    <!-- Core JS Files -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Argon Dashboard JS -->
    <script src="{{ asset('assets/js/argon-dashboard.js') }}"></script>

    @stack('js');
</body>

</html>
