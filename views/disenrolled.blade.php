<!DOCTYPE html>
<html>
<head>
    <title>Disenrolled | 2FA Verification</title>
    @include('challenge::_styles')
</head>
<body>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 ml-auto mr-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Success! You have been disenrolled</h5>

                        <p class="card-text">To use {{ $method }} authentication again you will need to re-enrol.</p>

                        <a href="{{ url('/') }}" class="btn btn-primary">Continue</a> or
                        <a href="{{ route('challenge.enrol', $method) }}">re-enrol now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>