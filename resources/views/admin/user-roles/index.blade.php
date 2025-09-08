@extends('admin.layouts.app_second', [
    'title' => 'Role List',
    'sub_title' => 'User Roles'
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Role List</li>
            </ol>
        </nav>
    </div>
</div>
@endsection


@section('content')
<div class="page-start-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h1 class="mb-0">User Roles</h1>
                            @if(checkPermission('MANAGE_ROLE','add','3'))
                            <a href="{{ route('admin.user-roles.create') }}" class="btn-rfq btn-rfq-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Add New Role
                            </a>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="product_listing_table">
                                <thead>
                                    <tr>
                                        <th width="5%">SR.NO.</th>
                                        <th>Role Name</th>
                                        <th class="text-center">Manage Permission</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Modified</th>
                                        <!-- <th width="15%">Actions</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $index => $role)
                                        <tr>
                                            <td>{{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}</td>
                                            <td>{{ $role->role_name }}</td>
                                            <td class="text-center w-200px">
                                                <a href="{{ route('admin.user-roles.edit', $role->id) }}" class="btn-sm btn-rfq btn-rfq-white width-inherit px-2 py-1">
                                                    <i class="bi bi-lock-fill"></i>
                                                </a>
                                            </td>
                                            <td class="text-center w-100px">
                                            <span>
                                                    <label class="switch" for="status-{{ $role->id }}">
                                                        <input type="checkbox"
                                                            class="status-toggle" 
                                                            data-id="{{ $role->id }}" 
                                                            id="status-{{ $role->id }}" 
                                                            {{ $role->is_active ? 'checked' : '' }}
                                                            >
                                                        <span class="slider round"></span>
                                                    </label>
                                                </span>
                                            </td>
                                            <td class="text-center w-100px">{{ $role->updated_at->format('d/m/Y') }}</td>
                                           <!--  <td>
                                                <div class="action-buttons">
                                                    <button class="btn-rfq btn-rfq-danger btn-sm delete-btn" data-id="{{ $role->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td> -->
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No roles found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <x-paginationwithlength :paginator="$roles" />
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
    // Status toggle handler
    $('.status-toggle').on('change', function() {
        const roleId = $(this).data('id');
        const isActive = $(this).is(':checked');

        $.ajax({
            url: "{{ route('admin.user-roles.status', ['id' => ':id']) }}".replace(':id', roleId),
            type: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                is_active: isActive ? 1 : 0
            },
            beforeSend: function() {
                $(`#status-${roleId}`).prop('disabled', true);
            },
            success: function(response) {
                toastr.success(response.message || 'Status updated successfully');
            },
            error: function(xhr) {
                // Revert the toggle on error
                $(`#status-${roleId}`).prop('checked', !isActive);
                toastr.error(xhr.responseJSON?.message || 'Error updating status');
            },
            complete: function() {
                $(`#status-${roleId}`).prop('disabled', false);
            }
        });
    });

    // Delete button handler
    $(document).on('click', '.delete-btn', function() {
        const roleId = $(this).data('id');
        const deleteUrl = "{{ route('admin.user-roles.destroy', ':id') }}".replace(':id', roleId);

        if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $(`button[data-id="${roleId}"]`).prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Role deleted successfully');
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    } else {
                        toastr.error(response.message || 'Failed to delete role');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error deleting role');
                },
                complete: function() {
                    $(`button[data-id="${roleId}"]`).prop('disabled', false);
                }
            });
        }
    });
});
</script>
@endsection