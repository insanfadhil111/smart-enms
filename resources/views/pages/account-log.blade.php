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
                <div class="card-header pb-0 d-flex justify-content-between">
                    <h6>Account Logs</h6>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Login Time</th>
                                </tr>
                            </thead>
                            <tbody>
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
@endsection

<script>
    // Hide the success message after 7 seconds
    setTimeout(function() {
        var successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 7000); // 7000 ms = 7 seconds
</script>
