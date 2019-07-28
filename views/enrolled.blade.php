<!DOCTYPE html>
<html>
<head>
    <title>Enrolled | 2FA Verification</title>
    @include('challenge::_styles')
</head>
<body>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 ml-auto mr-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Success! You have been enrolled</h5>

                        <p class="card-text">You can now use {{ $method }} authentication to sign in to your account.</p>

                        <a href="{{ url('/') }}" class="btn btn-primary">Continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>