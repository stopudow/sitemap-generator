# Sitemap Generator Library

## Overview

__Sitemap Generator__ is a PHP library that allows you to generate sitemap content in various file formats such as XML, JSON, or CSV. This library is designed to be lightweight and does not have any external dependencies. It follows the standard set by [www.sitemaps.org](https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd).

## Installation

You can install SitemapGenerator via [Packagist](https://packagist.org/). Simply include the library in your project to get started.

``` shell
composer require stopudow/sitemap-generator
```

Alternatively, you can download the `SitemapGenerator.php` file from the `source` folder and include it in your PHP project.

``` php
require_once('path/to/SimpleGenerator.php');
```

## Usage

To generate a sitemap content, use the `generate` method provided by the `SitemapGenerator` class. Here is an example of how to use it:

``` php
$pagesArray = [
    [
        'loc' => 'https://site.com/',
        'lastmod' => '2024-06-27',
        'priority' => '1.0',
        'changefreq' => 'hourly',
    ],
    [
        'loc' => 'https://site.com/news',
        'lastmod' => '2024-06-27',
        'priority' => '0.5',
        'changefreq' => 'daily',
    ],
    [
        'loc' => 'https://site.com/about',
        'lastmod' => '2024-06-20',
        'priority' => '0.1',
        'changefreq' => 'weekly',
    ],
];

SitemapGenerator::generate($pagesArray, 'xml', '/var/www/site.ru/upload/sitemap.xml');
```

Ensure that the `$pagesArray`, `$fileType`, and `$filePath` parameters are correctly set before calling the `generate` method to avoid any exceptions.

## Note

Handle any exceptions that may be thrown during the generation process to ensure the successful creation of the sitemap content.
