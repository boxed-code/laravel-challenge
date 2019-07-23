<!DOCTYPE html>
<html>
<head>
    <title>TFA Verification</title>
</head>
<body>
    @if (!empty($errors))
        @foreach ($errors->getBag('default')->all() as $error)
            {{ $error }}
        @endforeach
    @endif
    <form method="POST" action="{{ url($verificationPath) }}">
        <input type="text" name="code" placeholder="Code">
        <button type="submit">Verify</button>
        @csrf
    </form>
</body>
</html>