<html>
<head><title>{{ $title }}</title></head>
<body>
    <h1>{{ $heading }}</h1>
    <ul>
        @foreach($items as $item)
        <li>
            <strong>{{ $item['name'] }}</strong>
            <span>{{ $item['email'] }}</span>
            <p>{{ $item['bio'] }}</p>
        </li>
        @endforeach
    </ul>
</body>
</html>
