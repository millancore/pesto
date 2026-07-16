<!-- Pesto lint test file: every numbered block contains a deliberate error.
     Note: PHP reports only the first syntax error per file, so there is a
     single syntax-error block; the other errors have dedicated lint checks. -->
<div>

    <!-- 1. PHP syntax error inside a php-if condition -->
    <p php-if="$user->">Broken condition</p>

    <!-- 2. Orphan php-else: an <hr> breaks the sibling chain with the php-if -->
    <p php-if="$ok">Yes</p>
    <hr>
    <p php-else>Orphan else</p>

    <!-- 3. Orphan p:elseif (short syntax): no preceding sibling with p:if -->
    <span>separator</span>
    <p p:elseif="$maybe">Orphan elseif</p>

    <!-- 4. php-with without a php-partial on the same element -->
    <section php-with="['title' => 'Home']">No partial here</section>

    <!-- 5. Unclosed expression: missing the closing "}}".
         Kept last: an unclosed "{{" pairs with the next "}}" in the file,
         so any later expression would absorb it into one broken span. -->
    <p>{{ $title }</p>

</div>
