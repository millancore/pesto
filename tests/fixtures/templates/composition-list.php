<?php
    $item2 = false;
?>

<ul php-partial="list.php" php-with="['id' => 'maria']">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>
        <ul php-partial="list.php" php-with="['id' => 456]">
            <li>child Item 3</li>
            <li>child Item 4</li>
            <li>item value: {{ $item2 }}</li>
            <li>item type: {{ gettype($item2) }}</li>
            <li>ternary: {{ $item2 ? 'aaa' : 'bbb' }}</li>
        </ul>
    </li>
</ul>

