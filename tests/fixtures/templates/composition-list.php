<?php
$item2 = htmlspecialchars('Item custom', ENT_QUOTES, 'UTF-8')
?>

<ul php-partial="list.php" php-with="['id' => 123]">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>
        <ul php-partial="list.php" php-with="['id' => 456]">
            <li>child Item 3</li>
            <li>child Item 4</li>
            <li>{{ $item2 }}</li>
        </ul>
    </li>
</ul>

