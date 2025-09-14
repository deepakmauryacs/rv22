<?php

use App\Http\Controllers\Buyer\BuyerDashboardController;
use App\Http\Controllers\Buyer\BuyerProfileController;
use App\Http\Controllers\Buyer\CommonController;
use App\Http\Controllers\Buyer\CategoryController;
use App\Http\Controllers\Buyer\VendorProductController;
use App\Http\Controllers\Buyer\RFQDraftController;
use App\Http\Controllers\Buyer\RFQComposeController;
use App\Http\Controllers\Buyer\ComposeRFQController;
//start inventory Section
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Buyer\InventoryController;
use App\Http\Controllers\Buyer\InventoryVendorProductController;
use App\Http\Controllers\Buyer\BulkInventoryController;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\ReportsController;
use App\Http\Controllers\Buyer\IssuedController;
use App\Http\Controllers\Buyer\IndentController;
use App\Http\Controllers\Buyer\ProductWiseStockLedgerController;
use App\Http\Controllers\Buyer\GrnController;
use App\Http\Controllers\Buyer\IssueReturnController;
use App\Http\Controllers\Buyer\StockReturnController;
use App\Services\ExportService;
//end inventory section
use App\Http\Controllers\Buyer\SearchProductController;
use App\Http\Controllers\Buyer\ActiveRFQController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Buyer\PiController;
use App\Http\Controllers\Buyer\CISController;
use App\Http\Controllers\Buyer\UserManagementController;
use App\Http\Controllers\Buyer\RolePermissionController;
use App\Http\Controllers\Buyer\RFQUnapprovedOrderController;
use App\Http\Controllers\Buyer\AddYourVendorController;
use App\Http\Controllers\Buyer\ApiIndentController;
use App\Http\Controllers\Buyer\VendorSearchController;
use App\Http\Controllers\Buyer\PreferenceVendorController;
use App\Http\Controllers\Buyer\ForwardAuctionController;

use App\Http\Controllers\Buyer\NotificationController;
use App\Http\Controllers\Buyer\HelpSupportController;

use App\Http\Controllers\Buyer\TechnicalApprovalController;

use App\Http\Controllers\Buyer\AuctionController;

use App\Http\Controllers\Buyer\ScheduledRfqController;
use App\Http\Controllers\Buyer\OrderConfirmedController;

use App\Http\Controllers\Buyer\AuctionCISController;

