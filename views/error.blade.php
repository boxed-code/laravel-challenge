<!DOCTYPE html>
<html>
<head>
    <title>TFA Error</title>
</head>
<body>
    Error
    @if (!empty($errors))
        @foreach ($errors->getBag('default')->all() as $error)
            {{ $error }}
        @endforeach
    @endif
</body>
</html>