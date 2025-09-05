<aside class="sidebar sidebar-dashboard-page" id="mySidebar">
    <div class="page-slider">
        <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i class="bi bi-x font-size-22"></i></a>
        <div class="sidebar-menu-list accordion custom-toggle-accordion" id="sidebarAccordion">
            <div class="{{ setActiveMenu('buyer.dashboard') }}">
                <a href="{{ route("buyer.dashboard") }}" class="menu-text-colour">
                <span class="sidebar_icon"><i class="bi bi-x-diamond-fill"></i></span>
                <span class="nav_text">Dashboard</span></a>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                        aria-expanded="false" aria-controls="collapseOne">
                    <span class="sidebar_icon"><i class="bi bi-journal-text"></i></span><span class="nav_text">RFQ</span>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
                    <div class="accordion-body">
                        <ul class="accordian-submenu">
                            {{-- <li><a href="javascript:void(0)">Generate New RFQ</a></li> --}}
                            <li><a href="javascript:void(0)">Generate Bulk RFQ</a></li>
                            <li><a href="{{ route('buyer.rfq.active-rfq') }}" class="{{ setActiveMenu('buyer.rfq.active-rfq') }}">Active RFQs / CIS</a></li>
                            <li><a href="{{ route('buyer.auction.index') }}" class="{{ setActiveMenu('buyer.auction.index') }}">Auction</a></li>
                            <li><a href="{{ route('buyer.rfq.draft-rfq') }}" class="{{ setActiveMenu('buyer.rfq.draft-rfq') }}">Draft RFQ</a></li>
                            <li><a href="{{route('buyer.rfq.scheduled-rfq')}}" class="{{ setActiveMenu('buyer.rfq.scheduled-rfq') }}">Scheduled RFQ</a></li>
                            <li><a href="{{ route('buyer.rfq.sent-rfq') }}" class="{{ setActiveMenu('buyer.rfq.sent-rfq') }}">Sent RFQ</a></li>
                            <li><a href="javascript:void(0)">Counter Offer</a></li>
                            <li><a href="javascript:void(0)"> Unapproved Orders </a></li>
                            <li><a href="{{route('buyer.rfq.order-confirmed')}}" class="{{ setActiveMenu('buyer.rfq.order-confirmed') }}"> Orders Confirmed </a></li>
                            <li><a href="{{route('buyer.rfq.pi-invoice')}}" class="{{ setActiveMenu('buyer.rfq.pi-invoice') }}"> PI's / Invoices </a></li>
                        </ul>
                    </div>
                </div>
            </div>
             <div class="accordion-item">
                  <h2 class="accordion-header" id="headingForwardAuction">
                        @php
                            $accordionClasses = setParentActiveMenu(['buyer.forward-auction.create', 'buyer.forward-auction.index']);
                        @endphp
                      <button class="{{ $accordionClasses['button_class'] }}" type="button"
                              data-bs-toggle="collapse"
                              data-bs-target="#collapseForwardAuction"
                              aria-expanded="{{ $accordionClasses['aria_expanded'] }}"
                              aria-controls="collapseForwardAuction">
                        <span class="sidebar_icon"><i class="bi bi-send"></i></span>
                        <span class="nav_text">Forward Auction</span>
                      </button>
                  </h2>
                  <div id="collapseForwardAuction" class="{{ $accordionClasses['collapse_class'] }}" aria-labelledby="headingForwardAuction" data-bs-parent="#sidebarAccordion">
                      <div class="accordion-body">
                            <ul class="accordian-submenu">
                              <li><a href="{{ route('buyer.forward-auction.create') }}" class="{{ setActiveMenu('buyer.forward-auction.create') }}">Create</a></li>
                              <li><a href="{{ route('buyer.forward-auction.index') }}" class="{{ setActiveMenu('buyer.forward-auction.index') }}">View</a></li>
                            </ul>
                      </div>
                  </div>
              </div>

              <div class="accordion-item">
                  <h2 class="accordion-header" id="headingVendors">
                        @php
                            $accordionClasses = setParentActiveMenu(['buyer.search-vendor.index', 'buyer.add-vendor.create', 'buyer.vendor.favourite', 'buyer.vendor.blacklist']);
                        @endphp
                      <button class="{{ $accordionClasses['button_class'] }}" type="button"
                              data-bs-toggle="collapse"
                              data-bs-target="#collapseVendors"
                              aria-expanded="{{ $accordionClasses['aria_expanded'] }}"
                              aria-controls="collapseVendors">
                        <span class="sidebar_icon"><i class="bi bi-people"></i></span>
                        <span class="nav_text">Vendors</span>
                      </button>
                  </h2>
                  <div id="collapseVendors" class="{{ $accordionClasses['collapse_class'] }}" aria-labelledby="headingVendors" data-bs-parent="#sidebarAccordion">
                      <div class="accordion-body">
                          <ul class="accordian-submenu">
                              <li><a href="{{route('buyer.search-vendor.index')}}" class="{{ setActiveMenu('buyer.search-vendor.index') }}">Vendor Search</a></li>
                              <li><a href="{{route('buyer.add-vendor.create')}}" class="{{ setActiveMenu('buyer.add-vendor.create') }}">Add Your Vendor</a></li>
                              <li><a href="{{route('buyer.vendor.favourite')}}" class="{{ setActiveMenu('buyer.vendor.favourite') }}">Favourites</a></li>
                              <li><a href="{{route('buyer.vendor.blacklist')}}" class="{{ setActiveMenu('buyer.vendor.blacklist') }}">Blocked / Blacklisted</a></li>
                          </ul>
                      </div>
                  </div>
              </div>

            {{-- <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    <span class="sidebar_icon"><i class="bi bi-clipboard2"></i></span>
                    <span class="nav_text">Reports</span>
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingThree">
                            <li><a href="javascript:void(0)">Total RFQ Created</a></li>
                            <li><a href="javascript:void(0)">Total RFQ Product Created</a></li>
                            <li><a href="javascript:void(0)">Products Ordered Details</a></li>
                            <li><a href="javascript:void(0)">Vendor Wise Activity</a></li>
                        </ul>
                    </div>
                </div>
            </div> --}}

            @if (Auth::user()->is_api_enable)
            <div>
                <a href="{{ route('buyer.apiIndent.list') }}" class="menu-text-colour submenu-list {{ setActiveMenu('buyer.apiIndent.list') }}">
                    <span class="sidebar_icon"><i class="bi bi-receipt"></i></span>
                    <span class="nav_text">Api Indent</span></a>
            </div>
            @else
            <div>
                <a href="{{ route('buyer.inventory.index') }}" class="menu-text-colour submenu-list {{ setActiveMenu('buyer.inventory.index') }}">
                    <span class="sidebar_icon"><i class="bi bi-receipt"></i></span>
                    <span class="nav_text">Inventory</span></a>
            </div>
            @endif

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    @php
                        $accordionClasses = setParentActiveMenu(['buyer.profile', 'buyer.setting.change-password']);
                    @endphp
                    <button class="{{ $accordionClasses['button_class'] }}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="{{ $accordionClasses['aria_expanded'] }}" aria-controls="collapseTwo">
                        <span class="sidebar_icon"><i class="bi bi-gear"></i></span><span class="nav_text">Setting</span>
                    </button>
                </h2>
                <div id="collapseTwo" class="{{ $accordionClasses['collapse_class'] }}" aria-labelledby="headingTwo">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingTwo">
                            <li><a href="{{ route("buyer.profile") }}" class="{{ setActiveMenu('buyer.profile') }}">My Profile</a></li>
                            <li><a href="{{ route("buyer.setting.change-password") }}" class="{{ setActiveMenu('buyer.setting.change-password') }}">Change Password</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSix">
                    @php
                        $accordionClasses = setParentActiveMenu(['buyer.user-management.users', 'buyer.role-permission.roles']);
                    @endphp
                    <button class="{{ $accordionClasses['button_class'] }}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseSix" aria-expanded="{{ $accordionClasses['aria_expanded'] }}" aria-controls="collapseSix">
                    <span class="sidebar_icon"><i class="bi bi-person-workspace"></i></span>
                    <span class="nav_text"> User Management</span>
                    </button>
                </h2>
                <div id="collapseSix" class="{{ $accordionClasses['collapse_class'] }}" aria-labelledby="headingSix">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingSix">
                            <li><a href="{{ route("buyer.user-management.users") }}" class="{{ setActiveMenu('buyer.user-management.users') }}">Manage Users</a></li>
                            <li><a href="{{ route("buyer.role-permission.roles") }}" class="{{ setActiveMenu('buyer.role-permission.roles') }}">Manage Role</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        <span class="sidebar_icon"><i class="bi bi-chat-dots"></i></span>
                        <span class="nav_text"> Message</span>
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse show" aria-labelledby="headingFour">
                    <div class="accordion-body">
                        <ul class="accordian-submenu pb-4" aria-labelledby="headingFour">
                            @include('message.message-menu')
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>