Route::name('buyer.')->group(function () {

    Route::middleware(['auth', 'validate_account', 'usertype:1'])->group(function () {

        // common routes
        Route::post('/get-state-by-country-id', [CommonController::class, 'getStateByCountryId'])->name('get-state-by-country-id');
        Route::post('/get-city-by-state-id', [CommonController::class, 'getCityByStateId'])->name('get-city-by-state-id');
        Route::post('/check_notification', [CommonController::class, 'notification'])->name('check_notification');


        Route::prefix('profile')->group(function () {
            Route::get('/', [BuyerProfileController::class, 'index'])->name('profile');
            Route::post('/validate-buyer-gstin-vat', [BuyerProfileController::class, 'validateBuyerGSTINVat'])->name('validate-buyer-gstin-vat');
            Route::post('/validate-buyer-short-code', [BuyerProfileController::class, 'validateBuyerShortCode'])->name('validate-buyer-short-code');
            Route::post('/save-buyer-profile', [BuyerProfileController::class, 'saveBuyerProfile'])->name('save-buyer-profile');
            Route::get('/profile-complete', [BuyerProfileController::class, 'profileComplete'])->name('profile-complete');
        });

        Route::prefix('notification')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('notification.index');
        });
        Route::middleware(['profile_verified'])->group(function () {

            Route::prefix('dashboard')->group(function () {
                Route::get('/', [BuyerDashboardController::class, 'index'])->name('dashboard');
            });

            // Route::prefix('category')->group(function () {

            Route::prefix('setting')->group(function () {
                Route::get('change-password', [BuyerDashboardController::class, 'change_password'])->name('setting.change-password');
                Route::post('update-password', [BuyerDashboardController::class, 'updatePassword'])->name('setting.update-password');
            });
            Route::prefix('help-support')->group(function () {
                Route::get('/', [HelpSupportController::class, 'index'])->name('help_support.index');
                Route::get('/create', [HelpSupportController::class, 'create'])->name('help_support.create');
                Route::post('/store', [HelpSupportController::class, 'store'])->name('help_support.store');
                Route::put('/update/{id}', [HelpSupportController::class, 'update'])->name('help_support.update');
                Route::post('/view', [HelpSupportController::class, 'view'])->name('help_support.view');
                Route::post('/list', [HelpSupportController::class, 'list'])->name('help_support.list');
            });

            Route::prefix('category')->group(function () {
                Route::get('/category-product/{id}', [CategoryController::class, 'index'])->name('category.product');
                Route::post('/get-category-product', [CategoryController::class, 'getCategoryProduct'])->name('category.get-product');
            });

            Route::prefix('vendor-product')->group(function () {
                Route::get('/{id}', [VendorProductController::class, 'index'])->name('vendor.product');
                Route::get('details/{product_id}/{vendor_id}', [VendorProductController::class, 'vendorProductDetails'])->name('vendor.product.details');
            });

            Route::prefix('rfq')->group(function () {
                Route::post('draft/add', [RFQDraftController::class, 'addToDraft'])->name('rfq.add-to-draft');
                Route::get('compose/draft-rfq/{draft_id}', [RFQComposeController::class, 'index'])->name('rfq.compose-draft-rfq');
                Route::post('draft-rfq/add', [RFQDraftController::class, 'addToDraftRFQ'])->name('rfq.add-to-draft-rfq');
                Route::get('compose/rfq-success/{rfq_id}', [ComposeRFQController::class, 'composeRFQSuccess'])->name('rfq.compose-rfq-success');

                Route::get('active-rfq', [ActiveRFQController::class, 'index'])->name('rfq.active-rfq');
                Route::get('sent-rfq', [ActiveRFQController::class, 'sent_rfq'])->name('rfq.sent-rfq');
                Route::post('reuse-rfq', [ActiveRFQController::class, 'reuseRFQ'])->name('rfq.reuse');
                Route::get('rfq-details/{rfq_id}', [ActiveRFQController::class, 'rfq_details'])->name('rfq.details');
                Route::post('close-rfq', [ActiveRFQController::class, 'closeRFQ'])->name('rfq.close');
                Route::post('edit-rfq', [ActiveRFQController::class, 'editRFQ'])->name('rfq.edit');

                Route::get('draft-rfq', [RFQDraftController::class, 'index'])->name('rfq.draft-rfq');
                Route::get('pi-invoice', [PiController::class, 'index'])->name('rfq.pi-invoice');
                Route::get('cis-sheet/{rfq_id}', [CISController::class, 'index'])->name('rfq.cis-sheet');
                Route::post('last-cis-po', [CISController::class, 'last_cis_po'])->name('rfq.cis.last-cis-po');

                Route::get('counter-offer/{rfq_id}', [CISController::class, 'counter_offer'])->name('rfq.counter-offer');
                Route::get('quotation-received/{rfq_id}/{vendor_id}', [CISController::class, 'quotation_received'])->name('rfq.quotation-received');
                Route::get('counter-offer/success/{rfq_id}', [CISController::class, 'counter_offer_success'])->name('rfq.counter-offer-success');
                Route::get('quotation-received/print/{rfq_id}/{vendor_id}', [CISController::class, 'quotation_received_print'])->name('rfq.quotation-received.print');

                Route::get('scheduled-rfq', [ScheduledRfqController::class, 'index'])->name('rfq.scheduled-rfq');
                Route::post('scheduled-rfq/delete', [ScheduledRfqController::class, 'delete'])->name('rfq.scheduled-rfq.delete');
                Route::get('order-confirmed', [OrderConfirmedController::class, 'index'])->name('rfq.order-confirmed');
                Route::get('order-confirmed/view/{id}', [OrderConfirmedController::class, 'view'])->name('rfq.order-confirmed.view');
                Route::get('order-confirmed/print/{id}', [OrderConfirmedController::class, 'print'])->name('rfq.order-confirmed.print');
                Route::post('order-confirmed/cancel/{id}', [OrderConfirmedController::class, 'cancel'])->name('rfq.order-confirmed.cancel');

                Route::post('approve', [OrderConfirmedController::class, 'approve'])->name('rfq.unapproved-order.approve');

                Route::get('order-confirmed/export/total', [OrderConfirmedController::class, 'exportTotal'])->name('rfq.order-confirmed.exportTotal');
                Route::get('order-confirmed/export/batch', [OrderConfirmedController::class, 'exportBatch'])->name('rfq.order-confirmed.exportBatch');

            });


            Route::prefix('unapproved-orders')->group(function () {
                Route::get('list', [RFQUnapprovedOrderController::class, 'index'])->name('unapproved-orders.list');
                Route::get('create/{rfq_id}', [RFQUnapprovedOrderController::class, 'create'])->name('unapproved-orders.create');
                Route::post('generate-po', [RFQUnapprovedOrderController::class, 'generatePO'])->name('unapproved-orders.generatePO');
                Route::get('approve-po/{rfq_id}', [RFQUnapprovedOrderController::class, 'approvePO'])->name('unapproved-orders.approvePO');
                Route::post('export-po', [RFQUnapprovedOrderController::class, 'exportPOData'])->name('unapproved-orders.exportPOData');
                Route::post('delete-po', [RFQUnapprovedOrderController::class, 'deletePO'])->name('unapproved-orders.deletePO');
                Route::post('download-po/{rfq_id}', [RFQUnapprovedOrderController::class, 'downloadPOPdf'])->name('unapproved-orders.downloadPOPdf');
                Route::post('print', [RFQUnapprovedOrderController::class, 'print'])->name('unapproved-orders.print');
            });

            Route::prefix('user-management')->group(function () {
                Route::get('users', [UserManagementController::class, 'index'])->name('user-management.users');
                Route::get('add-user', [UserManagementController::class, 'create'])->name('user-management.create-user');
                Route::post('store-user', [UserManagementController::class, 'store'])->name('user-management.store-user');
                Route::get('edit-user/{id}', [UserManagementController::class, 'edit'])->name('user-management.edit-user');
                Route::put('update-user/{id}', [UserManagementController::class, 'update'])->name('user-management.update-user');
            });

            Route::prefix('role-permission')->group(function () {
                Route::get('roles', [RolePermissionController::class, 'index'])->name('role-permission.roles');
                Route::get('add-role', [RolePermissionController::class, 'create'])->name('role-permission.create-role');
                Route::post('store-role', [RolePermissionController::class, 'store'])->name('role-permission.store-role');
                Route::get('edit-role/{id}', [RolePermissionController::class, 'edit'])->name('role-permission.edit-role');
                Route::put('update-role/{id}', [RolePermissionController::class, 'update'])->name('role-permission.update-role');
                Route::delete('delete-role/{id}', [RolePermissionController::class, 'destroy'])->name('role-permission.destroy');
                Route::put('status-role/{id}', [RolePermissionController::class, 'updateStatus'])->name('role-permission.status');
            });

            Route::prefix('add-vendor')->group(function () {
                Route::get('/', [AddYourVendorController::class, 'index'])->name('add-vendor.create');
                Route::post('store', [AddYourVendorController::class, 'store'])->name('add-vendor.store');
            });

            Route::prefix('search-vendor')->group(function () {
                Route::get('/', [VendorSearchController::class, 'index'])->name('search-vendor.index');
                Route::post('search', [VendorSearchController::class, 'search'])->name('search-vendor.search');
                Route::post('favourite-block-vendor', [VendorSearchController::class, 'favouriteBlockVendor'])->name('search-vendor.favourite-blacklist');
            });

            Route::prefix('vendor')->group(function () {
                Route::get('favourite', [PreferenceVendorController::class, 'favourite'])->name('vendor.favourite');
                Route::get('blacklisted', [PreferenceVendorController::class, 'blacklist'])->name('vendor.blacklist');
                Route::delete('deleted/{id}', [PreferenceVendorController::class, 'deleted'])->name('vendor.deleted');
            });


            Route::prefix('ajax')->group(function () {

                Route::post('get-vendor-product', [VendorProductController::class, 'getVendorProduct'])->name('vendor.get-product');

                Route::post('compose/get-draft-product', [RFQComposeController::class, 'getDraftProduct'])->name('rfq.get-draft-product');
                Route::post('compose/search-selected-product', [RFQComposeController::class, 'searchSelectedProduct'])->name('rfq.search-selected-product');
                Route::post('compose/rfq-update-product', [RFQComposeController::class, 'updateProduct'])->name('rfq.update-product');
                Route::post('compose/rfq-update-draft', [RFQComposeController::class, 'updateDraftRFQ'])->name('rfq.update-draft');
                Route::post('compose/rfq-delete-product', [RFQComposeController::class, 'deleteProduct'])->name('rfq.delete-product');
                Route::post('compose/rfq-delete-product-variant', [RFQComposeController::class, 'deleteProductVariant'])->name('rfq.delete-product-variant');
                Route::post('compose/rfq-delete-draft', [RFQComposeController::class, 'deleteDraftRFQ'])->name('rfq.delete-draft');
                Route::post('compose/delete-edited-rfq', [RFQComposeController::class, 'deleteEditedRFQ'])->name('rfq.delete-edited-rfq');
                Route::post('compose/rfq/search-vendors', [RFQComposeController::class, 'searchVendors'])->name('rfq.search-vendors');
                Route::post('compose/rfq/add-vendor-to-rfq', [RFQComposeController::class, 'addVendorToRFQ'])->name('rfq.add-vendor-to-rfq');
                Route::post('compose/rfq-compose', [ComposeRFQController::class, 'composeRFQ'])->name('rfq.compose');
                Route::post('compose/rfq-update', [ComposeRFQController::class, 'updateRFQ'])->name('rfq.update');

                Route::post('search/vendor-product', [SearchProductController::class, 'searchVendorActiveProduct'])->name('search.vendor-product');
                Route::post('search-by-division', [SearchProductController::class, 'getSearchByDivision'])->name('search-by-division');

                Route::post('delete-draft-rfq', [RFQDraftController::class, 'deleteDraftRFQ'])->name('rfq.draft-rfq.delete-draft-rfq');

                Route::post('save-counter-offer/{rfq_id}', [CISController::class, 'save_counter_offer'])->name('rfq.save-counter-offer');

                Route::post('cis/save-technical-approval', [TechnicalApprovalController::class, 'save'])->name('cis.technical-approval.save');
            });

            // List auctions (index)
            Route::get('forward-auction', [ForwardAuctionController::class, 'index'])
                ->name('forward-auction.index');

            // Show create form
            Route::get('forward-auction/create', [ForwardAuctionController::class, 'create'])
                ->name('forward-auction.create');

            // Store new auction
            Route::post('forward-auction/save', [ForwardAuctionController::class, 'store'])
                ->name('forward-auction.store');

            // Edit auction
            Route::get('forward-auction/{id}/edit', [ForwardAuctionController::class, 'edit'])
                ->name('forward-auction.edit');

            // Update auction
            Route::any('forward-auction/{id}/update', [ForwardAuctionController::class, 'update'])
                ->name('forward-auction.update');

            // Custom AJAX endpoints
            Route::post('forward-auction/search-vendors/vendor', [ForwardAuctionController::class, 'searchVendors'])
                ->name('forward-auction.search_vendors');

            Route::any('forward-auction/get-product-suggestions/product', [ForwardAuctionController::class, 'getProductSuggestions'])
                ->name('forward-auction.get_product_suggestions');

            Route::post('forward-auction/get-booked-times/list', [ForwardAuctionController::class, 'getBookedTimes'])
                ->name('forward-auction.get-booked-times');

            Route::post('forward-auction/force_stop', [ForwardAuctionController::class, 'forceStop'])
                ->name('forward-auction.force_stop');

            Route::delete('/forward-auction/{auction_id}', [ForwardAuctionController::class, 'destroy'])->name('forward-auction.destroy');

            Route::get('forward-auction/view/{auction}', [ForwardAuctionController::class, 'view'])->name('forward-auction.show');

            Route::get('buyer/forward-auction/export-cis/{auction_id}', [ForwardAuctionController::class, 'exportCIS'])
                ->name('forward-auction.export-cis');


            Route::get('/auction/live-auction-rfq', [AuctionController::class, 'index'])->name('auction.index');

            Route::get('auction/live-auction-rfq/cis-sheet/{rfq_id}', [AuctionCISController::class, 'index'])->name('auction.cis-sheet');

            Route::post('/auction/create', [AuctionController::class, 'createAuction'])->name('auction.create');
            Route::post('/auction/get', [AuctionController::class, 'getAuction'])->name('auction.get');
            Route::get('/auction/live-rfqs', [AuctionController::class, 'liveAuctionRfq'])->name('auction.live-rfqs');
            Route::post('/auction/close', [AuctionController::class, 'closeAuction'])->name('auction.close');
            Route::get('/auction/cis/{rfq}', [AuctionController::class, 'buyerProductList'])->name('auction.cis');
            Route::get('cis-export/{rfq}', [AuctionController::class, 'exportBuyerCisSheetNew'])->name('buyer.auction.cis-export');
            Route::post('/auction/force-stop', [AuctionController::class, 'forceStop'])->name('auction.force-stop');
            Route::post('/auction/booked-times', [AuctionController::class, 'getBookedTimes'])->name('auction.booked-times');





            // Inventory Section check_api_enable
            Route::middleware(['check_api_enable'])->group(function () {
                Route::prefix('inventory')->group(function () {
                    // Inventory Routes
                    Route::name('inventory.')->group(function () {
                        Route::get('/', [InventoryController::class, 'index'])->name('index');
                        Route::post('/store', [InventoryController::class, 'store'])->name('store');

                        Route::get('/data', [InventoryController::class, 'getData'])->name('data');
                        Route::post('/export', [InventoryController::class, 'exportInventoryData'])->name('exportData');

                        Route::get('/{id}/edit', [InventoryController::class, 'edit'])
                            ->where('id', '[0-9]+')
                            ->name('edit');

                        Route::delete('/delete', [InventoryController::class, 'deleteInventory'])->name('delete');

                        Route::post('/resetIndentRfq', [InventoryController::class, 'resetInventory'])->name('reset');
                        Route::post('/getInventoryDetailsById', [InventoryController::class, 'getInventoryDetails'])->name('getDetailsByID');

                        Route::post('/fetchInventoryDetailsForAddRfq', [InventoryController::class, 'fetchInventoryDetailsForAddRfq'])->name('fetchInventoryDetailsForAddRfq');
                        Route::post('/generateRFQ', [InventoryController::class, 'generateRFQ'])->name('generateRFQ');
                        Route::get('/activeRfq/{inventoryId}', [InventoryController::class, 'getActiveRfqDetails'])->name('activeRfq');
                        Route::get('/orderDetails/{inventoryId}', [InventoryController::class, 'getOrderDetails'])->name('orderDetails');
                    });

                    // Product Routes
                    Route::get('/search-products', [InventoryVendorProductController::class, 'search'])->name('product.search');
                    Route::post('/search-allproduct', [InventoryVendorProductController::class, 'searchAllProduct'])->name('search.allproduct');


                    // Indent Routes & close indent Routes
                    Route::prefix('indent')->name('indent.')->group(function () {
                        Route::get('/', [IndentController::class, 'index'])->name('index');
                        Route::get('/indent-list/data', [IndentController::class, 'getData'])->name('listdata');
                        Route::post('/store', [IndentController::class, 'store'])->name('store');
                        Route::delete('/delete/{id}', [IndentController::class, 'destroy'])->name('delete');
                        Route::post('/fetch-indent-data', [IndentController::class, 'fetchIndentData'])->name('fetchIndentData');
                        Route::post('/getIndentData', [IndentController::class, 'getIndentData'])->name('getIndentData');
                        Route::post('/indentexport', [IndentController::class, 'exportIndentData'])->name('exportData');

                        // Close Indent
                        Route::get('/close-indent', [IndentController::class, 'closeIndent'])->name('closeindent');
                        Route::get('/close-indent-list/data', [IndentController::class, 'getDataCloseIndent'])->name('closeindentlistdata');
                        Route::post('/closeindentexport', [IndentController::class, 'exportCloseIndentData'])->name('exportDataclose');
                    });

                    // Bulk Inventory
                    Route::prefix('bulk-inventory')->name('bulk.inventory.')->group(function () {
                        Route::get('/import', [BulkInventoryController::class, 'importBulkInventory'])->name('import');
                        Route::post('/upload-csv', [BulkInventoryController::class, 'uploadCSV'])->name('uploadCSV');
                        Route::post('/delete-row', [BulkInventoryController::class, 'deleteRow'])->name('deleteRow');
                        Route::post('/update-row', [BulkInventoryController::class, 'updateRowData'])->name('updateRow');
                        Route::post('/check', [BulkInventoryController::class, 'checkBulkInventory'])->name('check');
                        Route::post('/update-products', [BulkInventoryController::class, 'updateBulkProducts'])->name('updateProducts');
                    });

                    // Manual PO
                    Route::prefix('manualPO')->name('manualPO.')->group(function () {
                        Route::post('/fetchInventory', [ManualPOController::class, 'fetchInventoryDetails'])->name('fetchInventory');
                        Route::post('/store', [ManualPOController::class, 'store'])->name('store');
                        Route::get('/searchVendorByVendorname', [ManualPOController::class, 'searchVendorByVendorname'])->name('search.vendors');
                        Route::get('/getVendorDetailsByName', [ManualPOController::class, 'getVendorDetailsByName'])->name('get.vendordetails');
                    });

                    // Issued to
                    Route::prefix('issued_to')->name('issued.')->group(function () {
                        Route::get('/getissuedto', [IssuedController::class, 'getissuedto'])->name('getissuedto');
                        Route::post('/save-issue-to', [IssuedController::class, 'saveIssueTo'])->name('save');
                        Route::post('/delete-issue-to', [IssuedController::class, 'deleteIssueTo'])->name('delete');
                    });

                    //Issue
                    Route::prefix('issue')->name('issue.')->group(function () {
                        Route::post('/fetchInventoryDetails', [IssuedController::class, 'fetchInventoryDetails'])->name('fetchInventoryDetails');
                        Route::post('/store', [IssuedController::class, 'store'])->name('store');
                        Route::post('/consume-store', [IssuedController::class, 'ConsumeStore'])->name('consume.store');
                    });

                    //Issue Return
                    Route::prefix('issue_return')->name('issue_return.')->group(function () {
                        Route::post('/fetchInventoryDetails', [IssueReturnController::class, 'fetchInventoryDetails'])->name('fetchInventoryDetails');
                        Route::post('/store', [IssueReturnController::class, 'store'])->name('store');
                    });

                    //stock return
                    Route::prefix('stock_return')->name('stock_return.')->group(function () {
                        Route::post('/fetchInventoryDetails', [StockReturnController::class, 'fetchInventoryDetails'])->name('fetchInventoryDetails');
                        Route::post('/store', [StockReturnController::class, 'store'])->name('store');
                    });

                    // Reports
                    Route::prefix('reports')->name('report.')->group(function () {


                        Route::get('indent', [ReportsController::class, 'index'])->name('indent');
                        Route::get('closeindent',  [ReportsController::class, 'index'])->name('closeindent');
                        Route::get('issued',  [ReportsController::class, 'index'])->name('issued');
                        Route::get('issuereturn',  [ReportsController::class, 'index'])->name('issuereturn');
                        Route::get('manualpo',  [ReportsController::class, 'index'])->name('manualpo');
                        Route::get('stockLedger',  [ReportsController::class, 'index'])->name('stockLedger');
                        Route::get('grn',  [ReportsController::class, 'index'])->name('grn');
                        Route::get('pendingGrn',  [ReportsController::class, 'index'])->name('pendingGrn');
                        Route::get('currentStock',  [ReportsController::class, 'index'])->name('currentStock');
                        Route::get('stockReturn',  [ReportsController::class, 'index'])->name('stockReturn');
                        Route::get('pendingGrnStockReturn',  [ReportsController::class, 'index'])->name('pendingGrnStockReturn');



                        // Product Wise Stock Ledger Report
                        Route::prefix('product-wise-stock-ledger')->name('productWiseStockLedger.')->group(function () {
                            Route::get('/fetchlistdata',  [ProductWiseStockLedgerController::class, 'fetchData'])->name('listdata');
                            Route::get('/{id}',  [ProductWiseStockLedgerController::class, 'index'])->name('index');
                            Route::post('excel', [ProductWiseStockLedgerController::class, 'export'])->name('export');
                        });


                        // Stock ledger Report
                        Route::prefix('stockLedger')->group(function () {
                            Route::get('listdata', [InventoryController::class, 'getData'])->name('stockLedger.listdata');
                            Route::post('excel', [InventoryController::class, 'exportInventoryData'])->name('stockLedger.export');
                        });
                        // Current Stock Report
                        Route::prefix('currentStock')->group(function () {
                            Route::get('listdata',  [InventoryController::class, 'currentStockGetData'])->name('currentStock.listdata');
                            Route::post('excel',  [InventoryController::class, 'exportCurrentStockData'])->name('currentStock.export');
                        });
                        // Manual PO Report
                        Route::prefix('manualpo')->group(function () {
                            Route::get('listdata',  [ManualPOController::class, 'listdata'])->name('manualPO.listdata');
                            Route::get('orderDetails/{id}', [ManualPOController::class, 'orderDetails'])->name('manualPO.orderDetails');
                            Route::post('excel', [ManualPOController::class, 'export'])->name('manualpoReport.export');
                            Route::post('cancel', [ManualPOController::class, 'cancelManualOrder'])->name('manualpo.cancelOrder');
                            Route::get('/download/{id}', [ManualPOController::class, 'download'])->name('manualpo.download');
                        });

                        // Issued Report
                        Route::prefix('issue')->group(function () {
                            Route::get('listdata', [IssuedController::class, 'getIssuedListData'])->name('issuedlistdata');
                            Route::post('excel', [IssuedController::class, 'export'])->name('issuedExport');
                        });

                        // Issued Return Report
                        Route::prefix('issueReturn')->group(function () {
                            Route::get('listdata', [IssueReturnController::class, 'getIssueReturnListData'])->name('issuedReturnlistdata');
                            Route::post('excel', [IssueReturnController::class, 'export'])->name('issuedReturnExport');
                        });

                        // Indent Reports
                        Route::get('indent/listdata', [IndentController::class, 'getindentreportData'])->name('indentlistdata');
                        Route::post('excel', [IndentController::class, 'exportIndentreportData'])->name('exportIndentReport');

                        Route::get('closeindent/listdata', [IndentController::class, 'getcloseindentreportData'])->name('closeindentlistdata');
                        Route::post('closeindent/export', [IndentController::class, 'exportCloseIndentData'])->name('exportcloseIndentReport');
                        Route::post('closeindent/data', [IndentController::class, 'closeindentdata'])->name('closeindentdata');


                        // grn Reports
                        Route::get('grn/listdata', [GrnController::class, 'grnReportlistdata'])->name('grnReportlistdata');
                        Route::post('grn/export', [GrnController::class, 'exportGrnReport'])->name('exportGrnReport');
                        Route::post('grn/data', [GrnController::class, 'fetchGrnRowdata'])->name('fetchGrnRowdata');
                        Route::post('grn/updatedata', [GrnController::class, 'editGrnRowdata'])->name('editGrnRowdata');
                        Route::get('grn/downloaddata/{id}', [GrnController::class, 'downloadGrnRowdata'])->name('downloadGrnRowdata');

                        //prndinggrn reports
                        Route::get('pendingGrn/listdata', [GrnController::class, 'pendingGrnReportlistdata'])->name('pendingGrnReportlistdata');
                        Route::post('pendingGrn/export', [GrnController::class, 'exportPendingGrnReport'])->name('exportPendingGrnReport');

                        //prndinggrnstockreturn reports
                        Route::get('pendingGrnStockReturn/listdata', [GrnController::class, 'pendingGrnStockReturnReportlistdata'])->name('pendingGrnStockReturnReportlistdata');
                        Route::post('pendingGrnStockReturn/export', [GrnController::class, 'exportPendingGrnStockReturnReport'])->name('exportPendingGrnStockReturnReport');



                        //stock return report
                        Route::get('stockReturn/listdata', [StockReturnController::class, 'stockReturnReportlistdata'])->name('stockReturnReportlistdata');
                        Route::post('stockReturn/export', [StockReturnController::class, 'exportStockReturnReport'])->name('exportStockReturnReport');
                        Route::post('stockReturn/data', [StockReturnController::class, 'fetchStockReturnRowdata'])->name('fetchStockReturnRowdata');
                        Route::post('stockReturn/updatedata', [StockReturnController::class, 'editStockReturnRowdata'])->name('editStockReturnRowdata');
                    });

                    //Add grn
                    Route::prefix('grn')->name('grn.')->group(function () {
                        Route::get('/check-grn-entry/{inventoryId}', [GrnController::class, 'checkGrnEntry'])->name('checkGrnEntry');
                        Route::post('/store', [GrnController::class, 'store'])->name('store');
                    });

                    //store excel file delete
                    Route::post('/delete-export-file', function (Request $request, ExportService $exportService) {
                        return response()->json(
                            $exportService->deleteExportFile($request->input('file_path'))
                        );
                    })->name('delete.export.file');
                });

                /***:- indent api  -:***/
                Route::prefix('api-indent')->group(function () {
                    Route::controller(ApiIndentController::class)->group(function () {
                        Route::get('/', 'apiIndentList')->name('apiIndent.list');
                        Route::post('update-indent-api', 'updateIndentApi')->name('apiIndent.update');
                        Route::post('get-product-list', 'getProductList')->name('apiIndent.getProductList');
                        Route::post('get-rfq-list', 'getRfqList')->name('apiIndent.getRfqList');
                        Route::post('generate-rfq', 'generateRFQ')->name('apiIndent.generateRFQ');
                        Route::post('delete-indent-api', 'deleteIndent')->name('apiIndent.deleteIndent');
                        Route::post('export-indent-data', 'exportIndentData')->name('apiIndent.exportIndentData');
                    });
                });
            });


            /***:- message section  -:***/
            Route::prefix('message')->group(function () {
                Route::controller(MessageController::class)->group(function () {
                    Route::get('/', 'index')->name('message.index');
                });
            });
        });

        Route::get('forward-auction/view/{auction}', [ForwardAuctionController::class, 'view'])->name('forward-auction.show');

        Route::get('buyer/forward-auction/export-cis/{auction_id}', [ForwardAuctionController::class, 'exportCIS'])
            ->name('forward-auction.export-cis');


        Route::get('/auction/live-auction-rfq', [AuctionController::class, 'index'])->name('auction.index');

        Route::get('auction/live-auction-rfq/cis-sheet/{rfq_id}', [AuctionCISController::class, 'index'])->name('auction.cis-sheet');

        Route::post('/auction/create', [AuctionController::class, 'createAuction'])->name('auction.create');
        Route::post('/auction/get', [AuctionController::class, 'getAuction'])->name('auction.get');
        Route::get('/auction/live-rfqs', [AuctionController::class, 'liveAuctionRfq'])->name('auction.live-rfqs');
        Route::post('/auction/close', [AuctionController::class, 'closeAuction'])->name('auction.close');
        Route::get('/auction/cis/{rfq}', [AuctionController::class, 'buyerProductList'])->name('auction.cis');
        Route::get('cis-export/{rfq}', [AuctionController::class, 'exportBuyerCisSheetNew'])->name('buyer.auction.cis-export');
        Route::post('/auction/force-stop', [AuctionController::class, 'forceStop'])->name('auction.force-stop');
        Route::post('/auction/booked-times', [AuctionController::class, 'getBookedTimes'])->name('auction.booked-times');
    });

    // Route::post('api-indent/add-product/{key}', [ApiIndentController::class, 'postmanStore'])->name('apiIndent.postmanStore');
});
