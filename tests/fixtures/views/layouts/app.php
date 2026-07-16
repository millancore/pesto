<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title | upper }}</title>
</head>
<body>
    <header>{{ $header | slot }}</header>

    <main>{{ $main | slot }}</main>

    <footer>{{ $year | date:'Y' }}</footer>
</body>
</html>
