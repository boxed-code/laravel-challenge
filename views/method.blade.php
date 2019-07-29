<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification</title>
    @include('challenge::_styles')
</head>
<body>
    <form method="POST" action="{{ $form_action_url }}">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6 ml-auto mr-auto">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">2FA Verification</h5>
                            <h6 class="card-subtitle mb-3 text-muted">How would you like to verify yourself?</h6>
                            @foreach ($methods as $key => $label)
                            <div class="custom-control custom-radio">
                              <input type="radio" id="{{ $key }}" name="method" value="{{ $key }}" class="custom-control-input">
                              <label class="custom-control-label" for="{{ $key }}">{{ $label }}</label>
                            </div>
                            @endforeach
                            @csrf
                            <div class="mt-3">
                                <button class="btn btn-primary" type="submit">Continue</button>
                                @if (in_array($verification_purpose, ['auth', 'device_auth']))
                                or <a href="{{ route('logout') }}">Cancel and logout</a>
                                @else
                                or <a href="javascript:history.go(-1)">Go back</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
</html>