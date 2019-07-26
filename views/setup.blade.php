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
    <form method="POST" action="{{ $form_action_url }}">
        This view should be overridden by the notification provider.
        <button type="submit">Continue</button>
        @csrf
    </form>
</body>
</html>
{{ dd($setup_data) }}