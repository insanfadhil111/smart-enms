@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'User Management'])
    
    <div class="row mt-4 mx-4">
        <div class="col-12">
            @if (session('success'))
                <div id="success-message" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <div class="alert alert-light" role="alert">
                Here are some registered <strong>users</strong>.
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Users</h6>
                    <div class="d-flex align-items-center">
                        <input type="text" id="search-input" class="form-control me-2" placeholder="Search by firstname, lastname, email, role" style="width: 300px;" autocomplete="off">
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Add Account</a>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="users-table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Create Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody id="users-body">
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div>
                                                    <img src="{{ asset('img/' . $user->image) }}" class="avatar me-3" alt="image">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->firstname }} {{ $user->lastname }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $user->level }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">{{ $user->created_at->format('d/m/Y') }}</p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <!-- Edit Account -->
                                            <a href="{{ route('account.edit', $user->id) }}" class="text-sm font-weight-bold text-info">Edit</a>

                                            <!-- Delete Account -->
                                            <form action="{{ route('account.destroy', $user->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-sm font-weight-bold text-danger ps-3" onclick="return confirm('Are you sure you want to delete this account?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk menghilangkan pesan setelah 7 detik (7000 milidetik)
        setTimeout(function() {
            var successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 7000); // 7000 ms = 7 detik

        // Pencarian secara langsung
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#users-body tr');

            rows.forEach(row => {
                const userName = row.cells[0].textContent.toLowerCase(); // Nama
                const userEmail = row.cells[0].querySelector('.text-xs.text-secondary').textContent.toLowerCase(); // Email
                const userRole = row.cells[1].textContent.toLowerCase(); // Role

                if (userName.includes(searchTerm) || userEmail.includes(searchTerm) || userRole.includes(searchTerm)) {
                    row.style.display = ''; // Tampilkan baris
                } else {
                    row.style.display = 'none'; // Sembunyikan baris
                }
            });
        });
    </script>
@endsection
