# Document Converter

A PHP wrapper around Libreoffice for converting documents from one format to another.

For example:

 * Microsoft Word to PDF
 * OpenOffice to Microsoft Word
 * PDF to HTML
 * ...and many more

## Installation

> IMPORTANT: You must have Libreoffice installed.
 
Using composer:

```bash
composer require lukaswhite/document-converter
``` 
 
## Usage

```php
use Lukaswhite\DocumentConverter\Converter;

$converter = new Converter('/path/to/document.doc');
$converter->toPDF();
```

All being well, this should create a file named `document.pdf` in the same folder.

To customize the filename:

```php
$converter->outputAs('converted')->toPDF();
```

...or the output path:

```php
$converter->outputTo('/path/to/converted/files')->toPDF();
```

You can of course combine these:

```php
$converter->outputAs('converted')
    ->outputTo('/path/to/converted/files')
    ->toPDF();
```

For other formats:

```php
$converter->toFormat('doc');
```

### Return Format

The conversion method returns an object that contains information about the conversion:

```php
$result = $converter->toPDF();

$result->getFilepath(); // e.g. /path/to/document.pdf
$result->getFilename(); // e.g. document.pdf
$result->getExtension(); // e.g. pdf
```