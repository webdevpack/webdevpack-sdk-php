# WEBDEVPACK SDK for PHP

WEBDEVPACK SDK for PHP provides a simple, expressive way to integrate WEBDEVPACK's tools into your PHP applications. Easily access web-development utilities, automate workflows, and build faster with a unified PHP interface.

## Install via Composer

```shell
composer require webdevpack/webdevpack-sdk-php
```

## Examples

```php
require __DIR__ . '/vendor/autoload.php';

$wdp = new WebDevPack\Client(['apiKey' => 'YOUR-API-KEY']);

// IMAGES

// Optimize images
$wdp->optimizeImage($sourceFilename, $targetFilename, 80);

// Convert images
$wdp->convertImage($sourceFilename, $targetFilename, 'webp', 80);

// Get text from image (OCR)
$result = $wdp->getTextFromImage($sourceFilename, 'eng');

// Generate QR Code
$wdp->generateQRCode($text, $targetFilename, 500, 'webp');

// Generate barcode
$wdp->generateBarcode($text, $targetFilename, 500, 300, 'webp');

// CODE

// Minify JavaScript code
$result = $wdp->minifyJavaScript($source);

// Minify JavaScript file
$wdp->minifyJavaScriptFile($sourceFilename, $targetFilename);

// Minify CSS code
$result = $wdp->minifyCSS($source);

// Minify CSS file
$wdp->minifyCSSFile($sourceFilename, $targetFilename);

// WEBSITES

// Get domain WHOIS information
$result = $wdp->domainWhois($domain);

// SECURITY

// Generate password
$result = $wdp->generatePassword($length, true, true, true);

// Generate key pair
$result = $wdp->generateKeyPair($bits);

// DOCUMENTS

// Convert HTML to PDF
$wdp->convertHTMLToPDF($source, $targetFilename);

// Convert HTML file to PDF
$wdp->convertHTMLFileToPDF($sourceFilename, $targetFilename);
```

## Requirements
- PHP 8.4+
- Composer

## License
This project is licensed under the MIT License. See the [license file](https://github.com/webdevpack/webdevpack-sdk-php/blob/master/LICENSE) for more information.
