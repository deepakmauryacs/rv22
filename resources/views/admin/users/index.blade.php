@extends('admin.layouts.app_second', [
    'title' => 'User Management',
    'sub_title' => ''
])
@section('css')
<style>
    .form-floating>.form-control, .form-floating>.form-select {
        width: 172px;
        max-width: 100%;
    }
    @media (max-width: 767px) {
        .form-floating>.form-control, .form-floating>.form-select {
        width: 100% !important;
    }
    ul.rfq-filter-button li {
        display: block;
    }
    }
</style>
@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <a href="{{ route('admin.users.index') }}"> User Management </a>
                </li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-body">
                <div class="col-md-12 botom-border">
                    <h1>All Users</h3>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
                            
                            <form id="searchForm" action="{{ route('admin.users.index') }}" method="GET">
                                <div class="row pt-2 pt-sm-4 gy-4 gx-3">
                                    <div class="col-12 col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" id="search_user" name="search"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('search') }}"
                                                    placeholder="Search by name/email/contact" style="width: 350px;">
                                                <label for="search_user">Search By Name/Email/Contact</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select name="status" id="user_status"
                                                    class="form-select fillter-form-select">
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                                                        Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>
                                                        Inactive</option>
                                                </select>
                                                <label for="user_status">Status</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{ route('admin.users.index') }}"
                                            class="btn-style btn-style-danger">RESET</a>
                                            
                                    </div>
                                    <div class="col-auto">
                                    <a href="{{ route('admin.users.create') }}" class="btn-rfq btn-rfq-white ">
                                            <i class="bi bi-plus"></i> ADD 
                                        </a>
                                    </div>
                                </div>
                            </form>

                        </div>

                    </div>

                    <div class="product_listing_table_wrap" id="table-container">
                        @include('admin.users.partials.table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    $(document).on('change', '#perPage', function() {
        const form = $('#searchForm');
        const formData = form.serialize();
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;
        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function() {
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

    

    // Delete user
    $('.delete-user').click(function() {
        var userId = $(this).data('id');

        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: "{{ url('super-admin/users') }}/" + userId,
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred. Please try again.');
                }
            });
        }
    });

    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        const userId = $(this).data('id');
        const isActive = $(this).is(':checked');
        
        $.ajax({
            url: "{{ route('admin.users.updateStatus', ['id' => ':id']) }}".replace(':id', userId),
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    $(this).prop('checked', !isActive);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred. Please try again.');
                $(this).prop('checked', !isActive);
            }
        });
    });
    
})
</script>
@endsection