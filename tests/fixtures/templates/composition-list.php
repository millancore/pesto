<ul php-partial="list.php" php-with="['id' => 'list']">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>
        <ul php-partial="list.php"
            php-with="['id' => 'sublist']"
            php-inner>
            <li>child Item 3</li>
            <li>child Item 4</li>
        </ul>
    </li>
</ul>

