<!DOCTYPE html>
<html>
<head>
    <title>Setup | 2FA Verification</title>
    @include('two_factor::_styles')
</head>
<body>
    <form method="POST" action="{{ $form_action_url }}">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-5 ml-auto mr-auto">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">2FA Verification</h5>
                            <h6 class="card-subtitle mb-3 text-muted">Google Authenticator Setup</h6>

                            @if (!empty($errors))
                                <div class="mb-3 text-danger">
                                @foreach ($errors->getBag('default')->all() as $error)
                                    {{ $error }}
                                @endforeach
                                </div>
                            @endif

                            <p class="card-text">
                                Please scan the QR code or enter the code manually into your authenticator app.
                            </p>

                            <div class="mb-5">
                                <img src="{!! $setup_data['qr_png'] !!}">
                                <span class="text-muted">{{ $setup_data['secret'] }}</span>
                            </div>

                            @csrf
                            <div>
                                <button class="btn btn-primary" type="submit">Continue</button>
                                or <a href="{{ url('/') }}">cancel setup</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
</html>