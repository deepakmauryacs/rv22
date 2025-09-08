<div class="table-responsive">
    <table class="product_listing_table">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $key => $user)
            <tr>
                <td>{{ $users->firstItem() + $key }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->country_code ? '+' . $user->country_code : '' }} {{ $user->mobile }}</td>
                <td>{{ $user->role->role_name ?? 'N/A' }}</td>
                <td>

                <span>
                        <label class="switch" for="status-{{ $user->id }}">
                            <input type="checkbox" 
                            id="status-{{ $user->id }}" 
                                   class="status-toggle" 
                                  data-id="{{ $user->id }}"
                                  {{ $user->status == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </span>
                </td>
                <td>
                    @if(checkPermission('ADMIN_USERS','edit','3'))
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-rfq btn-rfq-secondary btn-sm">
                             Edit
                        </a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No users found</td>
            </tr>
        @endforelse
    </tbody>
    </table>
</div>
<x-paginationwithlength :paginator="$users" />
