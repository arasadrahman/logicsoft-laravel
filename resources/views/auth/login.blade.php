<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
</head>
<body class="login-page">

<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <h2>Login</h2>
        </div>
        <div class="card-body">
            <form id="loginForm">
                @csrf

                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username or Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/adminlte.min.js') }}"></script>

<script>
$('#loginForm').on('submit', function(e){
    e.preventDefault();

    $.ajax({
        url: "{{ url('/login') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            if(res.success){
                toastr.success("Login successful");
                setTimeout(() => {
                    window.location.href = "{{ url('/dashboard') }}";
                }, 1000);
            } else {
                toastr.error(res.message);
            }
        },
        error: function(res){
            console.log(res);
            if (res.responseJSON.message){
                toastr.error(res.responseJSON.message);
            } else {
                toastr.error("Something went wrong");
            }
        }
    });
});
</script>

</body>
</html>
