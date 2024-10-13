@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Account Logs'])

    <div class="row mt-4 mx-4">
        <div class="col-12">
            @if (session('success'))
                <div id="success-message" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="alert alert-light" role="alert">
                Below are the logs of account <strong>login times</strong>.
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Account Logs</h6>
                    <div class="d-flex align-items-center">
                        <form method="GET" action="{{ route('account-log.index') }}" class="d-flex me-2">
                            <select name="filter" class="form-select me-2" id="filter-select">
                                <option value="all">Semua</option>
                                <option value="newest" {{ request('filter') == 'newest' ? 'selected' : '' }}>10 Paling Baru</option>
                                <option value="oldest" {{ request('filter') == 'oldest' ? 'selected' : '' }}>10 Paling Lama</option>
                            </select>

                            <select name="sort_by" class="form-select me-2" id="sort-by-select" {{ (request('filter') == 'newest' || request('filter') == 'oldest') ? 'disabled' : '' }}>
                                <option value="login_time" {{ request('sort_by') == 'login_time' ? 'selected' : '' }}>Login Time</option>
                                <option value="user" {{ request('sort_by') == 'user' ? 'selected' : '' }}>User</option>
                                <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
                            </select>

                            <select name="sort_order" class="form-select me-2">
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending (A-Z)</option>
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending (Z-A)</option>
                            </select>

                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                        <form method="GET" action="{{ route('account-log.export') }}" class="d-flex">
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                            <button type="submit" class="btn btn-secondary ms-2">Export to PDF</button>
                        </form>
                        <!-- Input Pencarian -->
                        <input type="text" id="search-input" class="form-control ms-3" placeholder="Search by user or email" style="width: 200px;" autocomplete="off">
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="logs-table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Login Time</th>
                                </tr>
                            </thead>
                            <tbody id="logs-body">
                                @foreach ($logs as $log)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <img src="{{ asset('img/default-avatar.png') }}" class="avatar me-3" alt="user-image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $log->user->firstname }} {{ $log->user->lastname }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $log->user->email }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <p class="text-sm font-weight-bold mb-0">{{ $log->login_time }}</p>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($logs->isEmpty())
                            <p class="text-center text-sm py-3">No account login logs available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hide the success message after 7 seconds
        setTimeout(function() {
            var successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 7000); // 7000 ms = 7 seconds

        // Tambahkan event listener untuk filter select
        document.getElementById('filter-select').addEventListener('change', function() {
            const sortBySelect = document.getElementById('sort-by-select');
            if (this.value === 'newest' || this.value === 'oldest') {
                sortBySelect.value = 'login_time'; // Set pilihan ke "Login Time"
                sortBySelect.disabled = true; // Nonaktifkan dropdown
            } else {
                sortBySelect.disabled = false; // Aktifkan kembali dropdown jika bukan "10 Paling Baru/Lama"
            }
        });

        // Pencarian secara langsung
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#logs-body tr');

            rows.forEach(row => {
                const userCell = row.cells[0].textContent.toLowerCase();
                const emailCell = row.cells[1].textContent.toLowerCase();
                if (userCell.includes(searchTerm) || emailCell.includes(searchTerm)) {
                    row.style.display = ''; // Tampilkan baris
                } else {
                    row.style.display = 'none'; // Sembunyikan baris
                }
            });
        });
    </script>
@endsection
