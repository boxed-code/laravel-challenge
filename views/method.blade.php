<!DOCTYPE html>
<html>
<head>
    <title>TFA Method Selection</title>
</head>
<body>
    <p>Which verification method would you like to use?</p>

    <form method="POST" action="{{ url($challenge_path) }}">
        @foreach ($methods as $key => $label)
            <label>
                <input type="radio" name="method" value="{{ $key }}">
                {{ $label }}
            </label>
        @endforeach
        @csrf
        <br>
        <br>
        <button type="submit">Continue</button>
    </form>
</body>
</html>