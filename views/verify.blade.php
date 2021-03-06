<!DOCTYPE html>
<html>
<head>
    <title>Verify | 2FA Verification</title>
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
                            <h6 class="card-subtitle mb-3 text-muted">Enter the code you have recieved via {{ $method_display_label }} below</h6>

                            @if (!empty($errors))
                                <div class="mb-3 text-danger">
                                @foreach ($errors->getBag('default')->all() as $error)
                                    {{ $error }}
                                @endforeach
                                </div>
                            @endif

                            <div class="form-group">
                                <input class="form-control form-control-lg" type="text" name="code" placeholder="Code">
                            </div>

                            @csrf
                            <div class="mt-3">
                                <button class="btn btn-primary" type="submit">Verify</button>
                                or <a href="{{ route('challenge') }}">select a different method</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
</html>