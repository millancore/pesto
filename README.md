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
    <li php-foreach="range(1, 10) as $number" php-if="$number > 4">Item {{ $number }}</li>
</ul>
```
Or, for greater clarity, use  `<template>`, which will not be included in the final render.

```html
<ul
    <template php-foreach="range(1, 10) as $number">
       <li php-if="$number > 4">Item {{ $number }}</li>
    </template>
</ul>
```
## Installation
#### Requirements
 - PHP **^8.4**

Pesto is dependency-free, making it versatile enough to be included in any project.

```shell
composer require millanco/pesto
```

## Features

Pesto templates use files with the `.php` extension,
allowing you to seamlessly integrate PHP code

- View Composition
  - Slots
  - Named Slots
- Attributes
  - If Attribute
  - Loops
  - Partials
- Filters
- Security
