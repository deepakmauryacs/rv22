@extends('buyer.layouts.app', ['title'=>'Sent RFQ'])

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
            <div class="bg-white sent-rfq-page">
                <form id="filter-rfq" action="{{ route('buyer.rfq.sent-rfq') }}" method="GET">
                    <h3 class="card-head-line px-2">Sent RFQ</h3>
                    <div class="px-2">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="rfq_no" placeholder="" value="" />
                                        <label for="">RFQ No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-share"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="division" name="division">
                                            <option value="0" selected=""> Select </option>
                                            @foreach ($divisions as $item)
                                                <option value="{{ $item->id }}" {{ request('division')==$item->id ? 'selected' : '' }} >{{ $item->division_name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="">Division</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="category" name="category">
                                            <option value="0" selected=""> Select </option>
                                            @foreach ($unique_category as $item => $ids)
                                                <option value="{{ implode(',', $ids) }}" {{ request('category')==implode(',', $ids) ? 'selected' : '' }}>{{ $item }}</option>
                                            @endforeach
                                        </select>
                                        <label>Category</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="product_name" placeholder="" value="" />
                                        <label for="">Product Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="prn_number" placeholder="" value="" />
                                        <label for="">PRN Number</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span>
                                        Search
                                    </button>
                                    <button type="button" onclick="javascript:window.location.href='{{ route('buyer.rfq.sent-rfq') }}'" class="ra-btn small-btn ra-btn-outline-danger">
                                        <span class="bi bi-arrow-clockwise"></span>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.rfq.sent-rfq.partials.table', ['results' => $results])
                    <!-- <table class="product-listing-table w-100">
                        <thead>
                            <tr>
                                <th>RFQ No.</th>
                                <th>RFQ Date</th>
                                <th>Product Name</th>
                                <th>PRN Number</th>
                                <th>Branch/Unit</th>
                                <th>Username</th>
                                <th>RFQ Status</th>
                                <th>RFQ Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>RATB-25-00049</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBE
                                        </span>
                                        <button class="btn btn-link text-black border-0 p-0 font-size-12 bi bi-info-circle-fill ms-1"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="KILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBE"></button>
                                    </div>
                                </td>
                                <td></td>
                                <td> Buyer Branch 1</td>
                                <td> BUYER TESTER</td>
                                <td>
                                    <span class="rfq-status Auction-Completed">Closed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="list-unread">
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING PRODUCTS
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 2</td>
                                <td>User 2</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBE
                                        </span>
                                    </div>
                                </td>
                                <td>60</td>
                                <td>Branch 3</td>
                                <td>User 3</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary disabled">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        WATER TESTING KIT
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 4</td>
                                <td>User 4</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING
                                        </span>
                                    </div>
                                </td>
                                <td>64</td>
                                <td>Branch 5</td>
                                <td>User 5</td>
                                <td>
                                    <span class="rfq-status Partial-order">Order Confirmed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR
                                        </span>
                                    </div>
                                </td>
                                <td>21,22</td>
                                <td>Branch 6</td>
                                <td>User 6</td>
                                <td>
                                    <span class="rfq-status Partial-order">Partial Order</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light disabled">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>RATB-25-00049</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBE
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td> Buyer Branch 1</td>
                                <td> BUYER TESTER</td>
                                <td>
                                    <span class="rfq-status Auction-Completed">Closed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="list-unread">
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING PRODUCTS
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 2</td>
                                <td>User 2</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBE
                                        </span>
                                    </div>
                                </td>
                                <td>60</td>
                                <td>Branch 3</td>
                                <td>User 3</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary disabled">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        WATER TESTING KIT
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 4</td>
                                <td>User 4</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING
                                        </span>
                                    </div>
                                </td>
                                <td>64</td>
                                <td>Branch 5</td>
                                <td>User 5</td>
                                <td>
                                    <span class="rfq-status Partial-order">Order Confirmed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR
                                        </span>
                                    </div>
                                </td>
                                <td>21,22</td>
                                <td>Branch 6</td>
                                <td>User 6</td>
                                <td>
                                    <span class="rfq-status Partial-order">Partial Order</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light disabled">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table> -->
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <!-- jQuery UI -->
    <script>
    $(document).ready(function () {
        $(".clickable-td").click(function() {
            window.location.href = $(this).find('a').attr('href');
        });
        $(document).on('submit', '#filter-rfq', function(e) {
            e.preventDefault();
            loadTable($(this).attr('action') + '?' + $(this).serialize());
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            loadTable($(this).attr('href'));
        });

        $(document).on('change', '#perPage', function () {
            const form = $('#filter-rfq');
            const formData = form.serialize();
            const perPage = $(this).val();
            const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;
            loadTable(url);
        });

        function loadTable(url) {
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function () {
                    $('#table-container').html('<div class="text-center py-4">Loading...</div>');
                },
                success: function(response) {
                    $('#table-container').html(response);
                    if (history.pushState) {
                        history.pushState(null, null, url);
                    }
                }
            });
        }

        $(document).on("click", "#reset-filter", function(){
            $(".rfq-filter-button").find("input").val("");
            $(".rfq-filter-button").find("select").val("0");
        });

    });
</script>
@endsection