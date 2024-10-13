@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Account'])

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Edit Account</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('account.update', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-3">
                                <label for="username">Username</label>
                                <input type="text" name="username" value="{{ $user->username }}" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="firstname">First Name</label>
                                <input type="text" name="firstname" value="{{ $user->firstname }}" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="lastname">Last Name</label>
                                <input type="text" name="lastname" value="{{ $user->lastname }}" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="password">Password (optional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            </div>

                            <!-- <div class="form-group">
                                <label for="image">Profile Image</label>
                                <input type="file" name="image" class="form-control">
                            </div> -->

                            <div class="form-group mb-3">
                                <label for="level">Role</label>
                                <select name="level" class="form-control" required>
                                    <option value="super admin" {{ $user->level == 'super admin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="admin" {{ $user->level == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="user" {{ $user->level == 'user' ? 'selected' : '' }}>User</option>
                                    <option value="guest" {{ $user->level == 'guest' ? 'selected' : '' }}>Guest</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
