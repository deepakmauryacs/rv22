<a href="{{ route('webPage.home',['companyName'=>session()->get('company_slug')]) }}"
    class="ra-btn {{ request()->routeIs('webPage.home') || request()->routeIs('webPage.productDetail') || request()->routeIs('webPage.productList') ? 'ra-btn-primary' : 'mini-web-page-btn' }} py-2 rounded-0 d-inline-flex">Home</a>
<a href="{{ route('webPage.contactUs',['companyName'=>session()->get('company_slug')]) }}"
    class="{{ request()->routeIs('webPage.contactUs') ? 'ra-btn-primary' : 'mini-web-page-btn' }} ra-btn   py-2 rounded-0 d-inline-flex ms-2">Contact
    Us</a>