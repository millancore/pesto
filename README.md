 # Pesto

```shell
composer require millancore/pesto
```

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
<ul
    <template php-foreach="range(1, 10) as $number">
       <li php-if="$number > 7">Item {{ $number }}</li>
    </template>
</ul>
```
Result:
```html
<ul>
    <li>Item 8</li>
    <li>Item 9</li>
    <li>Item 10</li>
</ul>
```
## Installation & Usage

- PHP ^8.4

Pesto is available via Composer: `composer require millancore/pesto` and is free of third-party dependencies

```php
use MillanCore\Pesto\PestoFactory;

$pesto = PestoFactory::create([
    templatesPath: __DIR__ . '/views',
    cachePath: __DIR__ . '/cache',
]);

$pesto->render('view.php', ['user' => $user]);
```

## Features

Pesto templates use files with the `.php` extension,
allowing you to seamlessly integrate PHP code

- [View Composition](#view-composition)
  - [The `<template>` Tag](#template-tag)
  - [Partials](#partials--slots)
  - [Slots](#partials--slots) 
  - [Nested Views](#nested-views)
- [Control Flow](#control-flow)
  - [If Attribute](#if-attribute)
  - [Loop](#loops)
  - [Inline](#inline)
- [Filters](#filters)
  - [Chain Filters](#chain-filters)
  - [Filters with Arguments](#filters-with-arguments)

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
    <header>{{ $header }}</header>

    <main>{{ $slot }}</main>
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
<ul php-foreach="$list as $item">
    <li>{{ $item }}</li>
</ul>
```

### Inline
Pesto also allows you to use inline control flow directives.

```html
<ul php-foreach="$users as $user" php-if="$user->isAdmin()">>
    <li>{{ $user->name | ucfirst }}</li>
    <li>{{ $user->email }}</li>   
</ul>
```

## Filters
Pesto provides a simple way to apply filters to variables using the pipe operator,
you can define your own filters or use the php built-in functions.

```html
<p>{{ $text | strtoupper }}</p>
```

### Chain Filters
You can chain multiple filters together.
```html
<p>{{ $text | ucfirst | truncate:50,... }}</p>
```

### Filters with Arguments
To pass arguments to a filter, you can use the `:` operator.

```html
<p>{{ $createAt | date:m-d-Y }}</p>
```

