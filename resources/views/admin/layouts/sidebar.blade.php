<div class="sidebar" id="mySidebar">
                <div class="page-slider">
                    <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i class="bi bi-x-lg"></i></a>
                    <div class="sidebar-menu-list accordion" id="sidebarAccordion">
                        <div class="dashboard_text">
                            <a href="{{ route('admin.dashboard') }}" class="menu-text-colour">
                                <span class="sidebar-icon"><i class="bi bi-x-diamond-fill  sidebar-bi"></i></span>
                                <span class="nav_text">Dashboard</span></a>
                        </div>

                        @if(checkPermission('PRODUCT_DIRECTORY','view','3'))
                        <div>
                            <a href="{{ route('admin.divisions.index') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-speedometer2 sidebar-bi"></i></span>
                                <span class="nav_text">Product Directory</span></a>
                        </div>
                        @endif

                        @php
                            $manageProducts = checkPermission('ALL_VERIFIED_PRODUCTS','view','3') ||
                                              checkPermission('PRODUCTS_FOR_APPROVAL','view','3') ||
                                              checkPermission('NEW_PRODUCT_REQUEST','view','3') ||
                                              checkPermission('EDIT_PRODUCT','view','3');
                        @endphp
                        @if($manageProducts)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    <span class="sidebar-icon"><i class="bi bi-layers sidebar-bi"></i></span>
                                    <span class="nav_text"> Manage products</span>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#sidebarAccordion">
                                <div class="accordion-body">
                                    <ul class="accordian-submenu">
                                        @if(checkPermission('ALL_VERIFIED_PRODUCTS','view','3'))
                                        <li><a href="{{ route('admin.verified-products.index') }}">All Verified
                                                Products</a></li>
                                        @endif
                                        @if(checkPermission('PRODUCTS_FOR_APPROVAL','view','3'))
                                        <li><a href="{{ route('admin.product-approvals.index') }}">Products for
                                                Approval</a></li>
                                        @endif
                                        @if(checkPermission('NEW_PRODUCT_REQUEST','view','3'))
                                        <li><a href="{{ route('admin.new-products.index') }}">New Product Request</a>
                                        </li>
                                        @endif
                                        @if(checkPermission('EDIT_PRODUCT','view','3'))
                                        <li><a href="{{ route('admin.edit-products.index') }}">Edit Product</a></li>
                                        @endif
                                        @if(checkPermission('EDIT_PRODUCT','view','3'))
                                        <li><a href="{{ route('admin.bulk-products.index') }}">Products for Approval
                                                Bulk</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(checkPermission('BUYER_MODULE','view','3'))
                        <div>
                            <a href="{{ route('admin.buyer.index') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-bag sidebar-bi"></i></span>
                                <span class="nav_text">Buyer Module</span></a>
                        </div>
                        @endif

                        @if(checkPermission('VENDOR_MODULE','view','3'))
                        <div>
                            <a href="{{ route('admin.vendor.index') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-shop-window sidebar-bi"></i></span>
                                <span class="nav_text"> Vendor Module</span></a>
                        </div>
                        @endif

                        @if(checkPermission('ADVERTISEMENT_AND_MARKETING','view','3'))
                        <div>
                            <a href="{{ route('admin.advertisement.index') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-megaphone sidebar-bi"></i></span>
                                <span class="nav_text">Advertisement/Marketing</span>
                            </a>
                        </div>
                        @endif
                        @php
                            $accountsModule = checkPermission('BUYERS_ACCOUNTS','view','3') ||
                                              checkPermission('VENDORS_ACCOUNTS','view','3');
                        @endphp
                        @if($accountsModule)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <span class="sidebar-icon"><i class="bi bi-person-plus sidebar-bi"></i></span>
                                    <span class="nav_text">Accounts Module</span>
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                data-bs-parent="#sidebarAccordion">
                                <div class="accordion-body">
                                    <ul class="accordian-submenu">
                                        @if(checkPermission('BUYERS_ACCOUNTS','view','3'))
                                        <li><a href="{{ route('admin.accounts.buyer') }}">Buyer's Accounts</a></li>
                                        @endif
                                        @if(checkPermission('VENDORS_ACCOUNTS','view','3'))
                                        <li><a href="{{ route('admin.accounts.vendor') }}">Vendor's Accounts</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(checkPermission('PLAN_MODULE','view','3'))
                        <div>
                            <a href="{{ route('admin.plan.index') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-columns sidebar-bi"></i></span>
                                <span class="nav_text">Plan Module </span></a>
                        </div>
                        @endif
                        @php
                            $reportsModule = checkPermission('DIVISION_AND_CATEGORY_WISE','view','3') ||
                                             checkPermission('BUYER_ACTIVITY_REPORTS','view','3') ||
                                             checkPermission('VENDOR_ACTIVITY_REPORTS','view','3') ||
                                             checkPermission('BUYER_REPORTS','view','3') ||
                                             checkPermission('VENDOR_REPORTS','view','3');
                        @endphp
                        @if($reportsModule)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <span class="sidebar-icon"><i class="bi bi-file-earmark-text sidebar-bi"></i></span>
                                    <span class="nav_text">Reports</span>
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                data-bs-parent="#sidebarAccordion">
                                <div class="accordion-body">
                                    <ul class="accordian-submenu" aria-labelledby="headingThree"
                                        data-bs-parent="#sidebarAccordion">
                                        @if(checkPermission('DIVISION_AND_CATEGORY_WISE','view','3'))
                                        <li><a href="{{route('admin.reports.product-division-category')}}">Products
                                                Division & Category Wise</a></li>
                                        @endif
                                        @if(checkPermission('BUYER_ACTIVITY_REPORTS','view','3'))
                                        <li><a href="{{route('admin.reports.buyer-activity')}}">Buyer Activity
                                                Reports</a></li>
                                        @endif
                                        @if(checkPermission('VENDOR_ACTIVITY_REPORTS','view','3'))
                                        <li><a href="{{ route('admin.vendor-activity-report.index') }}">Vendor Activity
                                                Reports</a></li>
                                        @endif
                    @if(checkPermission('VENDOR_REPORTS','view','3'))
                                        <li><a href="{{ route('admin.vendor-disabled-product-report.index') }}">Vendor
                                                Disabled Products</a></li>
                                        @endif
                                        @if(checkPermission('BUYER_REPORTS','view','3'))
                                        <li><a href="{{ route('admin.rfq-summary-report.index') }}">RFQs Summary</a>
                                        </li>
                                        <li><a href="{{ route('admin.reports.auction-rfqs-summary') }}">Auction RFQs
                                                Summary</a></li>
                                        <li><a href="{{ route('admin.reports.forward-auctions-summary') }}">Forward Auction Reports</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                        @php
                            $userMgmt = checkPermission('ADMIN_USERS','view','3') || checkPermission('MANAGE_ROLE','view','3');
                        @endphp
                        @if($userMgmt)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <span class="sidebar-icon"><i class="bi bi-people"></i></span>
                                    <span class="nav_text">User Management</span>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#sidebarAccordion">
                                <div class="accordion-body">
                                    <ul class="accordian-submenu" aria-labelledby="headingTwo"
                                        data-bs-parent="#sidebarAccordion">
                                        @if(checkPermission('ADMIN_USERS','view','3'))
                                        <li><a href="{{ route('admin.users.index') }}">Admin User</a></li>
                                        @endif
                                        @if(checkPermission('MANAGE_ROLE','view','3'))
                                        <li><a href="{{ route('admin.user-roles.index') }}">Manage role</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(checkPermission('HELP_AND_SUPPORT','view','3'))
                        <div>
                            <a href="{{ route('admin.help_support.index') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-question-circle sidebar-bi"></i></span>
                                <span class="nav_text"> Help and Support</span>
                            </a>
                        </div>
                        @endif
                        @if(checkPermission('CHANGE_PASSWORD','view','3'))
                        <div>
                            <a href="{{ route('admin.password.change') }}" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-lock"></i></span>
                                <span class="nav_text">Change Password </span></a>
                        </div>
                        @endif

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <span class="sidebar-icon"><i class="bi bi-chat-left-text sidebar-bi"></i></span>
                                    <span class="nav_text"> Message</span>
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse show"
                                aria-labelledby="headingFour" data-bs-parent="#sidebarAccordion">
                                <div class="accordion-body">
                                    <ul class="accordian-submenu" aria-labelledby="headingThree"
                                        data-bs-parent="#sidebarAccordion">
                                        @include('message.message-menu')
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @if(checkPermission('BUYER_QUERY','view','3'))
                        <div>
                            <a href="#" class="menu-text-colour submenu-list">
                                <span class="sidebar-icon"><i class="bi bi-info-circle sidebar-bi"></i></span>
                                <span class="nav_text"> Buyer Query</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
