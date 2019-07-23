<!DOCTYPE html>
<html>
<head>
    <title>TFA Setup</title>
</head>
<body>
    @if (!empty($errors))
        @foreach ($errors->getBag('default')->all() as $error)
            {{ $error }}
        @endforeach
    @endif
    <form method="POST" action="{{ url($setupPath) }}">
        <input type="text" name="test_method" placeholder="Test Data">
        <button type="submit">Continue</button>
        @csrf
    </form>
</body>
</html>
{{ dd($setupData) }}