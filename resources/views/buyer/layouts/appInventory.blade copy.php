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

    <link rel="shortcut icon" type="image/png" sizes="16x16" href="{{ asset('inventoryAssets/uploads/web_setup/raprocure-fevicon.png') }}">
    <!-- jQuery (Must be first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- Custom css -->
    <link href="{{ asset('public/assets/inventoryAssets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/inventoryAssets/fontawesome/css/all.css') }}" rel="stylesheet">
    <!--datetime picker-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />

    <!-- Custom css -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/layout.css') }}" rel="stylesheet" />

    
    <!-- jQuery Validation Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>



    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
    @stack('headJs')
</head>

<body>
    <div class="dashboard_page">
        <div class="container-fluid">
            <header class="P_header">
                <div class="container-fluid">
                    <div class="top_head row align-items-center">
                        <div class="col-lg-4  col-md-6 col-12 d-md-block d-none">
                            <h5>AMIT STEEL PLANT</h5>
                        </div>
                        <div class="col-12 col-md-4 d-lg-block d-none">
                            <h4 class="text-center">Welcome to Raprocure!</h4>
                        </div>
                        <div class="col-lg-4  col-md-6 col-12 d-flex align-items-center justify-content-between">
                            <h6 class="hlp-no">Helpline No.: 9088880077 / 9088844477</h6>
                            <a href="#" class="toggole" onclick="openNav()"><i class="fa-solid fa-bars"></i></a>
                            <div class="user">
                                <a href="#" class="d-flex align-items-center gap-2" onclick="setLogout()">
                                    <i class="fa-solid fa-user"></i>
                                    <span>Jhon Doe<i class="fa-solid fa-angle-down ms-1"></i></span>
                                </a>
                                <div class="user_logout" id="user_logout"><a href="#">Logout</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
                @yield('content')
        </div>
    </div>
    <!-- Bootstrip JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!--bootstrip icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <!-- FontAwesome JS -->
    <script src="{{ asset('public/assets/inventoryAssets/fontawesome/js/all.js') }}"></script>

    <!-- DateTime Picker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>

    <!-- XLSX excel JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('public/assets/inventoryAssets/js/script.js') }}"></script>
    <script src="{{ asset('public/js/manuallyAcceptPasteLogic.js') }}"></script>

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
    @yield('scripts')
    @stack('exJs')
</body>
</html>
