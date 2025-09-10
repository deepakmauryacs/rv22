<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\Category;
use Carbon\Carbon;
use DB;

class AuctionCISController extends Controller
{
    public function index($rfq_id)
    {
        // 1) Ensure RFQ is in live auctions
        $auctionExists = DB::table('rfq_auctions')->where('rfq_no', $rfq_id)->exists();
        if (!$auctionExists) {
            return response("<script>
                alert('THIS RFQ NOT BELONG TO LIVE AUCTIONS');
                window.location.href='" . route('buyer.dashboard') . "';
            </script>");
        }

        // 2) Buyer + RFQ ownership checks
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $parent_user_id)
            ->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }
        if ((int)$rfq_data->buyer_rfq_status === 1) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' CIS did not received any vendor quote to open.');
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        if (!empty($user_branch_id_only) && !in_array($rfq_data->buyer_branch, $user_branch_id_only)) {
            return back()->with('error', 'No RFQ found');
        }

        // 3) Static lookups
        $uom = getUOMList();
        $nature_of_business = DB::table('nature_of_business')
            ->select('id', 'business_name')
            ->orderByDesc('id')
            ->pluck('business_name', 'id')
            ->toArray();

        // 4) Current auction row (latest by id) — used for filtering vendors + prefill
        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();
        $editId = $auction->id ?? null;

        // 5) CIS payload (inline query previously in rfqAuctionDetails)
        $cis_filter_vendors = $this->extractCISFilterVendor($rfq_id);

        $cisData = Rfq::where('rfq_id', $rfq_id)
            ->select(
                'id',
                'rfq_id',
                'buyer_id',
                'buyer_user_id',
                'prn_no',
                'buyer_branch',
                'last_response_date',
                'buyer_price_basis',
                'buyer_pay_term',
                'buyer_delivery_period',
                'edit_by',
                'scheduled_date',
                'buyer_rfq_status',
                'created_at',
                'updated_at'
            )
            ->with([
                'buyer_branchs' => function ($q) {
                    $q->select('branch_id', 'name');
                },
                // ONLY load auction prices; we won't load/use rfqVendorQuotations here
                'rfqVendorAuctionPrices' => function ($q) {
                    $q->select([
                        'id',
                        'rfq_no',
                        DB::raw('NULL as rfq_id'),
                        'vendor_id',
                        DB::raw('rfq_product_veriant_id as rfq_product_variant_id'),
                        DB::raw('vend_price as price'),
                        DB::raw('NULL as mrp'),
                        DB::raw('NULL as discount'),
                        DB::raw('0 as buyer_price'),
                        DB::raw('NULL as vendor_brand'),
                        DB::raw('vend_specs as vendor_remarks'),
                        DB::raw('NULL as vendor_additional_remarks'),
                        DB::raw('vend_price_basis as vendor_price_basis'),
                        DB::raw('vend_payment_terms as vendor_payment_terms'),
                        DB::raw('vend_delivery_period as vendor_delivery_period'),
                        DB::raw('vend_currency as vendor_currency'),
                        'created_at',
                        'updated_at',
                    ])->orderBy('id', 'desc');
                },
                'rfqVendors'=> function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_user_id', 'product_id', 'vendor_status');
                },
                'rfqVendors.rfqVendorProfile'=> function ($q) {
                    $q->select('id', 'user_id', 'legal_name', 'date_of_incorporation', 'nature_of_business', 'company_name1', 'company_name2', 'msme_certificate', 'iso_registration');
                },
                'rfqVendors.rfqVendorDetails'=> function ($q) {
                    $q->select('id', 'name', 'country_code', 'mobile');
                },
                'rfqVendors.vendorMainProduct'=> function ($q) {
                    $q->select('id', 'vendor_id', 'product_id');
                },
                'rfqVendors.vendorMainProduct.product'=> function ($q) {
                    $q->select('id', 'product_name');
                },
                'rfqProducts'=> function ($q) {
                    $q->orderBy('product_order', 'asc');
                },
                'rfqProducts.productVariants'=> function ($q) use ($rfq_id) {
                    $q->where('rfq_id', $rfq_id)->orderBy('variant_order', 'asc');
                },
                'rfqProducts.masterProduct'=> function ($q) {
                    $q->select('id', 'product_name', 'division_id', 'category_id');
                },
                'rfq_auction'=> function ($q) {
                    $q->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time', 'is_rfq_price_map');
                },
                'rfqOrders'=> function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_id', 'po_number')->where('order_status', 1);
                },
                'rfqOrders.order_variants'=> function ($q) {
                    $q->select('id', 'po_number', 'rfq_product_variant_id', 'order_quantity');
                },
                'rfqTechnicalApproval'=> function ($q) {
                    $q->select('rfq_no', 'vendor_id', 'description', 'technical_approval');
                }
            ])
            ->first();

        if ($cisData) {
            $cisData->setRelation('rfqVendorQuotations', $cisData->rfqVendorAuctionPrices ?? collect());
        }

        $cis_filter = $this->cisFilter($rfq_id);

        $cis_array = $this->analyzeRFQDetails($cisData, $cis_filter_vendors);
        $cis_array = $this->sortRFQDetails($cis_array);
        $cis = array_merge($cis_filter, $cis_array);
        $rfq = $cis['rfq'] ?? null;

        // 6) STRICT: Vendors must be only those present in rfq_vendor_auctions for this auction
        $allowedVendorIds = [];
        if ($editId) {
            $allowedVendorIds = DB::table('rfq_vendor_auctions')
                ->where('auction_id', $editId)
                ->pluck('vendor_id')
                ->map(fn ($v) => (int)$v)
                ->toArray();
        }
        // Filter cis['vendors'] by allowed list (handles array-key or vendor_user_id inside row)
        $cis['vendors'] = collect($cis['vendors'] ?? [])
            ->filter(function ($row, $key) use ($allowedVendorIds) {
                $id = $key;
                if (is_array($row)) {
                    $id = $row['vendor_user_id'] ?? $key;
                } elseif (is_object($row)) {
                    $id = $row->vendor_user_id ?? $key;
                }
                return in_array((int)$id, $allowedVendorIds, true);
            })
            ->toArray();

        // 7) Request filters
        $filter = [
            'sort_price'       => request('sort_price'),
            'location'         => request('location'),
            'state_location'   => request('state_location'),
            'country_location' => request('country_location'),
            'last_vendor'      => request('last_vendor'),
            'favourite_vendor' => request('favourite_vendor'),
            'from_date'        => request('from_date'),
            'to_date'          => request('to_date'),
        ];
        $is_date_filter = !empty($filter['from_date']) || !empty($filter['to_date']);
        $currencies = DB::table('currencies')->where('status', '1')->get();

        // 8) Selected vendor ids (STRICT: only from rfq_vendor_auctions)
        $selectedVendorIds = [];
        if ($editId) {
            $selectedVendorIds = DB::table('rfq_vendor_auctions')
                ->where('auction_id', $editId)
                ->pluck('vendor_id')
                ->map(fn ($v) => (int)$v)
                ->toArray();
        }

        // 9) Variant start prices keyed by rfq_variant_id (for header/table prefill)
        $prefillVariantPrices = [];
        if ($editId) {
            $prefillVariantPrices = DB::table('rfq_auction_variants')
                ->where('auction_id', $editId)
                ->pluck('start_price', 'rfq_variant_id')
                ->toArray();
        }

        // 10) Header prefill (date/time/currency/decrement/type)
        $prefill = [];
        if ($auction) {
            try {
                // auction_date might be 'Y-m-d' or 'd/m/Y' etc.; display as d/m/Y
                $prefill['auction_date'] = Carbon::parse($auction->auction_date, 'Asia/Kolkata')->format('d/m/Y');
            } catch (\Throwable $e) {
                $prefill['auction_date'] = $auction->auction_date; // fallback to raw
            }
            $prefill['auction_time']      = $auction->auction_start_time;
            $prefill['min_bid_currency']  = $auction->currency ?? 'INR';
            $prefill['min_bid_decrement'] = (float)$auction->min_bid_decrement;
            $prefill['auction_type']      = $auction->auction_type;
        }

        // 11) LIVE AUCTION STATUS
        $current_status = null;
        if ($auction) {
            $current_status = $this->getAuctionStatus(
                $auction->auction_date,
                $auction->auction_start_time,
                $auction->auction_end_time
            );

            // If auction has ended and prices aren't mapped yet, persist them
            if ($auction->is_rfq_price_map !== '1' && $current_status === 3) {
                $this->mapAuctionPricesToQuotation($auction);
                $auction->is_rfq_price_map = '1';
            }
        }

        // 12) Render
        return view('buyer.auction.cis.rfq-cis', compact(
            'uom',
            'cis',
            'rfq',
            'nature_of_business',
            'filter',
            'is_date_filter',
            'currencies',
            'auction',
            'editId',
            'selectedVendorIds',
            'prefill',
            'prefillVariantPrices',
            'current_status',
        ));
    }

    /**
     * Map final auction prices into rfq_vendor_quotations and
     * flag the auction so that it isn't processed again.
     */
    private function mapAuctionPricesToQuotation($auction): void
    {
        DB::transaction(function () use ($auction) {
            $latest = DB::table('rfq_vendor_auction_price')
                ->selectRaw('MAX(id) as id')
                ->where('rfq_auction_id', $auction->id)
                ->groupBy('vendor_id', 'rfq_product_veriant_id');

            $rows = DB::table('rfq_vendor_auction_price as ap')
                ->joinSub($latest, 't', 't.id', '=', 'ap.id')
                ->select(
                    'ap.rfq_no',
                    'ap.vendor_id',
                    'ap.rfq_product_veriant_id',
                    'ap.vend_price',
                    'ap.vend_specs',
                    'ap.vend_price_basis',
                    'ap.vend_payment_terms',
                    'ap.vend_delivery_period',
                    'ap.vend_price_validity',
                    'ap.vend_dispatch_branch',
                    'ap.vend_currency',
                    'ap.vendor_user_id'
                )
                ->get();

            foreach ($rows as $row) {
                DB::table('rfq_vendor_quotations')->insert([
                    'rfq_id' => $row->rfq_no,
                    'vendor_id' => $row->vendor_id,
                    'rfq_product_variant_id' => $row->rfq_product_veriant_id,
                    'price' => $row->vend_price,
                    'mrp' => 0,
                    'discount' => 0,
                    'buyer_price' => 0,
                    'specification' => $row->vend_specs,
                    'vendor_remarks' => $row->vend_specs,
                    'vendor_price_basis' => $row->vend_price_basis,
                    'vendor_payment_terms' => $row->vend_payment_terms,
                    'vendor_delivery_period' => $row->vend_delivery_period,
                    'vendor_price_validity' => $row->vend_price_validity,
                    'vendor_dispatch_branch' => $row->vend_dispatch_branch,
                    'vendor_currency' => $row->vend_currency,
                    'buyer_user_id' => $auction->buyer_user_id,
                    'vendor_user_id' => $row->vendor_user_id,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::table('rfq_auctions')
                ->where('id', $auction->id)
                ->update([
                    'is_rfq_price_map' => '1',
                    'price_map_time' => Carbon::now(),
                ]);
        });
    }

    /**
     * Determine auction status for given date and time range.
     *
     * @return int 1=>Live, 2=>Scheduled, 3=>Completed
     */
    private function getAuctionStatus($date, $startTime, $endTime): int
    {
        $start = Carbon::parse($date . ' ' . $startTime, 'Asia/Kolkata');
        $end = Carbon::parse($date . ' ' . $endTime, 'Asia/Kolkata');
        $now = Carbon::now('Asia/Kolkata');

        if ($now->gt($end)) {
            return 3; // Completed
        }

        if ($now->between($start, $end)) {
            return 1; // Live
        }

        return 2; // Scheduled
    }

    private function cisFilter($rfq_id)
    {
        $cis_filter = Rfq::where('rfq_id', $rfq_id)
            ->select('id', 'rfq_id', 'buyer_id', 'buyer_user_id')
            ->with([
                'rfqVendors' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_user_id');
                },
                'rfqVendors.rfqVendorProfile' => function ($q) {
                    $q->select('id', 'user_id', 'legal_name', 'country', 'state');
                },
                'rfqVendors.rfqVendorProfile.vendor_country' => function ($q) {
                    $q->select('id', 'name');
                },
                'rfqVendors.rfqVendorProfile.vendor_state' => function ($q) {
                    $q->select('id', 'name');
                },
                'rfqVendors.vendorOrders' => function ($q) {
                    $q->select('id', 'vendor_id')->where('order_status', 1);
                },
                'rfqVendors.vendorFavorites' => function ($q) {
                    $q->select('vend_user_id')->where('fav_or_black', 1)->where('buyer_user_id', getParentUserId());
                }
            ])
            ->first();

        $last_vendor = [];
        $fav_vendor = [];
        $countryIds = [];
        $stateIds = [];

        if ($cis_filter) {
            foreach ($cis_filter->rfqVendors as $vendor) {
                $profile = $vendor->rfqVendorProfile;
                foreach ($vendor->vendorOrders as $order) {
                    if ($profile) {
                        $last_vendor[$order->vendor_id] = $profile->legal_name;
                    }
                }
                foreach ($vendor->vendorFavorites as $fav) {
                    if ($profile) {
                        $fav_vendor[$fav['vend_user_id']] = $profile->legal_name;
                    }
                }

                if ($profile) {
                    if ($profile->country == 101 && $profile->vendor_state) {
                        $stateIds[$profile->state] = $profile->vendor_state->name;
                    } elseif ($profile->vendor_country) {
                        $countryIds[$profile->country] = $profile->vendor_country->name;
                    }
                }
            }

            asort($last_vendor);
            asort($stateIds);
            asort($countryIds);
        }

        return [
            'fav_vendor' => $fav_vendor,
            'last_vendor' => $last_vendor,
            'filter_country' => $countryIds,
            'filter_state' => $stateIds,
        ];
    }

    private function extractCISFilterVendor($rfq_id)
    {
        $location = request('location');
        $state_location = request('state_location');
        $country_location = request('country_location');
        $last_vendor = request('last_vendor');
        $favourite_vendor = request('favourite_vendor');
        $from_date = request('from_date');
        $to_date = request('to_date');
        if (empty($from_date) && empty($to_date) && empty($location) && empty($last_vendor) && empty($favourite_vendor)) {
            return [];
        }

        $matchingVendorIds = [];

        if (!empty($from_date) || !empty($to_date)) {
            $vendorQuoteQuery = \App\Models\RfqVendorQuotation::where('rfq_id', $rfq_id)->where('status', 1);

            if (!empty($from_date) && !empty($to_date)) {
                $from = \Carbon\Carbon::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
                $to = \Carbon\Carbon::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
                $vendorQuoteQuery->whereBetween(\DB::raw('DATE(created_at)'), [$from, $to]);
            } elseif (!empty($from_date)) {
                $from = \Carbon\Carbon::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
                $vendorQuoteQuery->whereDate('created_at', '>=', $from);
            } elseif (!empty($to_date)) {
                $to = \Carbon\Carbon::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
                $vendorQuoteQuery->whereDate('created_at', '<=', $to);
            }

            $matchingVendorIds = $vendorQuoteQuery->pluck('vendor_id')->unique()->toArray();
            if (empty($matchingVendorIds)) {
                return [];
            }
        }

        if (!empty($last_vendor)) {
            if (!empty($matchingVendorIds)) {
                $matchingVendorIds = array_intersect($matchingVendorIds, $last_vendor);
            } else {
                $matchingVendorIds = $last_vendor;
            }
        }

        if (!empty($favourite_vendor)) {
            if (!empty($matchingVendorIds)) {
                $matchingVendorIds = array_intersect($matchingVendorIds, $favourite_vendor);
            } else {
                $matchingVendorIds = $favourite_vendor;
            }
        }

        $cis_filter = Rfq::where('rfq_id', $rfq_id)
            ->select('id', 'rfq_id', 'buyer_id', 'buyer_user_id')
            ->with([
                'rfqVendors' => function ($q) use ($state_location, $country_location, $matchingVendorIds) {
                    $q->select('id', 'rfq_id', 'vendor_user_id');
                    if (!empty($matchingVendorIds)) {
                        $q->whereIn('vendor_user_id', $matchingVendorIds);
                    }
                    $q->whereHas('rfqVendorProfile', function ($q2) use ($state_location, $country_location) {
                        if (!empty($state_location)) {
                            $q2->whereIn('state', explode(',', $state_location));
                        }
                        if (!empty($country_location)) {
                            $q2->whereIn('country', explode(',', $country_location));
                        }
                    });
                }
            ])
            ->first();

        $filterVendorUserIds = [];
        if ($cis_filter && $cis_filter->rfqVendors) {
            $filterVendorUserIds = $cis_filter->rfqVendors->pluck('vendor_user_id')->unique()->values()->toArray();
        }

        return $filterVendorUserIds;
    }

    private function analyzeRFQDetails($cis, $filter_vendors)
    {
        $orders = [];
        $variant_order_qty = [];
        foreach ($cis->rfqOrders as $order) {
            foreach ($order->order_variants as $variant) {
                $orders[$variant->rfq_product_variant_id][$order->vendor_id][] = $variant->order_quantity;
                $variant_order_qty[$variant->rfq_product_variant_id] = ($variant_order_qty[$variant->rfq_product_variant_id] ?? 0) + $variant->order_quantity;
            }
        }
        $vendor_technical_approval = [];
        foreach ($cis->rfqTechnicalApproval as $technical_approval) {
            $vendor_technical_approval[$technical_approval->vendor_id] = ['description' => $technical_approval->description, 'technical_approval' => $technical_approval->technical_approval];
        }

        $variants = [];
        $product_variant_count = [];
        $rfq_division = 0;
        $rfq_category = 0;
        foreach ($cis->rfqProducts as $product) {
            foreach ($product->productVariants as $variant) {
                $variants[$variant->id] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->masterProduct->product_name,
                    'brand' => $product->brand,
                    'remarks' => $product->remarks,
                    'product_order' => $product->product_order,
                    'specification' => $variant->specification,
                    'size' => $variant->size,
                    'quantity' => $variant->quantity,
                    'uom' => $variant->uom,
                    'attachment' => $variant->attachment,
                    'variant_order' => $variant->variant_order,
                    'variant_grp_id' => $variant->variant_grp_id,
                    'lowest_price' => null,
                    'orders' => $orders[$variant->id] ?? []
                ];
                $product_variant_count[$product->product_id][$variant->id] = true;
                if ($rfq_division == 0) {
                    $rfq_division = $product->masterProduct->division_id;
                    $rfq_category = $product->masterProduct->category_id;
                }
            }
        }
        unset($orders);

        $vendor_quotes = [];
        $buyer_quotes = [];
        $vendor_variant_map = [];
        foreach ($cis->rfqVendorQuotations as $quote) {
            $variant_id = $quote->rfq_product_variant_id;
            $left_qty = $variants[$variant_id]['quantity'] - ($variant_order_qty[$variant_id] ?? 0);

            $vendor_variant_map[$quote->vendor_id][$quote->rfq_product_variant_id] = true;

            $quote_data = [
                'id' => $quote->id,
                'rfq_id' => $quote->rfq_id,
                'vendor_id' => $quote->vendor_id,
                'rfq_product_variant_id' => $variant_id,
                'price' => $quote->price,
                'mrp' => $quote->mrp,
                'discount' => $quote->discount,
                'buyer_price' => $quote->buyer_price,
                'vendor_brand' => $quote->vendor_brand,
                'vendor_remarks' => $quote->vendor_remarks,
                'vendor_additional_remarks' => $quote->vendor_additional_remarks,
                'vendor_price_basis' => $quote->vendor_price_basis,
                'vendor_payment_terms' => $quote->vendor_payment_terms,
                'vendor_delivery_period' => $quote->vendor_delivery_period,
                'vendor_currency' => $quote->vendor_currency,
                'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $quote->updated_at->format('Y-m-d H:i:s'),
                'variant_quantity' => $variants[$variant_id]['quantity'],
                'left_qty' => $left_qty,
            ];

            $vendor_quotes[$quote->vendor_id][$variant_id][] = $quote_data;

            if ($quote->buyer_price > 0) {
                $buyer_quotes[$variant_id][] = [
                    'id' => $quote->id,
                    'buyer_price' => $quote->buyer_price,
                    'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $quote->updated_at->format('Y-m-d H:i:s'),
                ];
            }
        }
        unset($variant_order_qty);
        foreach ($buyer_quotes as $key => $value) {
            usort($buyer_quotes[$key], function ($a, $b) {
                return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
            });
        }

        $vendor_variant_quoted_count = [];
        foreach ($vendor_variant_map as $vendor_id => $variant_set) {
            $vendor_variant_quoted_count[$vendor_id] = count($variant_set);
        }
        unset($vendor_variant_map);

        $vendors = [];
        $vendor_total_amount = [];
        $vendor_delivery_period = [];
        $is_vendor_product = [];
        foreach ($cis->rfqVendors as $vendor) {
            $vendor_id = $vendor->vendor_user_id;
            if (isset($is_vendor_product[$vendor_id])) {
                $is_vendor_product[$vendor_id][$vendor->product_id] = true;
                continue;
            } else {
                $is_vendor_product[$vendor_id] = [$vendor->product_id => true];
            }

            $quotes = $vendor_quotes[$vendor_id] ?? [];
            $vendor_brand = [];
            $last_quote = [];
            $total_amount = 0;
            $delivery_period = 0;
            foreach ($quotes as $variantId => &$variantGroup) {
                usort($variantGroup, function ($a, $b) {
                    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
                });

                if (!empty($variantGroup)) {
                    $first = $variantGroup[0];
                    $first['total_amount'] = (float) $first['price'] * (float) $first['variant_quantity'];
                    $total_amount += (float) $first['price'] * (float) $first['variant_quantity'];
                    $last_quote[$variantId] = $first;
                    $delivery_period = $first['vendor_delivery_period'];

                    if (!empty($first['vendor_brand'])) {
                        $vendor_brand[] = $first['vendor_brand'];
                    }
                } else {
                    $last_quote[$variantId] = [];
                }
            }

            $latest_quote = null;
            foreach ($last_quote as $item) {
                if ($latest_quote === null || strtotime($item['created_at']) > strtotime($latest_quote['created_at'])) {
                    $latest_quote = $item;
                }

                $is_valid_filter_vendor = true;
                if (!empty($filter_vendors) && !in_array($item['vendor_id'], $filter_vendors)) {
                    $is_valid_filter_vendor = false;
                }
                if ($is_valid_filter_vendor) {
                    $lowest_price = $variants[$item['rfq_product_variant_id']]['lowest_price'];
                    if ($lowest_price === null || $item['price'] < $lowest_price) {
                        $variants[$item['rfq_product_variant_id']]['lowest_price'] = $item['price'];
                    }
                }
            }

            $productNames = collect($vendor->vendorMainProduct)
                ->pluck('product.product_name')
                ->filter()
                ->take(3)
                ->implode(', ');

            $vendors[$vendor_id] = [
                'vendor_user_id' => $vendor_id,
                'legal_name' => $vendor->rfqVendorProfile->legal_name,
                'vendor_rfq_status' => $vendor->vendor_status,
                'vintage' => (int) Carbon::parse($vendor->rfqVendorProfile->date_of_incorporation)->diffInYears(Carbon::now()),
                'nature_of_business' => $vendor->rfqVendorProfile->nature_of_business,
                'client' => $vendor->rfqVendorProfile->company_name1 . (!empty($vendor->rfqVendorProfile->company_name1) && !empty($vendor->rfqVendorProfile->company_name2) ? ', ' : '') . $vendor->rfqVendorProfile->company_name2,
                'certifications' => !empty($vendor->rfqVendorProfile->msme_certificate) ? $vendor->rfqVendorProfile->msme_certificate : $vendor->rfqVendorProfile->iso_registration,
                'name' => $vendor->rfqVendorDetails->name,
                'country_code' => $vendor->rfqVendorDetails->country_code,
                'mobile' => $vendor->rfqVendorDetails->mobile,
                'vendor_product' => $productNames,
                'vendor_brand' => !empty($vendor_brand) ? implode(', ', $vendor_brand) : '',
                'latest_quote' => $latest_quote,
                'last_quote' => $last_quote,
                'vendorQuotes' => $quotes,
                'technical_approval' => (!empty($vendor_technical_approval[$vendor_id]) && isset($vendor_technical_approval[$vendor_id])) ? $vendor_technical_approval[$vendor_id] : [],
            ];
            $vendor_total_amount[$vendor_id] = $total_amount;
            $vendor_delivery_period[$vendor_id] = $delivery_period;
        }
        unset($vendor_quotes);
        unset($vendor_technical_approval);

        $vendor_variant_count = [];
        foreach ($is_vendor_product as $vendor_id => $product_ids) {
            $vendor_variant_count[$vendor_id] = 0;

            foreach ($product_ids as $product_id => $value) {
                if (isset($product_variant_count[$product_id]) && is_array($product_variant_count[$product_id])) {
                    $vendor_variant_count[$vendor_id] += count($product_variant_count[$product_id]);
                }
            }
        }
        unset($product_variant_count);

        $common_vendors = array_intersect_key($vendor_variant_count, $vendor_variant_quoted_count);
        $max_quoted_vendor = [];
        if (!empty($common_vendors)) {
            $max_quoted = max($common_vendors);
            $max_quoted_vendor = array_keys(array_filter($common_vendors, fn($val) => $val == $max_quoted));
        }

        foreach ($vendor_variant_count as $vendor_id => $total_variant_count) {
            $vendor_quoted_variant_count = isset($vendor_variant_quoted_count[$vendor_id]) ? $vendor_variant_quoted_count[$vendor_id] : 0;
            $quoted_variant_percent = $vendor_quoted_variant_count > 0 ? number_format(($vendor_quoted_variant_count / $total_variant_count) * 100, 2) : 0;

            $vendors[$vendor_id]['vendor_quoted_product'] = $quoted_variant_percent . '% (' . $vendor_quoted_variant_count . '/' . $total_variant_count . ')';
        }

        $lowest_price_total = null;
        foreach ($vendor_total_amount as $vendor_id => $total_price) {
            $is_valid_filter_vendor = true;
            if (!empty($filter_vendors) && !in_array($vendor_id, $filter_vendors)) {
                $is_valid_filter_vendor = false;
            }
            if ($is_valid_filter_vendor) {
                if (($lowest_price_total === null || $total_price < $lowest_price_total) && $total_price > 0 && in_array($vendor_id, $max_quoted_vendor)) {
                    $lowest_price_total = $total_price;
                }
            }
        }
        unset($max_quoted_vendor);

        $category = Category::with(['division'])->where('id', $rfq_category)->first();

        $rfq = [
            'rfq_id' => $cis->rfq_id,
            'prn_no' => $cis->prn_no,
            'buyer_branch' => $cis->buyer_branch,
            'buyer_branch_name' => $cis->buyer_branchs->name,
            'buyer_price_basis' => $cis->buyer_price_basis,
            'buyer_pay_term' => $cis->buyer_pay_term,
            'buyer_delivery_period' => $cis->buyer_delivery_period,
            'edit_by' => $cis->edit_by,
            'buyer_rfq_status' => $cis->buyer_rfq_status,
            'rfq_division' => $category->division->division_name,
            'rfq_category' => $category->category_name,
            'scheduled_date' => optional($cis->scheduled_date)->format('Y-m-d'),
            'created_at' => $cis->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $cis->updated_at->format('Y-m-d H:i:s'),
            'last_response_date' => optional($cis->last_response_date)->format('Y-m-d'),
            'is_auction' => $cis->rfq_auction ? 1 : 2,
            'lowest_price_total' => $lowest_price_total,
        ];
        if (!empty($cis->rfq_auction)) {
            $rfq['auction_date'] = $cis->rfq_auction->auction_date ? Carbon::parse($cis->rfq_auction->auction_date)->format('Y-m-d') : '';
            $rfq['auction_start_time'] = $cis->rfq_auction->auction_start_time ? Carbon::parse($cis->rfq_auction->auction_start_time)->format('H:i:s') : '';
            $rfq['auction_end_time'] = $cis->rfq_auction->auction_end_time ? Carbon::parse($cis->rfq_auction->auction_end_time)->format('H:i:s') : '';
            $rfq['is_rfq_price_map'] = $cis->rfq_auction->is_rfq_price_map;

            $rfq['auction_status'] = $this->getAuctionStatus(
                $rfq['auction_date'],
                $rfq['auction_start_time'],
                $rfq['auction_end_time']
            );
        }

        $data = [
            'rfq' => $rfq,
            'vendor_total_amount' => $vendor_total_amount,
            'vendor_delivery_period' => $vendor_delivery_period,
            'vendor_variant_quoted_count' => $vendor_variant_quoted_count,
            'variants' => $variants,
            'vendors' => $vendors,
            'buyer_quotes' => $buyer_quotes,
            'is_vendor_product' => $is_vendor_product,
            'filter_vendors' => $filter_vendors,
        ];

        return $data;
    }

    private function sortRFQDetails($cis)
    {
        $sort_key = request('sort_price');

        $vendors = collect($cis['vendors']);
        $vendor_total_amount = collect($cis['vendor_total_amount']);
        $vendor_delivery_period = collect($cis['vendor_delivery_period']);
        $vendor_variant_quoted_count = collect($cis['vendor_variant_quoted_count']);

        $sortedVendorIDs = [];

        $quotedVendorIDs = $vendor_total_amount
            ->filter(fn($amount) => $amount > 0)
            ->keys()
            ->toArray();

        $nonQuotedVendorIDs = $vendors
            ->keys()
            ->diff($quotedVendorIDs)
            ->toArray();

        if (empty($sort_key)) {
            $combined = collect($quotedVendorIDs)->map(function ($vendor_id) use ($vendor_variant_quoted_count, $vendor_total_amount) {
                return [
                    'vendor_id' => $vendor_id,
                    'quote_count' => $vendor_variant_quoted_count[$vendor_id] ?? 0,
                    'total_amount' => $vendor_total_amount[$vendor_id] ?? 0,
                ];
            });

            $sortedVendorIDs = $combined
                ->sortBy([
                    ['quote_count', 'desc'],
                    ['total_amount', 'asc'],
                ])
                ->pluck('vendor_id')
                ->toArray();
        } else {
            switch ($sort_key) {
                case '1':
                    $sortedVendorIDs = $vendor_total_amount
                        ->only($quotedVendorIDs)
                        ->sort()
                        ->keys()
                        ->toArray();
                    break;

                case '2':
                    $sortedVendorIDs = $vendor_total_amount
                        ->only($quotedVendorIDs)
                        ->sortDesc()
                        ->keys()
                        ->toArray();
                    break;

                case '3':
                    $sortedVendorIDs = $vendor_delivery_period
                        ->only($quotedVendorIDs)
                        ->filter()
                        ->sort()
                        ->keys()
                        ->toArray();

                    $emptyDelivery = $vendor_delivery_period
                        ->only($quotedVendorIDs)
                        ->filter(fn($val) => empty($val))
                        ->keys()
                        ->toArray();

                    $sortedVendorIDs = array_merge($sortedVendorIDs, $emptyDelivery);
                    break;

                default:
                    $sortedVendorIDs = $vendor_total_amount
                        ->only($quotedVendorIDs)
                        ->sort()
                        ->keys()
                        ->toArray();
                    break;
            }
        }

        $finalVendorOrder = array_merge($sortedVendorIDs, $nonQuotedVendorIDs);

        $sortedVendors = collect($finalVendorOrder)
            ->mapWithKeys(fn($id) => [$id => $vendors[$id]])
            ->toArray();

        $cis['vendors'] = $sortedVendors;

        return $cis;
    }
}
