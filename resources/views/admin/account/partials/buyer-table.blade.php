<div class="table-responsive">
    <table class="product_listing_table">
    <thead>
        <tr>
            <th>Buyer Name</th>
            <th>Plan Name</th>
            <th>Invoice No</th>
            <th>No. of Users</th>
            <th>Buyer Code</th>
            <th>Buyer Contact</th>
            <th>Buyer Email</th>
            <th>Date of Subscription</th>
            <th>Period</th>
            <th>Next Renewal Date</th>
            <th>Amount</th>
            <th>Assign Manager</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
            <tr>
                <td class="text-wrap keep-word">{{ $result->legal_name ?? ''}}</td>
                <td>
                    {{ $result->latestPlan->plan_name ?? ''}}
                </td>
                <td>{{ $result->latestPlan->invoice_no ?? ''}}</td>
                <td>{{ $result->latestPlan->no_of_users ?? ''}}</td>
                <td>{{ $result->buyer_code ?? ''}}</td>
                <td>{{ $result->users->mobile ?? ''}}</td>
                <td>{{ $result->users->email ?? ''}}</td>
                <td>
                    {{ $result->latestPlan->start_date ?? ''}}
                </td>
                <td>{{ $result->latestPlan->subscription_period ?? ''}}</td>
                <td>
                    {{ $result->latestPlan->next_renewal_date ?? '' }}
                </td>
                <td>
                ₹{{ $result->latestPlan->final_amount ?? '' }}
                 
                </td>
                <td>
                    <select class="form-select"  name="assigned_manager" onchange="assignedManager('{{ $result->id }}',this.value);">
                        <option value="">Select Manager</option>
                        @foreach ($managers as $manager)
                            <option {{ $result->assigned_manager == $manager->id ? 'selected' : '' }} value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    @if(!empty($result->latestPlan)&&$result->latestPlan->final_amount > 0)
                    <span>
                        <a href="{{ route('admin.accounts.buyer.plan.view', $result->latestPlan->id) }}" class="btn-rfq btn-rfq-primary btn-sm w-100">View Current</a>
                    </span>
                    @elseif(!empty($result->latestPlan)&&$result->latestPlan->final_amount == 0 && $result->latestPlan->next_renewal_date < date('Y-m-d'))
                    <div class="row plan-row">
                        <div class="col-md-12 d-flex gap-2">
                            <select class="form-select" style="width:80px;" id="extend_plan_month_{{ $result->id }}">
                                <option value=""> </option>
                                <option value="1"> 1 </option>
                                <option value="2"> 2 </option>
                                <option value="3"> 3 </option>
                                <option value="6"> 6 </option>
                                <option value="12"> 12 </option>
                            </select>
                            <a href="javascript:void(0);" class="btn-rfq btn-rfq-primary btn-sm" onclick="extendFreePlan('{{ $result->id }}','{{ $result->latestPlan->id ?? ''}}','{{ $result->users->id ?? ''}}','{{ $result->legal_name ?? ''}}');">Extend </a>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-12 d-flex gap-2">
                            <select class="form-select" style="width:80px;" id="extend_plan_month_{{ $result->id }}">
                                <option value="">Select</option>
                                <option value="1"> 1 </option>
                                <option value="2"> 2 </option>
                                <option value="3"> 3 </option>
                                <option value="6"> 6 </option>
                                <option value="12"> 12 </option>
                            </select>
                            <a href="javascript:void(0);" class="btn-rfq btn-rfq-primary btn-sm" onclick="extendFreePlan('{{ $result->id }}','{{ $result->latestPlan->id ?? ''}}','{{ $result->users->id ?? ''}}','{{ $result->legal_name ?? ''}}');">Extend </a>
                        </div>
                    </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No Vendor found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
</div>
<x-paginationwithlength :paginator="$results" />

    