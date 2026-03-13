@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Profile Info --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Profile Information</h4>
                </div>
                <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">

                        <div class="form-group mb-3">
                            <label>Shop Name</label>
                            <input type="text" class="form-control" value="{{ $user->ShopName }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label>Shop Prefix</label>
                            <input type="text" class="form-control" value="{{ $user->ShopPrefix }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label>User Name</label>
                            <input type="text" name="UserName" class="form-control" value="{{ $user->UserName }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="Email" class="form-control" value="{{ $user->Email }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Mobile</label>
                            <input type="text" name="Mobile" class="form-control" value="{{ $user->Mobile }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Logo</label>
                            <input type="file" name="Logo" class="form-control">
                            @if($user->Logo && file_exists(public_path('uploads/logos/'.$user->Logo)))
                                <img src="{{ asset('uploads/logos/'.$user->Logo) }}" class="mt-2" height="80">
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Password Change --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Change Password</h4>
                </div>
                <form method="POST" action="{{ route('account.password') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Current Password</label>
                            <input type="text" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password</label>
                            <input type="text" name="password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <input type="text" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-danger">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
