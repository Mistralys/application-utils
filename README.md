# Application Utilities

Drop-in static PHP utility classes for common tasks, from file system related
operations and variable conversions to image manipulation.

## Composition

This package is an aggregation of several separate libraries upon which the 
application utilities are built. Depending on the features you need, you may
not need to install this package in its entirety. 

The following libraries are included in this package:

- [AppUtils Core][]: The minimum core classes used by all libraries.
- [AppUtils Image][]: Image manipulation classes using the GD library.
- [AppUtils Collections][]: Enum and item collection classes.
- [AppUtils Result Handling][]: Classes for collecting operation results.
- [AppLocalize][]: (Optional) Localization library and translation UI.

## Requirements

- PHP v7.4+
- Extensions: 
  - MBString
  - CURL
  - Json
  - GD
  - Zip
  - Dom
  - SimpleXML

## Installation

Simply require the package with Composer:

```
composer require mistralys/application-utils
```

Also see the [Packagist][] page.

### Translation support

To enable localization of any translatable strings in the package, 
require the [Application Localization][] package as well. German and 
French translations are included, and the integrated interface 
allows adding additional translations.

Other translation solutions are not supported.

```
composer require mistralys/application-localization
```

> Note: The `StringBuilder` helper's translation methods depend
> on the package being installed.

## Quickstart

The package is meant to simplify day to day development tasks, from
common file system related access to converting data types. It uses
exception-based error handling, so there is no need for checking 
PHP function return values for `false`.

There is no central factory for accessing the helpers. Instead, each
static helper class has its own factory methods and acts as hub for 
all related tasks.

For example, all file related tasks live in the `FileHelper` class.

```php
use AppUtils\FileHelper;

// Replacement for file_get_contents
$content = FileHelper::readContents('path/to/file');
```

Likewise, conversion methods live in the `ConvertHelper` class.

```php
use AppUtils\ConvertHelper;

$bool = ConvertHelper::string2bool('yes');
```

The helpers overview below can give you an idea of what is available.

## Overview of helpers

This overview is an excerpt of the available utility classes in the 
package. It shows only the main functionality - classes like the 
ConvertHelper for example have many static methods not shown here.

* [BaseException][]: Exception class with extended functionality.
* [BoolValue][]: Object oriented boolean values with one-way "sticky" values.
* [ConvertHelper][]: Converting dates, strings, transliterating and more.
    - ByteConverter: Convert byte values to kilobytes, megabytes...
    - ControlCharacters: Detecting and stripping control characters from strings.
    - DateInterval: Wrapper around the native DateInterval to fix common pitfalls.
    - DurationConverter: Convert time durations to human-readable formats.
    - EOL: Detect the newline style used in a string or file.
    - HiddenConverter: Converts hidden characters to readable for debugging.
    - IntervalConverter: Convert date intervals to human-readable formats.
    - QueryParser: Parse query formatted strings without the native limitations.
    - SizeNotation: Convert size strings (2 MB) to byte values.
    - TabsNormalizer: Allows intelligently left-adjusting indented text.
    - [ThrowableInfo][]: Exception analysis tool for debugging, and persisting them to storage.
    - [URLFinder][]: Allows detecting URLs in a string.
    - WordWrapper: Intelligently wrap text to a specific length.
* [ClassHelper][]: Static analysis-friendly class loading methods and Composer `ClassLoader` access. 
* CSVHelper: Read and generate CSV files.
* Highlighter: Syntax highlighting using GeSHi, as well as some custom variants.
* JSHelper: generate JavaScript statements from PHP with automatic PHP > JS conversion of variables.
* [FileHelper][]: File system utility methods and file finder.
    - [FileFinder][]: Fetching file lists by filters, recursive or not.
    - PHPClassInfo: Fetch basic class info from PHP files without reflection.
    - MimeTypes: List of commonly used mime types.
    - [PathsReducer][]: Reduce a list of absolute paths to a common root.
* HTMLHelper: Common HTML utility methods.
    - [HTMLTag][]: Object-Oriented HTML tag generation.
    - AttributeCollection: Collection class for attributes (HTML or other).
* ImageHelper: Image manipulation using native GD functions with alpha support.
    - [RGBAColor][]: Color container class with alpha channel support. 
* [IniHelper][]: INI file reader and editor.
* [Microtime][]: `DateTime` class with microseconds support and friendly methods interface.
* [NamedClosure][]: Wrapper for closures with the possibility to add a description.  
* [NumberInfo][]: Access and modify numbers with or without units.
* [OperationResult][]: Drop-in class to store the status of the result of any operation.
* [OutputBuffering][]: Output buffering wrapper with exception error management.
* PaginationHelper: Calculates all required numbers for a pagination widget.
* RegexHelper: Collection of typical validation Regexes.
* [Request][]: Validate, filter and access request variables.
    - URLComparer: Compare URLs, including query parameters.
    - RequestHelper: Build raw requests from scratch with multipart support.
    - AcceptHeaders: Allows parsing the `Accept:` header.
