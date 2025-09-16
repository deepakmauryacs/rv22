<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        <meta name="author" content="" />
        <meta property="og:image:size" content="300" />
        <link rel="shortcut icon" href="{{ asset('public/assets/superadmin/favicon/raprocure-fevicon.ico') }}"
            type="image/x-icon" />
        <title>{{$title??''}} {{$sub_title??''}} - Raprocure</title>
        <link href="{{ asset('public/assets/superadmin/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
        <!---css-->
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Toastr CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
        <!-- Toastr JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        @yield('css')
        @livewireStyles
        @stack('livewireStyles')

        @if (Auth::user()->user_type==3)
        <link href="{{ asset('public/assets/buyer/css/layout.css') }}" rel="stylesheet" />

        @elseif (Auth::user()->user_type==2)
        <link href="{{ asset('public/assets/vendor/css/layout.css') }}" rel="stylesheet" />

        @elseif (Auth::user()->user_type==1)

        <link href="{{ asset('public/assets/buyer/css/layout.css') }}" rel="stylesheet" />
        @endif

        <style>
            a.font-size-24 span.bi,
            button.font-size-24 span.bi {
                font-size: 24px !important;
                line-height: 1.6;
            }

            ul.accordian-submenu li a,
            .nav-text {
                font-size: 12px !important;
            }

            #mySidebar {
                margin-top: 10px;
            }

            .main {
                left: 0% !important;
            }

            li.bottom_user a i,
            li.notify-section a i {
                font-size: 22px !important;
            }

            @media only screen and (max-width: 767px) {

                .main-dashboard-page {
                    margin-left: 0rem;
                }
            }
        </style>

    </head>

    <body>

        @if (Auth::user()->user_type==3)
        @include('admin.layouts.navigation_second')
        @elseif (Auth::user()->user_type==2)
        @include('vendor.layouts.navigation')
        @elseif (Auth::user()->user_type==1)
        <div class="project_header sticky-top" id="project_header">
            @include('buyer.layouts.navigation')
        </div>
        @endif

        <div class="d-flex">
            @if (Auth::user()->user_type==3)
            <div class="bg-white">
                @include('admin.layouts.sidebar')
            </div>

            @elseif (Auth::user()->user_type==2)
            @include('vendor.layouts.sidebar')

            @elseif (Auth::user()->user_type==1)
            <div class="bg-white">
                @include('buyer.layouts.sidebar')
            </div>

            @endif

            <div class="main main-dashboard-page flex-grow-1" id="main1">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>

        <!-- Back to top button -->
        @if (Auth::user()->user_type==2)

        <button onclick="scrollToTop()" id="back-to-top-btn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>
        @else

        <button onclick="scrollToTop()" id="backToTopBtn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>
        @endif




        <!---bootsrap-->
        <script src="{{ asset('public/assets/superadmin/bootstrap/js/bootstrap.bundle.js') }}"></script>
        <!---local-js-->
        <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/46.1.0/ckeditor5.css" />

        @if (Auth::user()->user_type==3)
        <script src="{{ asset('public/assets/buyer/js/common.js') }}"></script>

        @elseif (Auth::user()->user_type==2)
        <script src="{{ asset('public/assets/vendor/js/common.js') }}"></script>

        @elseif (Auth::user()->user_type==1)
        <script src="{{ asset('public/assets/buyer/js/common.js') }}"></script>

        @endif

        <script>
            function limitCharacters(inputField, maxLength) {
          if (inputField.value.length > maxLength) {
              toastr.error(`Character limit exceeded! Maximum ${maxLength} characters allowed.`);
              inputField.value = inputField.value.substring(0, maxLength);
          }
      }
        </script>

        @include('buyer.layouts.app-js')

        @yield('scripts')

        @livewireScripts
        @stack('livewireScripts')
    </body>

</html>