<template php-partial="list.php" php-with="['id' => 'maria']">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>
        <template php-partial="list.php" php-with="['id' => 456]">
            <template php-foreach="range(1, 10) as $number">
                <li php-if="$number > 4">Item {{ $number }}</li>
            </template>
        </template>
    </li>
</template>