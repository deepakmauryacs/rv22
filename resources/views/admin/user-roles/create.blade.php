@extends('admin.layouts.app_second', [
    'title' => 'Add Role',
    'sub_title' => 'Create'
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.user-roles.index') }}">Roles List</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Role</li>
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
                        <div class="basic-form pro_edit">
                            <form id="createRoleForm" class="form-horizontal form-material">
                                @csrf
                                <div class="row mb-3 mt-2 gy-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" class="form-control fillter-form-control w-100" id="role_name" name="role_name" placeholder="Enter Role Name" oninput="limitCharacters(this, 100)">
                                                <label for="role_name">Role Name<sup class="text-danger">*</sup></label>
                                            </div>
                                        </div>


                                        <!-- <label for="role_name" class="form-label"><strong>Role Name</strong> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="role_name" name="role_name" placeholder="Enter Role Name" oninput="limitCharacters(this, 100)"> -->
                                        <span class="text-danger error-text role_name_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="d-flex gap-1 justify-content-md-end">
                                        <button type="submit" class="btn-rfq btn-rfq-primary">Save</button>
                                        <a href="{{ route('admin.user-roles.index') }}" class="btn-rfq btn-rfq-danger">Cancel</a>
                                     </div>
                                   </div>
                                </div>

                                <h1 class="my-4">Permissions</h1>
                                <div class="table-responsive">
                                    <table class="product_listing_table">
                                        <thead>
                                            <tr>
                                                <th class="module-name border-0">
                                                    <div class="d-flex align-items-center">
                                                        <input type="checkbox" id="select-all-modules" class="select-all me-4">
                                                        <label for="select-all-modules" class="fw-bold text-uppercase font-size-11 ms-1">Module/Submodule</label>
                                                    </div>
                                                </th>
                                                <th class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <label for="select-all-add" class="fw-bold text-uppercase font-size-11 me-1">Add</label>
                                                        <input type="checkbox" id="select-all-add" class="select-all">
                                                    </div>
                                                </th>
                                                <th class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <label for="select-all-edit" class="fw-bold text-uppercase font-size-11 me-1">Edit</label>
                                                        <input type="checkbox" id="select-all-edit" class="select-all">
                                                    </div>
                                                </th>
                                                <th class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <label for="select-all-delete" class="fw-bold text-uppercase font-size-11 me-1">Delete</label>
                                                        <input type="checkbox" id="select-all-delete" class="select-all">
                                                    </div>
                                                </th>
                                                <th class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <label for="select-all-view" class="fw-bold text-uppercase font-size-11 me-1">View</label>
                                                        <input type="checkbox" id="select-all-view" class="select-all">
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modules as $module)
                                                <tr>
                                                    <td class="module-name">
                                                        <input type="checkbox" class="module-check me-4" data-id="{{ $module->id }}">
                                                        <span class="font-size-11">{{ strtoupper($module->module_name) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($module->available_permissions['add'])
                                                            <input type="checkbox" name="permissions[{{ $module->id }}][add]" value="1" class="add-checkbox">
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($module->available_permissions['edit'])
                                                            <input type="checkbox" name="permissions[{{ $module->id }}][edit]" value="1" class="edit-checkbox">
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($module->available_permissions['delete'])
                                                            <input type="checkbox" name="permissions[{{ $module->id }}][delete]" value="1" class="delete-checkbox">
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($module->available_permissions['view'])
                                                            <input type="checkbox" name="permissions[{{ $module->id }}][view]" value="1" class="view-checkbox">
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                               
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All for each permission type
    document.getElementById('select-all-add').addEventListener('change', function() {
        document.querySelectorAll('.add-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('select-all-edit').addEventListener('change', function() {
        document.querySelectorAll('.edit-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('select-all-delete').addEventListener('change', function() {
        document.querySelectorAll('.delete-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('select-all-view').addEventListener('change', function() {
        document.querySelectorAll('.view-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Select All Modules (checks all checkboxes in all rows)
    document.getElementById('select-all-modules').addEventListener('change', function() {
        document.querySelectorAll('tbody tr').forEach(row => {
            const checkboxes = row.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Also update other select-all checkboxes
        document.querySelectorAll('thead .select-all').forEach(checkbox => {
            if (checkbox.id !== 'select-all-modules') {
                checkbox.checked = this.checked;
            }
        });
    });

    // Row-wise Module checkbox — check/uncheck the entire row
    const moduleCheckboxes = document.querySelectorAll('.module-check');

    moduleCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const inputs = row.querySelectorAll('input[type="checkbox"]:not(.module-check)');
            inputs.forEach(function(input) {
                input.checked = checkbox.checked;
            });
        });
    });
});
</script>
<script>
$(document).ready(function() {
    $('#createRoleForm').submit(function(e) {
        e.preventDefault();
        
        

        // Get form values
        const role_name = $('#role_name').val().trim();
      

        let hasErrors = false;

        // Client-side validation
        if (!role_name) {
            toastr.error('Please enter the role name.');
            hasErrors = true;
        } else if (role_name.length < 2) {
            toastr.error('Role name must be at least 2 characters.');
            hasErrors = true;
        }

        

        if (hasErrors) return;


        $.ajax({
            url: "{{ route('admin.user-roles.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success('Success: ' + response.message);
                window.location.href = "{{ route('admin.user-roles.index') }}";
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = [];

                    for (var key in errors) {
                        errorMessages.push(errors[key][0]);
                    }

                    toastr.error('Validation Error:\n' + errorMessages.join('\n'));
                } else {
                    // Other errors
                    toastr.error('Error: ' + (xhr.responseJSON.message || 'An error occurred'));
                }
            }
        });
    });
});
</script>
@endsection