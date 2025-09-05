@extends('buyer.layouts.app', ['title'=>'Add Your Vendor'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header py-3 bg-white">
                    <h1 class="font-size-18 mb-0">Vendor Search By Name</h1>
                </div>
                <div class="vendroe-search-page card-body pt-4">
                    <div class="px-md-2">
                        <div class="product-serach-box w-100">
                            <form class="d-flex searchBar">
                                <span class="bi bi-search me-2"></span>
                                <input type="search" class="search-vendor-input" placeholder="Search Vendor">
                                <button class="btn p-0" type="submit" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                    aria-label="Start Voice Search" data-bs-original-title="Start Voice Search">
                                <span class="bi bi-mic-fill font-size-18"></span>
                                </button>
                            </form>
                        </div>
                        <ul class="search-vendor-box" style="display:block;">

                        </ul>
                    </div>
                </div>
            </div>
            @if(0)
            <div class="card mt-5">
                <div class="card-header py-3 bg-white">
                    <h2 class="font-size-18 mb-0">Vendor Search By Category</h2>
                </div>
                <div class="vendroe-search-page card-body pt-4">
                    <div class="px-md-2">
                        <div class="product-serach-box w-100">
                            <form class="w-100">
                                <select class="form-select">
                                    <option> Select Category</option>
                                    <option>13-FEB-TEST</option>
                                    <option>65165</option>
                                    <option>874189</option>
                                    <option>AJIT KR</option>
                                    <option>AMIT1</option>
                                    <option>AUTOMOBILES</option>
                                    <option>BEARING</option>
                                    <option>BUG RE CHECK</option>
                                    <option>BULK CATEGORY</option>
                                    <option>BULK CATEGORY 2</option>
                                    <option>BULK CATEGORY 3</option>
                                    <option>BULK CATEGORY 4</option>
                                    <option>BULK CATEGORY 5</option>
                                    <option>CABLE &amp; ACCESSORIES</option>
                                    <option>CABLES</option>
                                    <option>CATDEL</option>
                                    <option>CATEGORY TESTING</option>
                                    <option>CIVIL</option>
                                    <option>COMPUTER</option>
                                    <option>CONSUMABLE</option>
                                    <option>CYLINDER</option>
                                    <option>DAIKIN</option>
                                    <option>ELECTRICAL</option>
                                    <option>EQUIPMENT</option>
                                    <option>FASTENER</option>
                                    <option>FEB KA CATEGORY</option>
                                    <option>FRIDGES</option>
                                    <option>GEAR BOX</option>
                                    <option>GIFT ITEMS</option>
                                    <option>HOSE PIPE</option>
                                    <option>HTTPSWWWMSN</option>
                                    <option>IT ACCESSORIES</option>
                                    <option>JIO 18-02-2025</option>
                                    <option>LABORATORY</option>
                                    <option>LIGHT FITTINGS</option>
                                    <option>LUBRICANT</option>
                                    <option>MEASURING DEVICES</option>
                                    <option>MECHANICAL</option>
                                    <option>MOBILE PHONES</option>
                                    <option>MOBILES</option>
                                    <option>MOTOR</option>
                                    <option>NEW CATEGORY</option>
                                    <option>PACKING &amp; SEALING</option>
                                    <option>PAINT</option>
                                    <option>PIPE FITTINGS &amp; VALVES</option>
                                    <option>PIPES</option>
                                    <option>POLLUTION </option>
                                    <option>PUMPS</option>
                                    <option>RAW MATERIAL</option>
                                    <option>REFRACTORY</option>
                                    <option>SAFETY</option>
                                    <option>SMARTPHONES</option>
                                    <option>SPECIAL CATEGORY</option>
                                    <option>SWITCH GEAR</option>
                                    <option>SWITCHES</option>
                                    <option>SYSADMIN CATEGORY SUBCATEGORY ADD418</option>
                                    <option>TEST CAT PINGKI</option>
                                    <option>TEST23</option>
                                    <option>TESTING DATA</option>
                                </select>
                            </form>
                        </div>
                        <ul class="search-vendor-box" style="display:block;">
                            <li>
                                <p class="green-text">Please enter more than 3 characters.</p>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue  vendor-name-left" title="TESTING VENDOR">TESTING VENDOR</a>
                                    <a href="#" title="ALLEN BOLT HT,HT HEX BOLT,SHOULDER SCREW">ALLEN BOLT HT,HT HEX BOLT,SHOULDER
                                    SCREW</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart-fill"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="TEST VENDOR TWO TWO PVT LTD">TEST VENDOR TWO TWO PVT
                                    LTD</a>
                                    <a title="TYRES,EARTHMOVING EQUIPMENT,HYDRA">TYRES,EARTHMOVING EQUIPMENT,HYDRA</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="MY TEST GURU VENDOR PVT LTD">MY TEST GURU VENDOR PVT
                                    LTD</a>
                                    <a title="FILTER LUB OIL SUPER BYPASS TYPE,SPRAY PIPE 1ST ZONE,ALIGNMENT ROLLER">FILTER LUB OIL
                                    SUPER
                                    BYPASS TYPE,SPRAY PIPE 1ST ZONE,ALIGNMENT ROLLER</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban text-danger"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="TEST VENDOR COMPANY">TEST VENDOR COMPANY</a>
                                    <a title="DP TEST MATERIALS,GLASS INSULATED COPPER LEAD WIRE,PRODUCT NEW FOR TEST MATERIALS">DP TEST
                                    MATERIALS,GLASS INSULATED COPPER LEAD WIRE,PRODUCT NEW FOR TES...</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue  vendor-name-left" title="TESTING VENDOR">TESTING VENDOR</a>
                                    <a href="#" title="ALLEN BOLT HT,HT HEX BOLT,SHOULDER SCREW">ALLEN BOLT HT,HT HEX BOLT,SHOULDER
                                    SCREW</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="TEST VENDOR TWO TWO PVT LTD">TEST VENDOR TWO TWO PVT
                                    LTD</a>
                                    <a title="TYRES,EARTHMOVING EQUIPMENT,HYDRA">TYRES,EARTHMOVING EQUIPMENT,HYDRA</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="MY TEST GURU VENDOR PVT LTD">MY TEST GURU VENDOR PVT
                                    LTD</a>
                                    <a title="FILTER LUB OIL SUPER BYPASS TYPE,SPRAY PIPE 1ST ZONE,ALIGNMENT ROLLER">FILTER LUB OIL
                                    SUPER
                                    BYPASS TYPE,SPRAY PIPE 1ST ZONE,ALIGNMENT ROLLER</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="TEST VENDOR COMPANY">TEST VENDOR COMPANY</a>
                                    <a title="DP TEST MATERIALS,GLASS INSULATED COPPER LEAD WIRE,PRODUCT NEW FOR TEST MATERIALS">DP TEST
                                    MATERIALS,GLASS INSULATED COPPER LEAD WIRE,PRODUCT NEW FOR TES...</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue  vendor-name-left" title="TESTING VENDOR">TESTING VENDOR</a>
                                    <a href="#" title="ALLEN BOLT HT,HT HEX BOLT,SHOULDER SCREW">ALLEN BOLT HT,HT HEX BOLT,SHOULDER
                                    SCREW</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="TEST VENDOR TWO TWO PVT LTD">TEST VENDOR TWO TWO PVT
                                    LTD</a>
                                    <a title="TYRES,EARTHMOVING EQUIPMENT,HYDRA">TYRES,EARTHMOVING EQUIPMENT,HYDRA</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="MY TEST GURU VENDOR PVT LTD">MY TEST GURU VENDOR PVT
                                    LTD</a>
                                    <a title="FILTER LUB OIL SUPER BYPASS TYPE,SPRAY PIPE 1ST ZONE,ALIGNMENT ROLLER">FILTER LUB OIL
                                    SUPER
                                    BYPASS TYPE,SPRAY PIPE 1ST ZONE,ALIGNMENT ROLLER</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="w-100">
                                    <a href="#" class="text-blue vendor-name-left" title="TEST VENDOR COMPANY">TEST VENDOR COMPANY</a>
                                    <a title="DP TEST MATERIALS,GLASS INSULATED COPPER LEAD WIRE,PRODUCT NEW FOR TEST MATERIALS">DP TEST
                                    MATERIALS,GLASS INSULATED COPPER LEAD WIRE,PRODUCT NEW FOR TES...</a>
                                </p>
                                <div class="d-flex">
                                    <button type="button" class="like-btn bg-transparent border-0 p-0"><span class="bi bi-heart"
                                        aria-hidden="true"></span></button>
                                    <button type="button" class="bg-transparent border-0 p-0"><span class="bi bi-ban"
                                        aria-hidden="true"></span></button>
                                </div>
                            </li>
                            <li>
                                <p class="dotted-loader-div w-100"><span class="loader-dotted"></span></p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </main>
@endsection

@section('scripts')
    <script>
    $('.search-vendor-input').on('input', function() {
        let input = $(this).val();
        console.log(input);
        if (input.length > 0 && input.length < 3) {
            $('.search-vendor-box').html('<li><p class="green-text">Please enter more than 3 characters.</p></li>');
        } else {
            $.ajax({
                url: "{{route('buyer.search-vendor.search')}}",
                type: "POST",
                dataType: "json",
                data: {
                    search: input,
                    _token: "{{ csrf_token() }}"
                },
                sendBefore: function() {
                    $('.search-vendor-box').html('<li><p class="dotted-loader-div w-100"><span class="loader-dotted"></span></p></li>');
                },
                success: function(response) {
                    $('.search-vendor-box').css('display', 'block');
                    $('.search-vendor-box').html(response);
                },
                error: function(error) {
                    console.log(error);
                },
                complete: function() {
                }
            })
        }
    });
    function manageVendor(e, types) {
        let vendorId = $(e).parent().attr('data-id');
        $.ajax({
            url: "{{route('buyer.search-vendor.favourite-blacklist')}}",
            type: "POST",
            dataType: "json",
            data: {
                vendor_id: vendorId,
                types: types,
                _token: "{{ csrf_token() }}"
            },
            sendBefore: function() {
            },
            success: function(response) {
                $(e).parent().html(response);
            },
            error: function(error) {
                console.log(error);
            },
            complete: function() {
            }
        });
    }

    </script>
@endsection
