# WEBDEVPACK SDK for PHP

WEBDEVPACK SDK for PHP provides a simple, expressive way to integrate WEBDEVPACK's tools into your PHP applications. Easily access web-development utilities, automate workflows, and build faster with a unified PHP interface.

## Install via Composer

```shell
composer require webdevpack/sdk-php
```

## Examples

```php
$wdp = new WebDevPack\Client(['apiKey' => 'YOUR-API-KEY']);

// Optimize images
$wdp->optimizeImage($sourceFilename, $targetFilename,80);

// Minify JavaScript
$wdp->minifyJavaScriptFile($sourceFilename, $targetFilename);

// Get text from image (OCR)
$result = $wdp->getTextFromImage($sourceFilename, 'eng');
```

## Documentation

Full [documentation](https://github.com/webdevpack/sdk-php/blob/master/docs/markdown/index.md) is available as part of this repository.

## License
This project is licensed under the MIT License. See the [license file](https://github.com/webdevpack/sdk-php/blob/master/LICENSE) for more information.
