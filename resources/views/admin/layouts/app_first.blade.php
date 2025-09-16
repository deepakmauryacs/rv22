<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta property="og:image:size" content="300" />
    <link rel="shortcut icon" href="{{ asset('public/assets/superadmin/favicon/raprocure-fevicon.ico') }}" type="image/x-icon"/>
    <title>{{$title??''}} {{$sub_title??''}} - Raprocure</title>
    <link href="{{ asset('public/assets/superadmin/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <!---css-->
    <link href="{{ asset('public/assets/superadmin/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/layout.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/custom.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/dashboard.css') }}" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @yield('css')
</head>

<body class="vh-100 d-flex flex-column">
    <div class="project_header sticky-top" id="project_header">
        <header class="Project_top_header">
            <div class="container-fluid">
                <div class="top_head row align-items-center">
                    <div class="col-4 col-md-1 col-lg-2 col-xl-4 top-head-left"></div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-4 d-lg-block d-none top-head-middle">
                        <h4 class="text-center">Welcome to Raprocure!</h4>
                    </div>
                    <div class="col-12 col-md-12 col-lg-6 col-xl-4 top-head-right">
                        <p class="text-white show_bothNo">
                            Helpline No.: 9088880077 / 9088844477
                        </p>
                        <h5>Raprocure Support</h5>
                    </div>
                </div>
            </div>
        </header>
        <div class="project_bottom_header">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="row btm_heada">
                        <div class="col-lg-2 col-md-6 col-sm-5 col-5 navbar-logo bottom-header-left">
                            <a class="logo-brand p-0" href="{{route("admin.dashboard")}}">
                                <img alt=" " class="brand-logo-img"
                                    src="{{ asset('public/assets/superadmin/images/rfq-logo.png') }}" />
                            </a>
                        </div>
                        <div class="col-lg-8 col-md-1 col-sm-1 col-1 bottom-header-center d-lg-none">
                            <a href="javascript:void(0)" onclick="openNav()"><i class="bi bi-list"></i></a>
                        </div>
                        <div class="col-lg-2 col-md-5 col-sm-6 col-6 bottom-header-end">
                            <ul>
                                <li class="notify-section">
                                    <a href="javascript:void(0)" onclick="setNotify(event)" id="notifyButton"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notification">
                                        <i class="bi bi-bell"></i>
                                        <span class="notification-number">1</span>
                                    </a>
                                    <div class="bell_messages" id="Allnotification_messages">
                                        <div class="message_wrap">
                                            <div class="message-wrapper Nblue">
                                                <div class="message-detail">
                                                    <a href="javascript:void(0)">
                                                        <div class="message-head-line">
                                                            <div class="person_name">
                                                                <span>A KUMAR</span>
                                                            </div>
                                                            <p class="message-body-line">
                                                                26 Mar, 2025 05:12 PM
                                                            </p>
                                                        </div>
                                                        <p class="message-body-line">
                                                            'A KUMAR' has responded to your RFQ No.
                                                            RATB-25-00046. You can check their quote here
                                                        </p>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="message-wrapper Npink">
                                                <div class="message-detail">
                                                    <a>
                                                        <div class="message-head-line">
                                                            <div class="person_name">
                                                                <span>A KUMAR</span>
                                                            </div>
                                                            <p class="message-body-line">
                                                                26 Mar, 2025 05:12 PM
                                                            </p>
                                                        </div>
                                                        <p class="message-body-line">
                                                            'A KUMAR' has responded to your RFQ No.
                                                            RATB-25-00046. You can check their quote here
                                                        </p>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="message-wrapper Nyellow">
                                                <div class="message-detail">
                                                    <a>
                                                        <div class="message-head-line">
                                                            <div class="person_name">
                                                                <span>TEST AMIT VENDOR</span>
                                                            </div>
                                                            <p class="message-body-line">
                                                                26 Mar, 2025 04:35 PM
                                                            </p>
                                                        </div>
                                                        <p class="message-body-line">
                                                            'TEST AMIT VENDOR' has responded to your RFQ No.
                                                            RATB-25-00046. You can check their quote here
                                                        </p>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="message-wrapper Ngreen">
                                                <div class="message-detail">
                                                    <a>
                                                        <div class="message-head-line">
                                                            <div class="person_name">
                                                                <span>A KUMAR</span>
                                                            </div>
                                                            <p class="message-body-line">
                                                                26 Mar, 2025 04:35 PM
                                                            </p>
                                                        </div>
                                                        <p class="message-body-line">
                                                            'A KUMAR' has responded to your RFQ No.
                                                            RATB-25-00046. You can check their quote here
                                                        </p>
                                                    </a>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.notification.index') }}">View All Notification</a>
                                        </div>
                                    </div>
                                </li>

                                <li class="notify-section">
                                    <a href="{{ route('admin.help_support.index') }}" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="Support">
                                        <i class="bi bi-question-circle"></i>
                                    </a>
                                </li>
                                <li class="bottom_user">
                                    <a href="javascript:void(0)" class="d-flex align-items-center userImg"
                                        onclick="setLogout(event)" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="Logout">
                                        <i class="bi bi-person-circle"></i>
                                    </a>
                                    <div class="user_logout" id="user_logout">
                                        <form method="POST" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button type="submit"
                                                style="width: 100px;background: white;border: none;"><i
                                                    class="fa-solid fa-arrow-right-from-bracket"></i>
                                                Logout</button>
                                        </form>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard_page d-flex overflow-auto flex-grow-1">
        @include('admin.layouts.sidebar')
        <div class="main " id="main">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <!---bootsrap-->
    <script src="{{ asset('public/assets/superadmin/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <!---local-js-->
    <script src="{{ asset('public/assets/superadmin/js/common.js') }}"></script>
    <script>
        function limitCharacters(inputField, maxLength) {
            if (inputField.value.length > maxLength) {
                toastr.error(`Character limit exceeded! Maximum ${maxLength} characters allowed.`);
                inputField.value = inputField.value.substring(0, maxLength);
            }
        }
    </script>
    @yield('scripts')
    <script>
            setInterval(check_user_message, 15*60000); //poll every 60 second
            check_user_message();
            // Listen for the visibility change event to detect when the tab becomes active
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    // The tab just became active, so fetch notifications immediately
                    check_user_message();
                }
            });
            function check_user_message()
            {
                // Check if the document (tab) is currently visible
                if (document.hidden) {
                    return; // If the tab is not active, skip the AJAX call
                }
                $.ajax({
                    url: "{{ route('admin.check_notification') }}",
                    type: 'POST',
                    dataType  : 'JSON',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('.notification-number').text(response.count);
                        $('#Allnotification_messages').html(response.html);
                    }
                });
            }
            function readNotification(notification) {
                $.ajax({
                    url: "{{ route('update-notification-status') }}",
                    type: 'POST',
                    dataType  : 'JSON',
                    data: {
                        notification,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                    }
                });
            }
    </script>
</body>

</html>
