<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rfq extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_response_date' => 'date',
    ];

    public function rfqBuyer()
    {
        return $this->belongsTo(User::class, 'id', 'buyer_id');
    }

    public function rfqBuyerProfile()
    {
        return $this->hasOne(Buyer::class, 'user_id', 'buyer_id');
    }

    public function rfqProducts()
    {
        return $this->hasMany(RfqProduct::class, 'rfq_id', 'rfq_id');
    }

    public function rfqVendors()
    {
        return $this->hasMany(RfqVendor::class, 'rfq_id', 'rfq_id');
    }

    // (Legacy name kept) – corrected FK to rfq_no
    public function RfqVendorAuctionPrice()
    {
        return $this->hasMany(RfqVendorAuctionPrice::class, 'rfq_no', 'rfq_id');
    }

    // Preferred relation name (same mapping)
    public function rfqVendorAuctionPrices()
    {
        return $this->hasMany(RfqVendorAuctionPrice::class, 'rfq_no', 'rfq_id');
    }

    public function rfqVendorQuotations()
    {
        return $this->hasMany(RfqVendorQuotation::class, 'rfq_id', 'rfq_id');
    }
    // public function latestVendorQuotation()
    // {
    //     return $this->hasMany(RfqVendorQuotation::class, 'rfq_id', 'rfq_id')
    //                 ->where('status', 1)
    //                 ->orderByDesc('created_at');
    // }

    public function getLastRfqVendorQuotation()
    {
        return $this->hasOne(RfqVendorQuotation::class, 'rfq_id', 'rfq_id')->latest();
    }

    public function buyerUser()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function buyerBranch()
    {
        return $this->belongsTo(BranchDetail::class, 'buyer_branch', 'branch_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'user_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vend_id');
    }

    public function products()
    {
        return $this->hasMany(RfqProduct::class, 'rfq_id', 'rfq_id');
    }

    public function buyer_branchs()
    {
        return $this->belongsTo(BranchDetail::class, 'buyer_branch', 'branch_id');
    }

    public function rfq_generated_by()
    {
        return $this->belongsTo(User::class, 'buyer_user_id', 'id');
    }

    public function rfq_auction()
    {
        return $this->hasOne(RfqAuction::class, 'rfq_no', 'rfq_id');
    }

    public function rfqOrders()
    {
        return $this->hasMany(Order::class, 'rfq_id', 'rfq_id');
    }
    public function rfqTechnicalApproval()
    {
        return $this->hasMany(TechnicalApproval::class, 'rfq_no', 'rfq_id');
    }

    // Rfq.php
    public function rfqAuction()
    {
        return $this->hasOne(RfqAuction::class, 'rfq_no', 'rfq_id')
            ->select(['rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time']);
    }

    public static function rfqAuctionDetails($rfq_id)
    {

        // extract filter vendor
        $cis_filter_vendors = self::extractCISFilterVendor($rfq_id);
        if (empty($cis_filter_vendors) && !empty($cis_vendors)) {
            $cis_filter_vendors = $cis_vendors;
        }

        $cis = self::where('rfq_id', $rfq_id)
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

                //  ONLY load auction prices; we won't load/use rfqVendorQuotations here
                'rfqVendorAuctionPrices' => function ($q) {
                    $q->select([
                        'id',
                        'rfq_no',
                        \DB::raw('NULL as rfq_id'),
                        'vendor_id',
                        \DB::raw('rfq_product_veriant_id as rfq_product_variant_id'),
                        \DB::raw('vend_price as price'),
                        \DB::raw('NULL as mrp'),
                        \DB::raw('NULL as discount'),
                        \DB::raw('0 as buyer_price'),
                        \DB::raw('NULL as vendor_brand'),
                        \DB::raw('vend_specs as vendor_remarks'),
                        \DB::raw('NULL as vendor_additional_remarks'),
                        \DB::raw('vend_price_basis as vendor_price_basis'),
                        \DB::raw('vend_payment_terms as vendor_payment_terms'),
                        \DB::raw('vend_delivery_period as vendor_delivery_period'),
                        \DB::raw('vend_currency as vendor_currency'),
                        'created_at',
                        'updated_at',
                    ])->orderBy('id', 'desc');
                },

                'rfqVendors' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_user_id', 'product_id', 'vendor_status');
                },
                'rfqVendors.rfqVendorProfile' => function ($q) {
                    $q->select('id', 'user_id', 'legal_name', 'date_of_incorporation', 'nature_of_business', 'company_name1', 'company_name2', 'msme_certificate', 'iso_registration');
                },
                'rfqVendors.rfqVendorDetails' => function ($q) {
                    $q->select('id', 'name', 'country_code', 'mobile');
                },
                'rfqVendors.vendorMainProduct' => function ($q) {
                    $q->select('id', 'vendor_id', 'product_id');
                },
                'rfqVendors.vendorMainProduct.product' => function ($q) {
                    $q->select('id', 'product_name');
                },
                'rfqProducts' => function ($q) {
                    $q->orderBy('product_order', 'asc');
                },
                'rfqProducts.productVariants' => function ($q) use ($rfq_id) {
                    $q->where('rfq_id', $rfq_id)->orderBy('variant_order', 'asc');
                },
                'rfqProducts.masterProduct' => function ($q) {
                    $q->select('id', 'product_name', 'division_id', 'category_id');
                },
                'rfq_auction' => function ($q) {
                    $q->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time', 'is_rfq_price_map');
                },
                'rfqOrders' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_id', 'po_number')->where('order_status', 1);
                },
                'rfqOrders.order_variants' => function ($q) {
                    $q->select('id', 'po_number', 'rfq_product_variant_id', 'order_quantity');
                },
                'rfqTechnicalApproval' => function ($q) {
                    $q->select('rfq_no', 'vendor_id', 'description', 'technical_approval');
                }
            ])
            ->first();
        // query done

        // echo "<pre>";
        // print_r($cis);
        // die;

        // Always use auction prices in place of rfqVendorQuotations
        if ($cis) {
            $cis->setRelation('rfqVendorQuotations', $cis->rfqVendorAuctionPrices ?? collect());
        }

        $cis_filter = self::cisFilter($rfq_id);

        $cis_array = self::analyzeRFQDetails($cis, $cis_filter_vendors);
        $cis_array = self::sortRFQDetails($cis_array);
        return array_merge($cis_filter, $cis_array);
    }


    public static function rfqDetails($rfq_id, $cis_vendors = [])
    {

        // extract filter vendor
        $cis_filter_vendors = self::extractCISFilterVendor($rfq_id);
        if (empty($cis_filter_vendors) && !empty($cis_vendors)) {
            $cis_filter_vendors = $cis_vendors;
        }

        $cis = self::where('rfq_id', $rfq_id)
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
                'rfqVendorQuotations' => function ($q) {
                    $q->select(
                        'id',
                        'rfq_id',
                        'vendor_id',
                        'rfq_product_variant_id',
                        'price',
                        'mrp',
                        'discount',
                        'buyer_price',
                        'vendor_brand',
                        'vendor_remarks',
                        'vendor_additional_remarks',
                        'vendor_price_basis',
                        'vendor_payment_terms',
                        'vendor_delivery_period',
                        'vendor_currency',
                        'created_at',
                        'updated_at'
                    )
                        ->where('status', 1)->orderBy('id', 'desc');
                },

                // Load auction prices with aliases to match rfqVendorQuotations
                // 'rfqVendorAuctionPrices' => function ($q) {
                //     $q->select([
                //         'id',
                //         'rfq_no',
                //         \DB::raw('NULL as rfq_id'),
                //         'vendor_id',
                //         \DB::raw('rfq_product_veriant_id as rfq_product_variant_id'),
                //         \DB::raw('vend_price as price'),
                //         \DB::raw('NULL as mrp'),
                //         \DB::raw('NULL as discount'),
                //         \DB::raw('0 as buyer_price'),
                //         \DB::raw('NULL as vendor_brand'),
                //         \DB::raw('vend_specs as vendor_remarks'),
                //         \DB::raw('NULL as vendor_additional_remarks'),
                //         \DB::raw('vend_price_basis as vendor_price_basis'),
                //         \DB::raw('vend_payment_terms as vendor_payment_terms'),
                //         \DB::raw('vend_delivery_period as vendor_delivery_period'),
                //         \DB::raw('vend_currency as vendor_currency'),
                //         'created_at',
                //         'updated_at',
                //     ])->orderBy('id', 'desc');
                // },

                'rfqVendors' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_user_id', 'product_id', 'vendor_status');
                },
                'rfqVendors.rfqVendorProfile' => function ($q) {
                    $q->select('id', 'user_id', 'legal_name', 'date_of_incorporation', 'nature_of_business', 'company_name1', 'company_name2', 'msme_certificate', 'iso_registration');
                },
                'rfqVendors.rfqVendorDetails' => function ($q) {
                    $q->select('id', 'name', 'country_code', 'mobile');
                },
                'rfqVendors.vendorMainProduct' => function ($q) {
                    $q->select('id', 'vendor_id', 'product_id');
                },
                'rfqVendors.vendorMainProduct.product' => function ($q) {
                    $q->select('id', 'product_name');
                },
                'rfqProducts' => function ($q) {
                    $q->orderBy('product_order', 'asc');
                },
                'rfqProducts.productVariants' => function ($q) use ($rfq_id) {
                    $q->where('rfq_id', $rfq_id)->orderBy('variant_order', 'asc');
                },
                'rfqProducts.masterProduct' => function ($q) {
                    $q->select('id', 'product_name', 'division_id', 'category_id');
                },
                'rfq_auction' => function ($q) {
                    $q->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time', 'is_rfq_price_map');
                },
                'rfqOrders' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_id', 'po_number')->where('order_status', 1);
                },
                'rfqOrders.order_variants' => function ($q) {
                    $q->select('id', 'po_number', 'rfq_product_variant_id', 'order_quantity');
                },
                'rfqTechnicalApproval' => function ($q) {
                    $q->select('rfq_no', 'vendor_id', 'description', 'technical_approval');
                }
            ])
            ->first();

        // If auction bids exist, override rfqVendorQuotations with them
        // if ($cis && $cis->relationLoaded('rfqVendorAuctionPrices') && $cis->rfqVendorAuctionPrices->count() > 0) {
        //     $cis->setRelation('rfqVendorQuotations', $cis->rfqVendorAuctionPrices);
        // }

        $cis_filter = self::cisFilter($rfq_id);

        $cis_array = self::analyzeRFQDetails($cis, $cis_filter_vendors);
        unset($cis);
        $cis_array = self::sortRFQDetails($cis_array);
        return array_merge($cis_filter, $cis_array);
    }

    public static function cisFilter($rfq_id)
    {
        $cis_filter = self::where('rfq_id', $rfq_id)
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
                // 1. Unique vendor_ids from vendorOrders
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

                // 2. Country & State logic from rfqVendorProfile
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
            'filter_state' => $stateIds
        ];
    }

    public static function extractCISFilterVendor($rfq_id)
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
            // Step 1: Get vendor IDs that have quotations in the date range
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

        $cis_filter = self::where('rfq_id', $rfq_id)
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

    public static function analyzeRFQDetails($cis, $filter_vendors)
    {

        $orders = [];
        $variant_order_qty = [];
        foreach ($cis->rfqOrders as $key => $order) {
            foreach ($order->order_variants as $k => $variant) {
                $orders[$variant->rfq_product_variant_id][$order->vendor_id][] = $variant->order_quantity;
                $variant_order_qty[$variant->rfq_product_variant_id] = ($variant_order_qty[$variant->rfq_product_variant_id] ?? 0) + $variant->order_quantity;
            }
        }
        $vendor_technical_approval = [];
        foreach ($cis->rfqTechnicalApproval as $key => $technical_approval) {
            $vendor_technical_approval[$technical_approval->vendor_id] = ['description' => $technical_approval->description, 'technical_approval' => $technical_approval->technical_approval];
        }

        $variants = [];
        $product_variant_count = [];
        $rfq_division = 0;
        $rfq_category = 0;
        foreach ($cis->rfqProducts as $key => $product) {
            foreach ($product->productVariants as $key => $variant) {
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
        foreach ($cis->rfqVendorQuotations as $key => $quote) {
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
                    'updated_at' => $quote->updated_at->format('Y-m-d H:i:s')
                ];
            }
        }
        unset($variant_order_qty);
        foreach ($buyer_quotes as $key => $value) {
            usort($buyer_quotes[$key], function ($a, $b) {
                return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
            });
        }

        // Count number of distinct variants per vendor
        $vendor_variant_quoted_count = [];
        foreach ($vendor_variant_map as $vendor_id => $variant_set) {
            $vendor_variant_quoted_count[$vendor_id] = count($variant_set);
        }
        unset($vendor_variant_map);

        $vendors = [];
        $vendor_total_amount = [];
        $vendor_delivery_period = [];
        $is_vendor_product = [];
        // vendor total amount
        foreach ($cis->rfqVendors as $key => $vendor) {
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
            // Sort each variant group inside vendorQuotes
            foreach ($quotes as $variantId => &$variantGroup) {
                usort($variantGroup, function ($a, $b) {
                    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
                });

                // Store the first (latest) quote after sorting
                if (!empty($variantGroup)) {
                    $first = $variantGroup[0];
                    $first['total_amount'] = (float)$first['price'] * (float)$first['variant_quantity'];
                    $total_amount += (float)$first['price'] * (float)$first['variant_quantity'];
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
                'technical_approval' => (!empty($vendor_technical_approval[$vendor_id]) && isset($vendor_technical_approval[$vendor_id])) ? $vendor_technical_approval[$vendor_id] : []
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

        // Step 1: Match keys in both arrays
        $common_vendors = array_intersect_key($vendor_variant_count, $vendor_variant_quoted_count);
        $max_quoted_vendor = [];
        if (!empty($common_vendors)) {
            // Step 2: Get the max value from those common keys
            $max_quoted = max($common_vendors);

            // Step 3: Get keys from the common set that have the max value
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

            $rfq['auction_status'] = getAuctionStatus($rfq['auction_date'], $rfq['auction_start_time'], $rfq['auction_end_time']);
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

    public static function sortRFQDetails($cis)
    {
        $sort_key = request('sort_price'); // e.g. 1, 2, 3

        $vendors = collect($cis['vendors']);
        $vendor_total_amount = collect($cis['vendor_total_amount']);
        $vendor_delivery_period = collect($cis['vendor_delivery_period']);
        $vendor_variant_quoted_count = collect($cis['vendor_variant_quoted_count']);

        $sortedVendorIDs = [];

        // Filter only vendors who quoted something (i.e., total amount > 0)
        $quotedVendorIDs = $vendor_total_amount
            ->filter(fn($amount) => $amount > 0)
            ->keys()
            ->toArray();

        $nonQuotedVendorIDs = $vendors
            ->keys()
            ->diff($quotedVendorIDs)
            ->toArray();

        if (empty($sort_key)) {
            // Default sort by quote count desc, then total amount asc — only for quoted vendors
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
                case '1': // Low to High by Total Amount
                    $sortedVendorIDs = $vendor_total_amount
                        ->only($quotedVendorIDs)
                        ->sort()
                        ->keys()
                        ->toArray();
                    break;

                case '2': // High to Low by Total Amount
                    $sortedVendorIDs = $vendor_total_amount
                        ->only($quotedVendorIDs)
                        ->sortDesc()
                        ->keys()
                        ->toArray();
                    break;

                case '3': // Delivery Period ASC
                    $sortedVendorIDs = $vendor_delivery_period
                        ->only($quotedVendorIDs)
                        ->filter() // Exclude empty/null values
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

        // Final: merge sorted vendors with the remaining unquoted ones
        $finalVendorOrder = array_merge($sortedVendorIDs, $nonQuotedVendorIDs);

        $sortedVendors = collect($finalVendorOrder)
            ->mapWithKeys(fn($id) => [$id => $vendors[$id]])
            ->toArray();

        $cis['vendors'] = $sortedVendors;

        return $cis;
    }
    public function rfqProductVariants()
    {
        return $this->hasMany(RfqProductVariant::class, 'rfq_id', 'rfq_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'rfq_id', 'rfq_id');
    }
    public static function unapprovedOrder($rfq_id, $vendor_data)
    {

        $vendor_ids = $vendor_data['vendors'];
        $variants = $vendor_data['variants'];
        $vendor_variants = $vendor_data['vendor_variants'];

        $unapproved_order = self::where('rfq_id', $rfq_id)
            ->select('id', 'rfq_id', 'buyer_id', 'buyer_user_id', 'prn_no', 'buyer_branch', 'warranty_gurarantee', 'buyer_rfq_status', 'created_at', 'updated_at')
            ->with([
                'rfqBuyerProfile' => function ($q) {
                    $q->select('user_id', 'country');
                },
                'buyer_branchs' => function ($q) {
                    $q->select('branch_id', 'name', 'address');
                },
                'rfqVendorQuotations' => function ($q) use ($vendor_ids, $variants) {
                    $q->select(
                        'id',
                        'rfq_id',
                        'vendor_id',
                        'rfq_product_variant_id',
                        'price',
                        'mrp',
                        'discount',
                        'buyer_price',
                        'vendor_brand',
                        'vendor_remarks',
                        'vendor_additional_remarks',
                        'vendor_price_basis',
                        'vendor_payment_terms',
                        'vendor_delivery_period',
                        'vendor_currency',
                        'created_at'
                    )
                        ->whereIn('id', function ($query) use ($vendor_ids, $variants) {
                            $query->selectRaw('MAX(id)')
                                ->from('rfq_vendor_quotations as quote')
                                ->where('quote.status', 1)
                                ->whereIn('quote.vendor_id', $vendor_ids)
                                ->whereIn('quote.rfq_product_variant_id', $variants)
                                ->groupBy('quote.vendor_id', 'quote.rfq_product_variant_id');
                        });
                },
                'rfqVendors' => function ($q) use ($vendor_ids) {
                    $q->select('id', 'rfq_id', 'vendor_user_id', 'product_id', 'vendor_status')->whereIn('vendor_user_id', $vendor_ids);
                },
                'rfqVendors.rfqVendorProfile' => function ($q) {
                    $q->select('id', 'user_id', 'legal_name', 'country', 'registered_address as address');
                },
                'rfqProducts' => function ($q) use ($variants) {
                    $q->orderBy('product_order', 'asc');
                    $q->whereHas('productVariants', function ($q2) use ($variants) {
                        $q2->whereIn('id', $variants);
                    });
                },
                'rfqProducts.productVariants' => function ($q) use ($rfq_id, $variants) {
                    $q->where('rfq_id', $rfq_id)->whereIn('id', $variants)->orderBy('variant_order', 'asc');
                },
                'rfqProducts.masterProduct' => function ($q) {
                    $q->select('id', 'product_name', 'division_id', 'category_id');
                },
                'rfqProducts.productVendors' => function ($q) use ($vendor_ids) {
                    $q->select('id', 'vendor_id', 'product_id', 'gst_id')->whereIn('vendor_id', $vendor_ids)->where('edit_status', '!=', 2);
                },
                'rfqOrders' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_id', 'po_number')->whereIn('order_status', [1, 3]);
                },
                'rfqOrders.order_variants' => function ($q) {
                    $q->select('id', 'po_number', 'rfq_product_variant_id', 'order_quantity');
                }
            ])
            ->first();
        // query done

        $unapproved_order = self::analyzeUnapprovedOrder($unapproved_order, $vendor_variants);
        return $unapproved_order;
    }

    public static function analyzeUnapprovedOrder($unapproved_order, $vendor_variants)
    {

        // $orders = [];
        $variant_order_qty = [];
        foreach ($unapproved_order->rfqOrders as $key => $order) {
            foreach ($order->order_variants as $k => $variant) {
                // $orders[$variant->rfq_product_variant_id][$order->vendor_id][] = $variant->order_quantity;
                $variant_order_qty[$variant->rfq_product_variant_id] = ($variant_order_qty[$variant->rfq_product_variant_id] ?? 0) + $variant->order_quantity;
            }
        }

        $variants = [];
        $vendor_product_gsts = [];
        foreach ($unapproved_order->rfqProducts as $key => $product) {
            foreach ($product->productVariants as $key2 => $variant) {
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
                    // 'lowest_price' => null,
                    // 'orders' => $orders[$variant->id] ?? []
                ];
            }
            foreach ($product->productVendors as $vendor_product) {
                $vendor_product_gsts[$vendor_product->vendor_id][$vendor_product->product_id] = $vendor_product->gst_id;
            }
        }
        // unset($orders);

        $vendor_quotes = [];
        $vendor_latest_quote = [];
        $is_qty_over = [];
        $all_qty_over = true;
        foreach ($unapproved_order->rfqVendorQuotations as $key => $quote) {
            $variant_id = $quote->rfq_product_variant_id;
            $left_qty = $variants[$variant_id]['quantity'] - ($variant_order_qty[$variant_id] ?? 0);

            $quote_data = [
                'id' => $quote->id,
                'price' => $quote->price,
                'mrp' => $quote->mrp,
                'discount' => $quote->discount,
                'buyer_price' => $quote->buyer_price,
                'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
                'variant_quantity' => $variants[$variant_id]['quantity'],
                'left_qty' => $left_qty,
            ];
            if ($left_qty <= 0) {
                $is_qty_over[] = $variant_id;
                if (($vendor_variants_key = array_search($variant_id, $vendor_variants[$quote->vendor_id])) !== false) {
                    unset($vendor_variants[$quote->vendor_id][$vendor_variants_key]);
                }
            }

            $vendor_quotes[$quote->vendor_id][$variant_id] = $quote_data;

            if (
                !isset($vendor_latest_quote[$quote->vendor_id]) ||
                strtotime($quote_data['created_at']) > strtotime($vendor_latest_quote[$quote->vendor_id]['created_at'])
            ) {
                $vendor_latest_quote[$quote->vendor_id] = [
                    'id' => $quote->id,
                    'rfq_id' => $quote->rfq_id,
                    'rfq_product_variant_id' => $variant_id,
                    'vendor_brand' => $quote->vendor_brand,
                    'vendor_remarks' => $quote->vendor_remarks,
                    'vendor_additional_remarks' => $quote->vendor_additional_remarks,
                    'vendor_price_basis' => $quote->vendor_price_basis,
                    'vendor_payment_terms' => $quote->vendor_payment_terms,
                    'vendor_delivery_period' => $quote->vendor_delivery_period,
                    'vendor_currency' => $quote->vendor_currency,
                    'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
                ];
            }
        }
        unset($variant_order_qty);

        if (!empty($is_qty_over)) {
            foreach ($is_qty_over as $key => $variant_id) {
                if (isset($variants[$variant_id])) {
                    unset($variants[$variant_id]);
                }
            }
        }
        unset($is_qty_over);

        if (count($variants) > 0) {
            $all_qty_over = false;
        }

        $vendors = [];
        foreach ($unapproved_order->rfqVendors as $key => $vendor) {
            $vendor_id = $vendor->vendor_user_id;
            $quotes = $vendor_quotes[$vendor_id] ?? [];

            $vendors[$vendor_id] = [
                'vendor_user_id' => $vendor_id,
                'legal_name' => $vendor->rfqVendorProfile->legal_name,
                'address' => $vendor->rfqVendorProfile->address,
                'country' => $vendor->rfqVendorProfile->country,
                'vendor_rfq_status' => $vendor->vendor_status,
                'vendorQuotes' => $quotes,
                'vendor_latest_quote' => $vendor_latest_quote[$vendor_id],
                'vendor_variants' => $vendor_variants[$vendor_id],
                'vendor_product_gsts' => $vendor_product_gsts[$vendor_id],
            ];
        }
        unset($vendor_quotes);
        unset($vendor_latest_quote);
        unset($vendor_variants);
        unset($vendor_product_gsts);

        $rfq = [
            'rfq_id' => $unapproved_order->rfq_id,
            'prn_no' => $unapproved_order->prn_no,
            'buyer_branch' => $unapproved_order->buyer_branch,
            'warranty_gurarantee' => $unapproved_order->warranty_gurarantee,
            'buyer_branch_name' => $unapproved_order->buyer_branchs->name,
            'buyer_branch_address' => $unapproved_order->buyer_branchs->address,
            'buyer_country' => $unapproved_order->rfqBuyerProfile->country,
            'buyer_rfq_status' => $unapproved_order->buyer_rfq_status,
        ];

        $data = [
            'rfq' => $rfq,
            'all_qty_over' => $all_qty_over,
            'variants' => $variants,
            'vendors' => $vendors,
        ];

        return $data;
    }
}
