<html>
<head><title>{{ $title }}</title></head>
<body>
    @include('_header', ['siteName' => $siteName])
    <main>
        <h2>{{ $heading }}</h2>
        <p>{{ $content }}</p>
    </main>
</body>
</html>
