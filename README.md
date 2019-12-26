[![Build Status](https://travis-ci.com/Mistralys/application-utils.svg?branch=master)](https://travis-ci.com/Mistralys/application-utils) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Mistralys/application-utils/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Mistralys/application-utils/?branch=master)

# Application Utilities

Drop-in static PHP utility classes for common tasks, from file system and image manipulation to a pagination calculator.

## Installation

Simply require the package via composer:

```json
"require": {
   "mistralys/application-utils": "dev-master"
}
```

## Overview

* BaseException: exception class with extended functionality.
* [ConvertHelper](https://github.com/Mistralys/application-utils/wiki/ConvertHelper): converting dates, strings, transliterating and more.
* CSVHelper: read and generate CSV files.
* JSHelper: generate JavaScript statements from PHP with automatic PHP > JS conversion of variables.
* [FileHelper](https://github.com/Mistralys/application-utils/wiki/FileHelper): file system utility methods and file finder.
* ImageHelper: image manipulation using native GD functions with alpha support.
* [IniHelper](https://github.com/Mistralys/application-utils/wiki/IniHelper): INI file reader and editor.
* NumberInfo: access and modify numbers with or without units.
* PaginationHelper: calculates all required numbers for a pagination widget.
* RegexHelper: collection of typical validation Regexes.
* [Request](https://github.com/Mistralys/application-utils/wiki/Request): class to validate, filter and access request variables.
* RequestHelper: build raw request headers from scratch.
* SVNHelper: working with a local SVN repository. Update, commit, etc.
* [ThrowableInfo](https://github.com/Mistralys/application-utils/wiki/ThrowableInfo): OOP access for error information, and persisting it to storage.
* [URLInfo](https://github.com/Mistralys/application-utils/wiki/URLInfo): An object oriented parse_url with fixes for a number of pitfalls.
* VariableInfo: OOP access for information on PHP variables.
* XMLHelper: simplifies working with some of the XML libraries like DOM or SimpleXML.
* ZIPHelper: abstracts working with the ZIPArchive class.

## Documentation

Documentation for the helper classes is ongoing in the [Application Utils Wiki](https://github.com/Mistralys/application-utils/wiki).

## Origin

Historically, these classes were integrated in several legacy applications, with their code diverging over time. This repository consolidated them all to make it easier to maintain them. 