* [StringBuilder][]: Easily concatenate strings, with formatting helpers, in a chainable interface.
* SVNHelper: working with a local SVN repository. Update, commit, etc.
* Traits and Interfaces: Plug-in traits for common tasks, with matching interfaces.
    - [Classable][]: For elements that can have classes, like HTML elements.
    - [Optionable][]: For elements which allow setting options, with strong typing.
    - Stringable: For elements with the `__toString()` method.
* Transliteration: Simple transliteration to ASCII for any names. 
* [URLInfo][]: An object-oriented parse_url with fixes for a number of pitfalls.
    - Highlighter: Highlights URLs with integrated CSS.
    - Normalizer: Normalizes a URL, including query parameter sorting.
* [VariableInfo][]: Information on any PHP variable types with string conversion.
* XMLHelper: Simplifies working with some XML libraries like DOM or SimpleXML.
    - HTMLLoader: Easy loading of HTML fragments or whole documents.
    - DOMErrors: LibXML parsing errors made easy with OO interface.
    - LibXML: Constants for all LibXML validation errors.
    - SimpleXML: Wrapper for SimpleXML with a number of utility methods.
    - Converter: Convert XML/HTML to arrays and json.
* ZIPHelper: Abstracts working with the ZIPArchive class.

## Documentation

Documentation for the helper classes is ongoing in the [Application Utils Wiki][].

## Origin

These classes are still in use in a number of legacy applications. They were originally 
scattered over all of these applications, with their code diverging over time. This 
repository aims to consolidate them all into a single package to make it easier to maintain 
them.

As the legacy applications are still being maintained, this package is actively maintained
and being modernized.


[Packagist]: https://packagist.org/packages/mistralys/application-utils
[Application Localization]: https://github.com/Mistralys/application-localization
[Application Utils Wiki]: https://github.com/Mistralys/application-utils/wiki
[BaseException]: https://github.com/Mistralys/application-utils/wiki/BaseException
[BoolValue]: https://github.com/Mistralys/application-utils/wiki/BoolValue
[StringBuilder]: https://github.com/Mistralys/application-utils/wiki/StringBuilder
[FileFinder]: https://github.com/Mistralys/application-utils/wiki/FileFinder
[NamedClosure]: https://github.com/Mistralys/application-utils/wiki/NamedClosure
[ConvertHelper]: https://github.com/Mistralys/application-utils/wiki/ConvertHelper
[ThrowableInfo]: https://github.com/Mistralys/application-utils/wiki/ThrowableInfo
[IniHelper]: https://github.com/Mistralys/application-utils/wiki/IniHelper
[URLInfo]: https://github.com/Mistralys/application-utils/wiki/URLInfo
[Request]: https://github.com/Mistralys/application-utils/wiki/Request
[FileHelper]: https://github.com/Mistralys/application-utils/wiki/FileHelper
[PathsReducer]: https://github.com/Mistralys/application-utils/wiki/PathsReducer
[Classable]: https://github.com/Mistralys/application-utils/wiki/Classable
[Optionable]: https://github.com/Mistralys/application-utils/wiki/Optionable
[NumberInfo]: https://github.com/Mistralys/application-utils/wiki/NumberInfo
[OperationResult]: https://github.com/Mistralys/application-utils/wiki/OperationResult
[VariableInfo]: https://github.com/Mistralys/application-utils/wiki/VariableInfo
[URLFinder]: https://github.com/Mistralys/application-utils/wiki/URLFinder
[OutputBuffering]: https://github.com/Mistralys/application-utils/wiki/OutputBuffering
[Microtime]: https://github.com/Mistralys/application-utils/wiki/Microtime
[HTMLTag]: https://github.com/Mistralys/application-utils/wiki/HTMLTag
[RGBAColor]: https://github.com/Mistralys/application-utils/wiki/RGBAColor
[ClassHelper]: https://github.com/Mistralys/application-utils/wiki/ClassHelper

[AppUtils Core]: https://github.com/Mistralys/application-utils-core
[AppUtils Image]: https://github.com/Mistralys/application-utils-image
[AppUtils Collections]: https://github.com/Mistralys/application-utils-collections
[AppUtils Result Handling]: https://github.com/Mistralys/application-utils-result-handling
[AppLocalize]: https://github.com/Mistralys/application-localization
