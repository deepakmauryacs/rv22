<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="author" content="">
        <meta property="og:image:size" content="300" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', session('page_title', 'Raprocure'))</title>
        <!---favicon-->
        <!-- <link rel="shortcut icon" type="image/png" sizes="16x16" href="//{{ asset('inventoryAssets/uploads/web_setup/raprocure-fevicon.png') }}"> -->
        <link rel="shortcut icon" href="{{ asset('public/assets/images/favicon/raprocure-fevicon.ico') }}"
            type="image/x-icon">
        <!-- jQuery (Must be first) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <!-- Toastr CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <!-- DataTables CSS -->
        <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> -->
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.4/css/jquery.dataTables.min.css" />

        <!-- Custom css -->
        <link href="{{ asset('public/assets/inventoryAssets/css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('public/assets/inventoryAssets/fontawesome/css/all.css') }}" rel="stylesheet">
        <!--datetime picker-->
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />

        <!-- Custom css -->
        <link href="{{ asset('public/assets/inventoryAssets/css/layout.css') }}" rel="stylesheet" />

        <!--bootstrip icon-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

        <!-- jQuery Validation Plugin -->
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>


        @stack('styles')
        @stack('headJs')
    </head>

    <body>
        <!---Header part-->

        <div class="project_header sticky-top">
            <header class="Project_top_header">
                <div class="container-fluid">
                    <div class="cust_container">
                        <div class="top_head row align-items-center">
                            <div class="col-4 col-md-1 col-lg-2 col-xl-4 top-head-left">
                                <h5 title="{{session('legal_name')}}">{{ (strlen(session('legal_name')) > 50) ?
                                    substr(session('legal_name'), 0, 50) . '...' : session('legal_name') }}</h5>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 col-xl-4 d-lg-block d-none top-head-middle">
                                <h4 class="text-center">Welcome to Raprocure!</h4>
                            </div>
                            <div class="col-12 col-md-12 col-lg-6 col-xl-4 top-head-right">
                                <div class="toggole pt-sm-1 position-absolute">
                                    <a a="" href="javascript:void(0)" onclick="openNav()">
                                        <span class="visually-hidden-focusable">Menu</span>
                                        <span class="bi bi-list font-size-22 fw-bold" aria-hidden="true"></span>
                                    </a>
                                </div>
                                <p class="text-white show_bothNo ms-4 ms-lg-0">
                                    Helpline No.: 9088880077 / 9088844477
                                </p>
                                <div class="dropdown user">
                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                        aria-expanded="false" title="{{Auth::user()->name}}">
                                        <span class="bi bi-person-fill" aria-hidden="true"></span>
                                        {{ (strlen(Auth::user()->name) > 10) ? substr(Auth::user()->name, 0, 10) . '...'
                                        : Auth::user()->name }}
                                    </a>
                                    <ul class="dropdown-menu user_logout">
                                        <li><a class="dropdown-item" href="{{ route('user.logout') }}">Logout</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        </div>




        <div class="d-flex">
            <!-- Section Sidebar -->
            <div class="bg-white">
                <!---Sidebar-->
                <div>
                    <a href="javascript:void(0)" class="menubtn" onclick="openNav()">MENU</a>
                </div>
                <aside class="sidebar sidebar-inner-page" id="mySidebar">
                    <div class="page-slider">
                        <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i
                                class="bi bi-x font-size-22"></i></a>
                        <aside class="sidebar" id="mySidebar">
                            <div class="page-slider">
                                <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i
                                        class="bi bi-x font-size-22"></i></a>
                                @include('buyer.layouts.sidebar')
                            </div>
                        </aside>
                    </div>
                </aside>
            </div>

            <!---Section Main-->
            <main class="main flex-grow-1">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </main>



        </div>

        <!-- Back to top button -->
        <button onclick="scrollToTop()" id="backToTopBtn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>

        <!-- Bootstrip JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



        <!-- Toastr JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

        <!-- DataTables JS -->
        <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.4/js/jquery.dataTables.min.js"></script>

        <!-- FontAwesome JS -->
        <script src="{{ asset('public/assets/inventoryAssets/fontawesome/js/all.js') }}"></script>

        <!-- DateTime Picker JS -->
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js">
        </script>

        <!-- XLSX excel JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


        <!-- Custom JS -->
        <script src="{{ asset('public/assets/inventoryAssets/js/script.js') }}"></script>
        <script src="{{ asset('public/js/manuallyAcceptPasteLogic.js') }}"></script>
        <script src="{{ asset('public/assets/inventoryAssets/js/common.js') }}"></script>

        <script>
            $(document).ready(function() {
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @elseif(session('error'))
                toastr.error("{{ session('error') }}");
            @elseif(session('warning'))
                toastr.warning("{{ session('warning') }}");
            @elseif(session('info'))
                toastr.info("{{ session('info') }}");
            @endif
        });
        </script>
        <script>
            function openNav() {
        const sidebar = document.getElementById("mySidebar");
        sidebar.style.transform = "translateX(0)";
        sidebar.classList.add("onClickMenuSidebar"); // Add 'open' class
      }

      function closeNav() {
        const sidebar = document.getElementById("mySidebar");
        sidebar.style.transform = "translateX(-115%)";
        sidebar.classList.remove("onClickMenuSidebar"); // Remove 'open' class

        let wasMobileView = window.innerWidth <= 768;
        window.addEventListener('resize', function () {
          const isMobileView = window.innerWidth <= 768;
          if (wasMobileView && !isMobileView) {
            closeNav();
          }
          wasMobileView = isMobileView;
        });
      }
        </script>
        @yield('scripts')
        @stack('exJs')
    </body>

</html>