<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Models\BranchDetail;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\InventoryController;
use App\Models\Issueto;
use App\Models\Issued;
use App\Models\IssuedReturn;
use App\Models\IssuedType;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function index()
    {
        $branches       =   BranchDetail::getDistinctActiveBranchesByUser(Auth::user()->id);
        $categories     =   InventoryController::getSortedUniqueCategoryNames();
        $routeName = Route::currentRouteName(); // Get the route name
        ManualPOController::userCurrency();
        switch ($routeName) {
            case 'buyer.report.manualpo':
                session(['page_title' => 'Manual PO Report Management - Raprocure']);
                return view('buyer.report.manualpo', compact('branches', 'categories'));

            case 'buyer.report.currentStock':
                session(['page_title' => 'Current Stock Report Management - Raprocure']);
                return view('buyer.report.currentStock', compact('branches', 'categories'));

            case 'buyer.report.indent':
                session(['page_title' => 'Indent Report Management - Raprocure']);
                return view('buyer.report.indent', compact('branches', 'categories'));

            case 'buyer.report.closeindent':
                session(['page_title' => 'Close Indent ReportManagement - Raprocure']);
                return view('buyer.report.closeindent', compact('branches', 'categories'));

            case 'buyer.report.grn':
                session(['page_title' => 'GRN Report Management - Raprocure']);
                return view('buyer.report.grn', compact('branches', 'categories'));

            case 'buyer.report.pendingGrn':
                session(['page_title' => 'Pending GRN Report Management - Raprocure']);
                return view('buyer.report.pendingGrn', compact('branches', 'categories'));

            // start pingki
            case 'buyer.report.pendingGrnStockReturn':
                session(['page_title' => 'Pending GRN Of Stock Return Report Management - Raprocure']);
                return view('buyer.report.pendingGrnStockReturn', compact('branches', 'categories'));
            // end pingki

            case 'buyer.report.issued':
                session(['page_title' => 'Issued Report Management - Raprocure']);
                $IssueTo     =   Issueto::orderBy('name', 'asc')->get();
                $addUsers = Issued::whereNotNull('buyer_id')->with('buyer:id,name')->get()->pluck('buyer')->filter()->unique('id')->sortBy('name')->values();
                return view('buyer.report.issued', compact('branches', 'categories','IssueTo','addUsers'));

            case 'buyer.report.issuereturn':
                session(['page_title' => 'Issued Return Report Management - Raprocure']);
                $addUsers = IssuedReturn::whereNotNull('buyer_id')->with('buyer:id,name')->get()->pluck('buyer')->filter()->unique('id')->sortBy('name')->values();
                return view('buyer.report.issuereturn', compact('branches', 'categories','addUsers'));
            case 'buyer.report.stockReturn':
                session(['page_title' => 'Stock Return Report Management - Raprocure']);
                $addUsers = IssuedReturn::whereNotNull('buyer_id')->with('buyer:id,name')->get()->pluck('buyer')->filter()->unique('id')->sortBy('name')->values();
                $ReturnTypes     =   IssuedType::orderBy('name', 'asc')->get();
                return view('buyer.report.stockreturn', compact('branches','ReturnTypes', 'categories','addUsers'));

            case 'buyer.report.stockLedger':
                session(['page_title' => 'Stock Ledger Report Management - Raprocure']);
                return view('buyer.report.stockLedger', compact('branches', 'categories'));

            case 'buyer.report.productWiseStockLedger':
                session(['page_title' => 'Product Wise Stock Ledger Report Management - Raprocure']);
                return view('buyer.report.productWiseStockLedger', compact('branches', 'categories'));



            default:
                abort(404);
        }
    }
}
