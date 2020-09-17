[![Build Status](https://travis-ci.com/Mistralys/application-utils.svg?branch=master)](https://travis-ci.com/Mistralys/application-utils) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Mistralys/application-utils/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Mistralys/application-utils/?branch=master)

# Application Utilities

Drop-in static PHP utility classes for common tasks, from file system and image manipulation to a pagination calculator.

## Installation

On the command line:

```
composer require mistralys/application-utils
```

Or via `composer.json`:

```json
"require": {
   "mistralys/application-utils": "dev-master"
}
```

### Optional packages

To enable localization of any translateable strings in the package, require `mistralys/application-localization` as well. German and french translations are included, and the integrated interface allows adding additional translations.

## Overview of helpers

This overview is an excerpt of the available utility classes in the package. It shows only the main functionality - classes like the ConvertHelper for example have a large number of static methods not shown here.

* BaseException: exception class with extended functionality.
* [ConvertHelper](https://github.com/Mistralys/application-utils/wiki/ConvertHelper): converting dates, strings, transliterating and more.
    - ByteConverter: Convert byte values to kilobytes, megabytes...
    - ControlCharacters: Detecting and stripping control characters from strings.
    - DateInterval: Wrapper around the native DateInterval to fix common pitfalls.
    - DurationConverter: Convert time durations to human readable formats.
    - EOL: Detect the newline style used in a string or file.
    - HiddenConverter: Converts hidden characters to readable for debugging.
    - IntervalConverter: Convert date intervals to human readable formats.
    - QueryParser: Parse query formatted strings without the native limitations.
    - SizeNotation: Convert size strings (2 MB) to byte values.
    - TabsNormalizer: Allows intelligently left-adjusting indented text.
    - [ThrowableInfo](https://github.com/Mistralys/application-utils/wiki/ThrowableInfo): OOP access for exception debugging, and persisting them to storage.
    - URLFinder: Allows detecting URLs in a string.
    - WordWrapper: Intelligently wrap text to a specific length.
* CSVHelper: read and generate CSV files.
* Highlighter: Syntax highlighting using GeSHi, as well as some custom variants.
* JSHelper: generate JavaScript statements from PHP with automatic PHP > JS conversion of variables.
* [FileHelper](https://github.com/Mistralys/application-utils/wiki/FileHelper): file system utility methods and file finder.
    - FileFinder: Fetching file lists by filters, recursive or not.
    - PHPClassInfo: Fetch basic class info from PHP files without reflection.
    - MimeTypes: List of commonly used mime types.
* ImageHelper: image manipulation using native GD functions with alpha support.
* [IniHelper](https://github.com/Mistralys/application-utils/wiki/IniHelper): INI file reader and editor.
* NumberInfo: access and modify numbers with or without units.
* OperationResult: Drop-in class to store the status of the result of any operation.
* PaginationHelper: calculates all required numbers for a pagination widget.
* RegexHelper: collection of typical validation Regexes.
* [Request](https://github.com/Mistralys/application-utils/wiki/Request): class to validate, filter and access request variables.
    - URLComparer: Compare URLs, including query parameters.
    - RequestHelper: build raw requests from scratch with multipart support.
    - AcceptHeaders: Allows parsing the Accept: header.
* StringBuilder: Easily concatenate strings, with formatting helpers, in a chainable interface.
* SVNHelper: working with a local SVN repository. Update, commit, etc.
* Traits: Plug-in traits for common tasks, with matching interfaces.
    - Classable: For elements that can have classes, like HTML elements.
    - Optionable: For elements which allow setting options, with strong typing.
* Transliteration: Simple transliteration to ASCII for any names. 
* [URLInfo](https://github.com/Mistralys/application-utils/wiki/URLInfo): An object oriented parse_url with fixes for a number of pitfalls.
    - Highlighter: Highlights URLs with integrated CSS.
    - Normalizer: Normalizes a URL, including query parameter sorting.
* Value_Bool: OO boolean values with optional true/false sticky values.
* VariableInfo: OO information on any PHP variable types with string conversion.
* XMLHelper: simplifies working with some of the XML libraries like DOM or SimpleXML.
    - HTMLLoader: Easy loading of HTML fragments or whole documents.
    - DOMErrors: LibXML parsing errors made easy with OO interface.
    - LibXML: Constants for all LibXML validation errors.
    - SimpleXML: Wrapper for SimpleXML with a number of utility methods.
    - Converter: Convert XML/HTML to arrays and json.
* ZIPHelper: abstracts working with the ZIPArchive class.

## Documentation

Documentation for the helper classes is ongoing in the [Application Utils Wiki](https://github.com/Mistralys/application-utils/wiki).

## Origin

Historically, these classes were integrated in several legacy applications, with their code diverging over time. This repository consolidated them all to make it easier to maintain them. 
