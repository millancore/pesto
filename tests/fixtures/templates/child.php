<div php-extends="layout.php">
    <div php-section="content">
        <h1>Welcome to Child Template</h1>
        <p>This is the content section that will be yielded in the parent layout.</p>

        <?php if($rawCondition) : ?>
            <p>This is a raw PHP conditional statement. also valid</p>
        <?php endif; ?>
    </div>
</div>
