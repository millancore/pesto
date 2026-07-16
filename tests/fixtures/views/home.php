<template php-partial="layouts/app.php" php-with="['title' => 'Home']">

    <nav php-slot="header">
        <ul p:partial="partials/nav.php"></ul>
    </nav>

    <section>
        <h1>{{ $heading | capitalize }}</h1>
        <ul>
            <li p:foreach="$items as $item" p:if="$item->visible">{{ $item->label | upper }}</li>
        </ul>
    </section>
</template>
