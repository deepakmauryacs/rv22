@extends('buyer.layouts.app-mini-web-page',['title'=>'Web Page','sub_title'=>'product not found'])

@section('content')

<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <div class="card rounded mini-web-page">
            <div class="card-body">
                <!-- Top Section -->
                <div class="row">
                    <div class="col-md-7">
                        <div class="buyer-info d-flex">


                            <div class="buyer-sort-desc">
                                <div class="d-flex">
                                    <div>

                                        <h1 class="font-size-18 mb-2">Page Not Found.</h1>
                                        <div class="d-flex">

                                        </div>


                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                    <!--:- menu -:-->
                    <div class="col-md-5 text-sm-end">
                        @include('web-page.menu')
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>


@endsection