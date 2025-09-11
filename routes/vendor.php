<?php

use App\Http\Controllers\MessageController;

use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorProfileController;
use App\Http\Controllers\Vendor\CommonController;
use App\Http\Controllers\Vendor\MiniWebPageController;
use App\Http\Controllers\Vendor\HelpSupportController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\RfqReceivedController;
use App\Http\Controllers\Vendor\LiveAuctionRfqController;
use App\Http\Controllers\Vendor\LiveAuctionRfqSinglePriceController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Vendor\FastTrackProductController;
use App\Http\Controllers\Vendor\MultipleProductController;
use App\Http\Controllers\Vendor\ProductGalleryController;
use App\Http\Controllers\Vendor\NotificationController;
Route::name('vendor.')->group(function() {
    Route::middleware(['auth', 'validate_account', 'usertype:2'])->group(function () {

        // common routes
        Route::post('/get-state-by-country-id', [CommonController::class, 'getStateByCountryId'])->name('get-state-by-country-id');
        Route::post('/get-city-by-state-id', [CommonController::class, 'getCityByStateId'])->name('get-city-by-state-id');
        Route::post('/check_notification', [CommonController::class, 'notification'])->name('check_notification');

        Route::prefix('profile')->group(function() {
            Route::get('/', [VendorProfileController::class, 'index'])->name('profile');
            Route::post('/validate-vendor-gstin-vat', [VendorProfileController::class, 'validateVendorGSTINVat'])->name('validate-vendor-gstin-vat');
            Route::post('/save-vendor-profile', [VendorProfileController::class, 'saveVendorProfile'])->name('save-vendor-profile');
            Route::get('/profile-complete', [VendorProfileController::class, 'profileComplete'])->name('profile-complete');
        });
        Route::get('/change-password', [VendorProfileController::class, 'changePassword'])->name('password.change');
        Route::post('/update-password', [VendorProfileController::class, 'updatePassword'])->name('password.update');

        Route::middleware(['profile_verified'])->group(function () {
            Route::prefix('dashboard')->group(function() {
                Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
            });

            Route::get('web-pages', [MiniWebPageController::class, 'index'])->name('web-pages.index');
            Route::post('web-pages/store', [MiniWebPageController::class, 'store'])->name('web-pages.store');
            // add new routes here.....
            Route::prefix('help-support')->group(function() {
                Route::get('/', [HelpSupportController::class, 'index'])->name('help_support.index');
                Route::get('/create', [HelpSupportController::class, 'create'])->name('help_support.create');
                Route::post('/store', [HelpSupportController::class, 'store'])->name('help_support.store');
                Route::put('/update/{id}', [HelpSupportController::class, 'update'])->name('help_support.update');
                Route::post('/view', [HelpSupportController::class, 'view'])->name('help_support.view');
                Route::post('/list', [HelpSupportController::class, 'list'])->name('help_support.list');
            });

            Route::prefix('orders-confirmed')->group(function() {
                Route::get('/rfq-order', [OrderController::class, 'rfqOrder'])->name('rfq_order.index');
                Route::get('/rfq-order/show/{id}', [OrderController::class, 'rfqOrderView'])->name('rfq_order.show');
                Route::get('/rfq-order/print/{id}', [OrderController::class, 'rfqOrderPrint'])->name('rfq_order.print');

                Route::get('/direct-order', [OrderController::class, 'directOrder'])->name('direct_order.index');
                Route::get('/direct-order/show/{id}', [OrderController::class, 'directOrderView'])->name('direct_order.show');
                Route::get('/direct-order/print/{id}', [OrderController::class, 'directOrderPrint'])->name('direct_order.print');

                Route::post('upload/pi-attachment', [OrderController::class, 'uploadPiAttachment'])->name('upload.pi.attachment');
            });
            Route::prefix('rfq')->group(function() {
                Route::get('/rfq-received', [RfqReceivedController::class, 'index'])->name('rfq.received.index');

                Route::get('{rfq_id}/reply', [RfqReceivedController::class, 'showRfqReplyForm'])->name('rfq.reply');

                Route::post('/submit', [RfqReceivedController::class, 'submitRfq'])->name('rfq.submit');
                Route::get('/success/{rfq_id}', [RfqReceivedController::class, 'success'])->name('rfq.success');

                Route::post('/add-product-to-vendor-profile', [RfqReceivedController::class, 'addProductToVendorProfile'])->name('rfq.add-product-to-vendor-profile');


                Route::get('/live-auction', [LiveAuctionRfqController::class, 'index'])->name('rfq.live-auction.index');

            });

            Route::get('/live-auction/{rfqId}/offer', [LiveAuctionRfqController::class, 'rfqAuctionOffer'])
                    ->where('rfqId', '[A-Za-z0-9\-\_]+')
                    ->name('live-auction.offer');

            Route::post('/live-auction/rfq/submit', [LiveAuctionRfqController::class, 'submitAuctionPrice'])->name('live-auction.rfq.submit');


            Route::post('/live-auction/metrics',[LiveAuctionRfqController::class, 'liveMetrics'])->name('live-auction.rfq.metrics');
            Route::post('/live-auction/total-metrics',[LiveAuctionRfqSinglePriceController::class, 'liveMetricsTotal'])->name('live-auction.rfq.total-metrics');

            Route::post('/live-auction-singal-price/rfq/submit', [LiveAuctionRfqSinglePriceController::class, 'saveLotRfq'])->name('live-auction-singal-price.rfq.submit');



            Route::prefix('notification')->group(function() {
                Route::get('/', [NotificationController::class, 'index'])->name('notification.index');
            });


            Route::prefix('products')->group(function() {
                Route::get('/', [VendorProductController::class, 'index'])->name('products.index');
                Route::get('/create', [VendorProductController::class, 'create'])->name('products.create');
                Route::post('/store', [VendorProductController::class, 'store'])->name('products.store');
                // Route for editing a product
                Route::get('/vendor/products/{id}/edit', [VendorProductController::class, 'edit'])->name('products.edit');

                Route::put('/update/{id}', [VendorProductController::class, 'update'])->name('products.update');


                // Product Listing


                Route::get('manage-products/approved', [VendorProductController::class, 'approvedList'])->name('manage-products.approved');
                Route::get('manage-products/pending', [VendorProductController::class, 'pendingList'])->name('manage-products.pending');


                // Update Status (AJAX)
                Route::post('product-approvals/{id}/status', [VendorProductController::class, 'updateStatus'])->name('product-approvals.status');

                // Delete Product (AJAX)
                Route::delete('product-approvals/{id}', [VendorProductController::class, 'destroy'])->name('admin.product-approvals.destroy');

                Route::get('get-categories-by-division/{division_id}', [VendorProductController::class, 'getCategoriesByDivision'])->name('getCategoriesByDivision');

                Route::get('product-autocomplete', [VendorProductController::class, 'autocomplete'])->name('product.autocomplete');

                Route::get('add-fast-track-product', [FastTrackProductController::class, 'index'])->name('products.fast_track_product');

                Route::post('products/new_search_product_for_supplier', [FastTrackProductController::class, 'newSearchProductForSupplier'])->name('products.new_search_product_for_supplier');
                Route::get('fasttrack/products/autocomplete', [FastTrackProductController::class, 'autocomplete'])->name('fasttrack.products.autocomplete');

                Route::post('fasttrack/products/store', [FastTrackProductController::class, 'storeFastTrackProducts'])->name('fasttrack.products.store');

               // For Multiple Products
               Route::get('add-multiple-product', [MultipleProductController::class, 'index'])->name('products.add_multiple_product');

               Route::any('add-multiple/products/autocomplete', [MultipleProductController::class, 'autocomplete'])->name('addmultiple.products.autocomplete');

               Route::post('add-multiple/products/store', [MultipleProductController::class, 'storeMultipleProducts'])->name('addmultiple.products.store');

                // Product Gallery Routes
                Route::get('products/{product}/gallery', [ProductGalleryController::class, 'create'])
                    ->name('products.gallery');
                Route::post('products/{product}/gallery/', [ProductGalleryController::class, 'store'])
                    ->name('products.gallery.store');
                Route::post('products/gallery/upload-temp', [ProductGalleryController::class, 'uploadTemp'])
                    ->name('products.gallery.upload-temp');
                Route::post('products/gallery/remove-temp', [ProductGalleryController::class, 'removeTemp'])
                    ->name('products.gallery.remove-temp');
                Route::delete('products/{product}/gallery', [ProductGalleryController::class, 'destroy'])
                    ->name('products.gallery.destroy');

            });


            Route::prefix('forward-auction')->group(function() {
                Route::get('/list', [\App\Http\Controllers\Vendor\ForwardAuctionController::class, 'index'])->name('forward-auction.index');
                Route::get('reply/{auction}', [\App\Http\Controllers\Vendor\ForwardAuctionController::class, 'auctionReply'])->name('forward-auction.view');
                Route::post('/submit-reply', [\App\Http\Controllers\Vendor\ForwardAuctionController::class, 'submitForwardReply'])->name('forward-auction.submit-reply');
                Route::post('/get-live-ranks', [\App\Http\Controllers\Vendor\ForwardAuctionController::class, 'getLiveRanks'])->name('forward-auction.get-live-ranks');
                Route::post('/check-bid-rank', [\App\Http\Controllers\Vendor\ForwardAuctionController::class, 'checkBidRank'])->name('forward-auction.check-bid-rank');
            });

            Route::prefix('message')->group(function () {
                Route::controller(MessageController::class)->group(function () {
                    Route::get('/', 'index')->name('message.index');
                });
            });

        });

    });
});
