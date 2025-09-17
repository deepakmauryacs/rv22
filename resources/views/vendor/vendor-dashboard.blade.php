@extends('vendor.layouts.app_first', ['title' => 'Dashboard', 'sub_title' => ''])
@section('title', 'Dashboard - Raprocure')
@section('content')
<section class="container-fluid">
  <div class="row pt-2">
    <div class="col-12 col-md-6 col-lg-6 mb-3 mb-sm-0">
      <div class="card card-dashboard">
        <div class="d-flex card-header bg-graident header-container justify-content-between align-items-center border-bottom-0 py-3">
          <h6 class="card-title text-white mb-0"><a href="">Products</a></h6>
          <a href="{{ route('vendor.products.index') }}" class="text-white font-size-13">See All Product</a>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table ra-table ra-table-stripped">
            <thead>
              <tr>
                <th scope="col">Product Name</th>
                <th scope="col">Division</th>
                <th scope="col">Category</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentProducts as $vendorProduct)
                @php
                  $product = $vendorProduct->product;
                  $productName = $vendorProduct->product_name ?? optional($product)->product_name;
                  $divisionName = optional(optional($product)->division)->division_name;
                  $categoryName = optional(optional($product)->category)->category_name;
                @endphp
                <tr>
                  <td><span>{{ $productName ?? 'N/A' }}</span></td>
                  <td><span>{{ $divisionName ?? 'N/A' }}</span> </td>
                  <td><span>{{ $categoryName ?? 'N/A' }}</span></td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center py-4">
                    <span>No products available.</span>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-6">
  <div class="p-0">
    <div id="carouselExampleInterval" class="carousel slide carousel-dashboard" data-bs-ride="carousel">
      <div class="carousel-inner">
        @forelse($advertisements as $index => $advertisement)
          <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" data-bs-interval="5000">
            <div class="ads-img">
              @php
                $img = !empty($advertisement->images)
                      ? asset('public/uploads/advertisment/' . $advertisement->images)
                      : asset('assets/images/DEFAULT_ADS_BANNER.jpeg');
                $fallback = asset('assets/images/DEFAULT_ADS_BANNER.jpeg');
              @endphp

              @if(!empty($advertisement->ads_url))
                <a href="{{ $advertisement->ads_url }}" target="_blank" rel="noopener">
              @endif

                <img
                  src="{{ $img }}"
                  alt="{{ $advertisement->buyer_vendor_name ?? 'Advertisement' }}"
                  class="w-100"
                  style="object-fit:cover;max-height:260px"
                  onerror="this.onerror=null;this.src='{{ $fallback }}';"
                />

              @if(!empty($advertisement->ads_url))
                </a>
              @endif
            </div>
          </div>
        @empty
          {{-- Default shown twice so carousel controls work --}}
          <div class="carousel-item active" data-bs-interval="5000">
            <div class="ads-img">
              <img
                src="{{ asset('public/assets/images/DEFAULT_ADS_BANNER.jpeg') }}"
                alt="Advertisement"
                class="w-100"
                style="object-fit:cover;"
              />
            </div>
          </div>
          <div class="carousel-item" data-bs-interval="5000">
            <div class="ads-img">
              <img
                src="{{ asset('public/assets/images/DEFAULT_ADS_BANNER.jpeg') }}"
                alt="Advertisement"
                class="w-100"
                style="object-fit:cover;"
              />
            </div>
          </div>
        @endforelse
      </div>

      {{-- Always show controls if ads exist, OR if we’re in @empty (2 default slides) --}}
      @if(($advertisements && $advertisements->count() > 1) || $advertisements->isEmpty())
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleInterval" data-bs-slide="prev">
          <span class="btn-prev-slider" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleInterval" data-bs-slide="next">
          <span class="btn-next-slider" aria-hidden="true"></span>
        </button>
      @endif
    </div>
  </div>
</div>


</div>
</section>
@endsection
