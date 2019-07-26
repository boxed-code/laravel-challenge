<!DOCTYPE html>
<html>
<head>
    <title>Ooops! | 2FA Verification</title>
    @include('two_factor::_styles')
</head>
<body>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 ml-auto mr-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ooops! There has been a problem</h5>

                        @if (!empty($errors))
                            <div class="mb-3 text-danger">
                            @foreach ($errors->getBag('default')->all() as $error)
                                {{ $error }}
                            @endforeach
                            </div>
                        @endif

                        <a href="{{ url('/') }}" class="btn btn-primary">Continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>