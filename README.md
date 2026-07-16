 # Pesto
 
Modern PHP template engine that provides an intuitive and expressive way
to build web application views. It offers a clean syntax using custom HTML attributes
and supports advanced templating features like view composition, slots, conditional
rendering, loops, and built-in security measures.

Pesto understands the context of `{{ variables }}` 
and escapes them according to their scope to avoid
[Cross-Site Scripting](https://en.wikipedia.org/wiki/Cross-site_scripting) (XSS) issues.

```html
<ul>
    <li php-foreach="range(1, 10) as $number" php-if="$number > 7">Item {{ $number }}</li>
</ul>
```
Or, for greater clarity, use  `<template>`, which will not be included in the final render.

```html
<ul>
    <template php-foreach="range(1, 10) as $number">
       <li php-if="$number > 7">Item {{ $number }}</li>
    </template>
</ul>
```
Pesto templates support files with the `.html` or `.php`  extension,
allowing you to integrate PHP code if needed.

- [Installation & usage](#installation--usage)
- [View Composition](#view-composition)
  - [The `<template>` Tag](#template-tag)
  - [Partials](#partials--slots)
  - [Slots](#partials--slots) 
  - [Nested Views](#nested-views)
- [Control Flow](#control-flow)
  - [If Attribute](#if-attribute)
  - [Loop](#loops)
  - [Inline](#inline)
- [Short Syntax](#short-syntax)
- [Command Line](#command-line)
  - [Compile](#compile)
  - [Lint](#lint)
- [Filters](#filters)
  - [Chain Filters](#chain-filters)
  - [Filters with Arguments](#filters-with-arguments)
  - [Add Filters](#add-filters)
- [Benchmarks](#benchmarks)

## Installation & Usage

- PHP ^8.4

Pesto is available via Composer and is free of third-party dependencies

```shell
composer require millancore/pesto
```

```php
use MillanCore\Pesto\PestoFactory;

$pesto = PestoFactory::create([
    templatesPath: __DIR__ . '/views',
    cachePath: __DIR__ . '/cache',
    // [ New CustomFilters(), ... ]
]);

$pesto->make('view.php', ['user' => $user]);
```

## View Composition
Pesto makes it easy to reuse parts of your views

### Template Tag
The `<template>` tag allows you to define php-* attributes
that will be evaluated but not the tag included in the final render.

- `<p php-if="$user->isAdmin()">Admin</p>` --> `<p>Admin</p>`
- `<template php-if="$user->isAdmin()">Admin</template>` --> `Admin`


### Partials & Slots
When working with views that are composed of other views,
you can use partials and slots to avoid repetition.

Layout
```html
<!--- layouts/app.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <header>{{ $header | slot }}</header>

    <main>{{ $main | slot }}</main>
</body>
</html>
```
View: 
```html
<!--- views/home.php -->
<template php-partial="layouts/app.php" php-with="['title' => 'Home']">
    
    <!-- Named slot -->
    <nav php-slot="header">
        <a href="/">Home</a>
        <a href="/about">About</a>
    </nav>
    
    <!--Main Slot -->
    <section>
        <h1>Home</h1>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quae.</p>
    <section>
</template
```
### Nested Views
Pesto allows you to nest views, allowing you to reuse
the same layout for multiple times in the same view.

```html
  <template php-partial="list.php">
    <li>Item</li>
    <li>
        <ul php-partial="list.php">
            <li>nested item</li>
            .... 
        </ul>
    </li>
  </template>
```
## Control Flow
Pesto provides two control flow directives: `foreach` and `if`, enough to build any kind of view.

### If Attribute

The only rule importance for use `php-elseif` and `php-else` is the tag must be a sibling of the `php-if` tag.

- `php-if` 
- `php-elseif`
- `php-else`

`php-if` allows you to conditionally render a block of code.
```html
<p php-if="$user->isAdmin()">Admin</p>
<p php-elseif="$user->isModerator()">Moderator</p>
<p php-else>Guest</p>
```

### Loops
Pesto provides a simple way to loop over arrays or objects.

```html
<li php-foreach="$list as $item">{{ $item }}</li>
```

### Inline
Pesto also allows you to use inline control flow directives.

```html
<ul>
  <template php-foreach="$users as $user" php-if="$user->isAdmin()">
      <li>{{ $user->name | title }} - {{ $user->email }}</li>
  </template>
</ul>
```

## Short Syntax
Every `php-*` attribute has a shorter `p:*` alias, both forms work everywhere
and can be mixed in the same template.

| Long form     | Short form  |
|---------------|-------------|
| `php-if`      | `p:if`      |
| `php-elseif`  | `p:elseif`  |
| `php-else`    | `p:else`    |
| `php-foreach` | `p:foreach` |
| `php-partial` | `p:partial` |
| `php-with`    | `p:with`    |
| `php-slot`    | `p:slot`    |

```html
<ul p:if="count($items) > 0">
    <li p:foreach="$items as $item">{{ $item }}</li>
</ul>
<p p:else>No items</p>
```

If an element has both forms of the same directive, the long form wins.
Since no client-side framework claims the `p:` prefix, it is safe to combine
with Vue, Alpine.js, or Lit bindings.

## Command Line
Pesto ships with a `pesto` binary (installed at `vendor/bin/pesto`) to validate
and inspect templates without rendering them.

```shell
vendor/bin/pesto help
```

| Command                          | Description                                        |
|----------------------------------|----------------------------------------------------|
| `pesto compile <template_path>`  | Validate and compile a template, print the result  |
| `pesto -c <template_path>`       | Shorthand for `compile`                            |
| `pesto lint <path> [<path>...]`  | Validate template files or directories             |
| `pesto help`                     | Show the help message                              |

Both commands read the template from stdin when no path is given (or with `-`).

### Compile
Compiles a template and prints the resulting PHP, so you can see exactly
what Pesto generates:

```shell
echo '<li p:foreach="$items as $item" p:if="$item->visible">{{ $item->name | title }}</li>' | vendor/bin/pesto compile
```
```php
<?php foreach($items as $item): ?><?php if ($item->visible): ?><li><?= $__pesto->output($item->name, ['title', 'escape']) ?></li><?php endif; ?><?php endforeach; ?>
```

If the template is invalid, the errors are printed and the command exits with `1`.

### Lint
Validates templates without rendering them: it checks for unclosed `{{ }}`
expressions, orphan `php-else`/`php-elseif` directives, unprocessed directives,
and PHP syntax errors in the compiled output.

```shell
# Single files or directories (scanned recursively for .html and .php)
vendor/bin/pesto lint views/home.php
vendor/bin/pesto lint views/ emails/

# Or from stdin
echo '<p php-else>Guest</p>' | vendor/bin/pesto lint
```
```
 ✗ <stdin>
   - Orphan "php-else" directive on line 1: it must be an immediate sibling of a "php-if" element.
```

With `--views <dir>` the linter also verifies that every `php-partial`
reference exists in the templates root. Without explicit paths, it lints
the whole directory:

```shell
vendor/bin/pesto lint --views views/
```
```
 ✓ views/home.php
 ✓ views/layouts/app.php
 ✓ views/partials/nav.php

Linted 3 templates: no errors found.
```

The exit code is `0` when all templates pass and `1` otherwise, so `lint`
fits directly into a CI pipeline.

## Filters
Pesto provides a simple way to apply filters to variables using the pipe operator,
you can define your own filters.

```html
<p>{{ $text | upper }}</p>
```

### List of Filters
- `raw` Prevents escaping of the variable.

#### String Filters
- `upper` 
- `lower` 
- `capitalize`
- `title`
- `trim`
- `nl2br`
- `strip_tags`
- `slug`
- `join`


### Chain Filters
You can chain multiple filters together.
```html
<p>{{ $text | capitalize | truncate:50,... }}</p>
```

### Filters with Arguments
To pass arguments to a filter, you can use the `:` operator.

```html
<p>{{ $createAt | date:'m-d-Y' }}</p>
```

### Add Filters
Add filter to Pesto is very simple, you can create a class with public methods
and add the AsFilter Attribute.

```php
// CustomFilter.php
#[AsFilter(name: 'truncate')]
public function truncate(string $value, int $length, string $end = '...') : string
{ 
    //...
}
```
on Pesto factory pass the class to the `filters` option.

```php
$pesto = PestoFactory::create([
    templatesPath: __DIR__ . '/views',
    cachePath: __DIR__ . '/cache', [
        New CustomFilter(),
    ]
]);

```

## Benchmarks

Pesto includes benchmarks comparing performance against Blade and Twig across four scenarios: simple rendering, loops, conditionals, and partials.

Benchmarks are powered by [PHPBench](https://phpbench.readthedocs.io/) with 100 iterations, 10 revolutions, and 5 warmup iterations.

```bash
# Run all benchmarks (Pesto, Blade, Twig)
composer bench

# Run a single engine
composer bench:pesto
composer bench:blade
composer bench:twig

# Generate an interactive HTML chart (output: benchmarks/chart.html)
composer bench:chart
```



