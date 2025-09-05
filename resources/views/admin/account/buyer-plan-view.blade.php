@extends('admin.layouts.app_second',['title' => 'Buyer','sub_title' => 'Account'])
@section('css')
<style>
.pagination .page-link {
    border-radius: 8px;
    margin: 0 3px;
    font-size: 16px;
    color: #3b82f6;
}

.pagination .active .page-link {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Buyer Account Details</h6>
            <div class="float-right">
                <a href="{{ route('admin.accounts.buyer') }}" class="btn btn-sm btn-secondary m-1 mt-0 mb-0">BACK</a>
                <a href="{{ route('admin.accounts.buyer.plan.invoice', $buyerPlan->id) }}" class="btn btn-secondary btn-sm">Download Invoice</a>
            </div>
        </div>
        <div class="card-body">
             
            <div class="table-responsive" id="table-container">
                <table class="table table-bordered">
                    <tr>
                        <td><b>Buyer Name:</b> {{$buyer->legal_name}}</td>
                        <td><b>No. Of Users:</b> {{$buyerPlan->no_of_users}}</td>
                    </tr>
                    <tr>
                        <td><b>Buyer Code:</b> {{$buyer->buyer_code}}</td>
                        <td><b>Buyer Contact:</b> {{$user->mobile}}</td>
                    </tr>
                    <tr>
                        <td><b>Buyer Email:</b> {{$user->email}}</td>
                        <td><b>Date of Subscription:</b> {{$buyerPlan->start_date}}</td>
                    </tr>
                    <tr>
                        <td><b>Subscription Period:</b> {{$buyerPlan->subscription_period}}</td>
                        <td><b>Next Renewal Date:</b> {{$buyerPlan->next_renewal_date}}</td>
                    </tr>
                    <tr>
                        <td><b>Amount:</b> ₹{{$buyerPlan->final_amount}}</td>
                        <td><b>Proforma Invoice No:</b> {{$buyerPlan->invoice_no}}</td>
                    </tr>
                    <tr>
                        <td><b>Plan Name:</b> {{$buyerPlan->plan_name}}</td>
                        <td><b>Transaction Id:</b> {{$buyerPlan->transaction_id}}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function assignedManager(user_id, manage_id) {
    $.ajax({
        url: "{{ route('admin.accounts.buyer.manager') }}",
        type: "post",
        data: {
            _token: "{{ csrf_token() }}",
            user_id,
            manage_id
        },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');
        },
    });
}
</script>
@endsection