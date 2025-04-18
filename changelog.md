### v3.1.7 - Pagination helper fixes
- PaginationHelper: Fixed incorrect next page value after using `setCurrentPage()`.
- PaginationHelper: Fixed uninitialized variable when calling `dump()` without calling `getPageNumbers()`.
- PaginationHelper: Added more information to the `dump()` method.
- PaginationHelper: Added the `getDump()` method to get the dump as an array.

### v3.1.6 - Request enhancements
- Request: Now sorting parameters in `buildURL()` to ensure consistent URLs.
- Request: Handling some edge cases of double slashes in URLs.
- URLBuilder: Added `keepOnly()` to remove all parameters except the ones specified.

### v3.1.5 - URLBuilder enhancements
- URLBuilder: Added overridable `init()` for any custom initializations.

### v3.1.4 - URLBuilder enhancements
- URLBuilder: Better support for extending the class.
- URLBuilder: Made the `createXXX()` methods work with extended classes.
- PHPStan: Moved the level to a config file for easier maintenance and better visibility.

### v3.1.3 - Added URLBuilder
- URLBuilder: Added the new helper class migrated from `mistralys/application_framework`.

### v3.1.2 - Pagination helper fix
- PaginationHelper: Fixed the current page returning `0` in some cases.

### v3.1.1 - Split off OperationResult
- Core: The `OperationResult`-related classes have been moved to a separate repository.
- Dependencies: Added [AppUtils Result Handling](https://github.com/Mistralys/application-utils-result-handling).

> NOTE: Minor version bump, since the split has no
> functional impact on this package.

## v3.1.0 - Split off the ImageHelper
- ImageHelper: Split it off into a separate package, `mistralys/application-utils-image`.
- ImageHelper: Fully backwards compatible; the class is still available in this package.
- Dependencies: Added [AppUtils Image](https://github.com/Mistralys/application-utils-image).

## v3.0.5 - Operation result improvements
- OperationResult: Duplicate messages are now compressed into a single entry.
- OperationResult: Added `getCount()` to count how many times a message has been added.
- OperationResult: Added `getCodes()` to the collection.
- OperationResult: Added `getTypeLabel()`.
- OperationResult: Added the possibility to add an operation label. 
- OperationResult: Added global functions `operationResult()` and `operationCollection()`.
- Code: PHPStan analysis now clean up to level 6, including tests.

### v3.0.4 - Core update
- Core: Updated core to [v1.0.3](https://github.com/Mistralys/application-utils-core/releases/tag/1.0.3).

### v3.0.3 - Pagination helper update
- PaginationHelper: Added `getTotalItems()`.
- PaginationHelper: Added `getTotalPages()`.
- PaginationHelper: Added `getItemsPerPage()`.
- PaginationHelper: Added `factory()` method to make chaining easier.
- PaginationHelper: Added `setCurrentPage()`, so it is not mandatory to instantiate the class.
- Tests: Renamed the `testsuites` folder for correct namespacing.

### v3.0.2 - ImageHelper fix
- ImageHelper: Allow passing 0 to a `resample()` argument instead of `NULL`.

### v3.0.1 - ImageHelper SVG fix
- ImageHelper: Fixed SVG image size detection when no document size is present.

### v3.0.0 - Split core (deprecation)
- Code Base: Split core classes into a separate GIT repository.
- Dependencies: Added `mistralys/application-utils-core`.

### Structural changes

This release is the first in a larger project to make the package
more modular. The core classes have been split into a separate
repository, which is now a dependency of this package. 
**This is entirely backwards compatible.**

The aim is to make it possible to choose which AppUtil utilities 
to use, without unnecessarily splitting the code base. This package
will stay the main entry point with the current selection of 
utilities for the foreseeable future.

It is planned to split other utilities off (like the ImageHelper), 
but they will then become dependencies of this package to keep them
available. They will be installable separately, however.

### Deprecation changes

- The `Interface_Stringable` has been renamed to `StringableInterface`.
- The `Interface_Optionable` has been renamed to `OptionableInterface`.
- The `Interface_Classable` has been renamed to `ClassableInterface`.
- The `ConvertHelper_ThrowableInfo` class has been renamed to `ThrowableInfo`.

For all of these interfaces and traits, the old versions are still
available to help with the migration. They will be removed in a future
release.

### v2.5.0 - Microtime Time Zones support
- Microtime: Now detecting nanoseconds independently of formatting.
- Microtime: Added time zone recognition.
- Microtime: Added the `TimeZoneInfo` utility class.
- Microtime: Added `getTimezoneInfo()`.
- Microtime: Added `getMilliseconds()`.
- Microtime: Added `getNanoseconds()`.
- Microtime: Added `getNanoDate()`.
- Microtime: Nanoseconds are now persisted when cloning dates.
- Microtime: Time zone identifiers are no longer case sensitive.
- Microtime: `getISODate()` and `getNanoDate()` can optionally include the time zone.

#### Nanoseconds

Microtime will more reliably detect and adjust nanosecond
values in date strings, reducing them to microseconds.
The nanoseconds can still be accessed via `getNanoseconds()`.

#### Deprecated methods

- `Microtime::createFromMicrotime()`

### v2.4.1 - PHP8 compatibility release
- ImageHelper: Removed some PHP8 deprecation notices.

### v2.4.0 - Maintenance and RGBAColor update
- HTMLTag: Made the constructor public to allow extending the class.
- Classable: Removed `addClass()` param type hint for interface compatibility.
- RBGAColor: Fixed setting a color channel via Array access.
- RGBAColor: Added the non-immutable `applyXXX()` methods required for Array access.
- Core: Fixed PHP8 deprecation notices.

### v2.3.4 - FileHelper improvements
- PathInfo: `isWithinFolder()` now correctly handles `../` in paths.

### v2.3.3 - FileHelper improvements
- PathInfo: Added `getSize()` to get the size in bytes.
- PathInfo: Added `isWithinFolder()` to compare paths.

### v2.3.2 - Luma fix
- RGBAColor: Better Luma calculation, based on an actual Luma formula.
- RGBAColor: Added `getLumaPercent()`.
- RGBAColor: Added `isDark()` and `isLight()`.

### v2.3.1 - HSV bugfix
- HSVColor: Fixed a division by zero issue with some color values.

### v2.3.0 - HSVColor feature (Breaking)
- HSVColor: Added a new color utility class for color adjustments.
- ColorChannel: Added `getPercentRounded()`.
- ColorChannel: Added support for passing through existing instances in factory methods.
- Colors: Renamed `decimal` related methods to `alpha` as it was too confusing.
- Stringable Interface: Now using Symfony's PHP8 shim to make it `Stringable` compatible.
- ImageSize: Added `getRatio()` to get the size aspect ratio value.
- ImageSize: Added `toStringRedable()` utility method.
- ImageSize: Added resize methods, including the practical `resizeInto()`.
- ImageSize: Added the global function `imgSize()` to create an instance.

#### Breaking changes

- `RGBAColor::getBrightness()` now returns `BrightnessChannel`.
- Renamed `ColorChannel::getDecimal()` to `getAlpha()`.
- Renamed `ColorChannel::decimal()` to `alpha`.
- Renamed `UnitsConverter::float2IntSevenBit()` to `alpha2IntSevenBit()`.
- Renamed `UnitsConverter::float2IntEightBit()` to `alpha2IntEightBit()`.
- Renamed `UnitsConverter::percent2Float()` to `percent2Alpha()`.
- Renamed `UnitsConverter::float2percent()` to `alpha2percent()`.
- Renamed `UnitsConverter::intSevenBit2Float()` to `intSevenBit2Alpha()`.
- Renamed `UnitsConverter::intEightBit2IntSevenBit()` to `intSevenBit2Alpha()`.
- Renamed `UnitsConverter::intEightBit2Float()` to `intEightBit2Alpha()`.

### v2.2.11 - RGBAColor fix
- RGBAColor: Fixed `toHEX()` returning erroneous values ([#10](https://github.com/Mistralys/application-utils/issues/10)).

### v2.2.10 - URLInfo path fix
- URLInfo: Fixed spaces stripped from the path component of URLs.

### v2.2.9 - Minor enhancements
- URLInfo: URLs that cannot be parsed by `parse_url` no longer cause a PHP error.
- URLInfo: URLs with too many slashes, e.g. `https:///` are now fixed automatically.

### v2.2.8 - Microtime ISO8601 support
- Microtime: Added support for date strings in ISO8601 format.

### v2.2.7 - Bugfixes
- URLInfo: Fixed a PHP error when calling `getTypeLabel()` with the `none` type.

### v2.2.6 - Bugfixes
- ArrayDataCollection: Fixed `getBool()` not accepting a string `1` as true.
- FileHelper: Fixed `sendFileAuto()` not being static.
- AttributeCollection: Added `setKeepIfEmpty()` to keep select empty attributes.
- HTMLTag: Added the possibility to keep empty attributes in the rendered tag.
- Functions: Added `array_remove_values()`.

### v2.2.5 - Minor enhancements
- ArrayDataCollection: Added `createFromJSON()`.
- ImageHelper: Fixed the `trim()` method borking colors in some cases.

### v2.2.4 - Added the JSON converter
- ConvertHelper: Added `json2var()`, `json2array()` and `array2json()`.
- JSONConverter: Added the static class with all utility methods.
- JSONConverter: All JSON methods now throw `JSONConverterException` exceptions.
- Updated all relevant internal calls to use the JSON converter methods.
- ArrayDataCollection: Added date and time related methods.
  - `getMicrotime()`
  - `setMicrotime()`
  - `getDateTime()`
  - `setDateTime()`
  - `getTimestamp()`

#### Exception changes

Any methods that were documented to trigger a `JsonException` before
now exclusively trigger `JSONConverterException`, which is not compatible
with `JsonException`. Try/Catch blocks must be updated.

> Note: The original `JsonException` is still available via the previous exception.

### v2.2.3 - DataArrayCollection tweak
- DataArrayCollection: `getJSONArray` now works with array values.

### v2.2.2 - FileHelper bugfix
- FileInfo: Fixed `is_file()` wrongly detecting existing folders as files in some cases.
- DataArrayCollection: Added `remove()`.
- DataArrayCollection: Added `keyExists()` and `keyHasValue()`.
- DataArrayCollection: An existing collection can now be used in `create()`, as pass-through value.

### v2.2.1 - Data array collection
- Collections: Added the `ArrayDataCollection` helper class.

### v2.2.0 - ImageHelper and PHP8 compatibility
- ImageHelper: Improved the `trim()` method.
- ImageHelper: Prepared for PHP8's GD library changes.
- ImageHelper: Renamed `opacity` to  `alpha` to keep the terminology consistent.
- ImageHelper: Fixed some alpha channel-related issues.
- ImageHelper: Added some unit tests for methods like `trim()`.
- RequestHelper: Made `RequestHelper::createCURL()` public.
- RequestHelper: Fixed PHP8 compatibility when initializing CURL.
- FileHelper: Fixed `removeExtension` throwing an exception with extensionless paths.
- Code: All unit tests running on PHP8.
- FileHelper: Added `IndeterminatePath` for paths that can be either a file or a folder.
- FileHelper: `FileInfo` and `FolderInfo` can now work with paths that do not exist.

#### Breaking changes

##### ImageHelper

Renamed all `opacity` methods to `alpha`.

##### FileHelper

Using `getPathInfo()` on paths like `/path/to/folder` when the target 
file or folder do not exist, will not return a folder instance anymore, but an
`IndeterminatePath`. This is because the target can be either a file or a folder. 

To avoid confusion when working with folders, use `/path/to/folder/` instead. 
Alternatively, use `getFolderInfo()` or `getFileInfo()` to specify the expected
type.

#### Deprecated features

- SVNHelper: Marked as deprecated, will be removed in a future release.

### v2.1.3 - URLInfo maintenance release
- URLInfo: Fixed parsing localhost URLs, with or without scheme.
- URLInfo: Improved the parsing of some edge cases.
- URLInfo: Namespaced and renamed internally used classes.
- URLInfo: All exceptions are now instances of `URLException`.
- URLInfo: Added custom schemes via `URISchemes`.
- URLInfo: Added custom host names via `URLHosts`.

### v2.1.2 - Maintenance release
- ClassHelper: Better handling of exceptions passed to `requireObjectInstanceOf()`.

### v2.1.1 - Maintenance release
- PHPClassInfo: Added support for detecting interfaces.
- PHPClassInfo: Added `isInterface()` and `isClass()` to individual results.
- FileInfo: Added `getBaseName()` to get the file name without extension.
- ClassHelper: Now throwing exceptions passed to `requireObjectInstanceOf`.
- RequestHelper: Added the `AcceptHeader` class for individual headers.

### v2.1.0 - Class helper release
- ClassHelper: Added the new `ClassHelper` static class to help with class loading.
- StyleCollection: Fixed mandatory `$important` parameter on `styleREM()`.

### v2.0.1 - FileInfo maintenance update
- FileHelper: Modified file and folder related methods to accept `SplFileInfo` instances.
- FileHelper: Added `PHPFile`, which extends `FileInfo`.
- FileHelper: Deprecated `checkPHPFileSyntax()`. Use `PHPFile::checkSyntax()` instead.
- FileHelper: `JSONFile` now extends `FileInfo`.
- FileHelper: `SerializedFile` now extends `FileInfo`.
- FileHelper: The class has been converted to strict typing.
- FileHelper: Fixed `FileInfo` throwing an exception when cast to string on not existing files.
- Microtime: Added all format character constants in the new class `DateFormatChars`.
- ConvertHelper: Added case insensitivity to `string2bool()`.
- ConvertHelper: Added `string2words()`, using the new `WordSplitter` class.
- LipsumHelper: Added the helper, with a lipsum dummy text detection tool ([#7](https://github.com/Mistralys/application-utils/issues/7)).

### v2.0.0 - PHP7.4 and code quality update
- FileHelper: Removed the deprecated `openUnserialized()` method.
- StringBuilder: Added `bool()`.
- StringBuilder: Added `setSeparator()` to customize the separator character.
- FileFinder: Added the `$enabled` parameter to `makeRecursive()`.
- FileHelper: Added the `FileInfo` and `FolderInfo` classes.
- FileHelper: Modernized the code, and split into subclasses.
- FileHelper: Added `FolderInfo::getIterator()`.
- Traits: Added the `StylableTrait` trait and matching interface.
- Traits: Added the `AttributableTrait` trait and matching interface.
- Traits: Added the `ClassableAttribute` trait and matching interface.
- Classable: Removed type hints on `addClass()` for HTML_QuickForm compatibility.
- Examples: Added a separate composer install for the examples UI.
- RGBAColor: Refactored entirely with color channels.
- StyleCollection: Added the utility class `StyleCollection` to handle CSS styles.
- HTMLTag: Added the new tag builder utility class.
- Examples: Added a separate `composer.json` to solve composer dependency issues.
- UnitTests: Tests are now included in the PHPStan analysis.
- UnitTests: Started modernizing tests with namespaces and type hinting.
- Code: PHPStan Analysis is now clean up to level 5.
- Code: Generalized type hinting update for PHP 7.4 (ongoing).
- BaseException: The previous parameter is now documented as accepting `Throwable`.
- CSVHelper: Upgraded library to v1.3.

#### Breaking changes
 
- Minimum PHP version requirement is now v7.4.
- Deprecated `FileHelper::findFiles()`.
- Removed `$exit` parameter of `Request::sendJSON()`. Use `sendJSONAndExit()` instead.
- Removed `$exit` parameter of `Request::sendHTML()`. Use `sendHTMLAndExit()` instead.
- Removed deprecated methods:
  - `FileHelper::createCSVParser()`.
  - `FileHelper::parseCSVString()`.
  - `FileHelper::parseCSVFile()`.
  - `FileHelper::getUTFBOMs()`.
  - `FileHelper::detectUTFBom()`.
  - `FileHelper::isValidUnicodeEncoding()`.

#### Scrutinizer changes

Removed the `application-localization` installation from the configuration,
as there currently seems to be no easy fix to make it possible.

Removed this part:

```
    dependencies:
        override:
            - COMPOSER_ROOT_VERSION=dev-master composer require mistralys/application-localization:1.4.1
```

### v1.9.0 - Feature release
- HTMLTag: Added the utility class `HTMLTag` to render HTML tags.
- AttributeCollection: Added the utility class `AttributeCollection` to handle HTML tag attributes.
- StringBuilder: Added support for using callables in the `ifXXX()` methods.
- StringBuilder: Added `linkOpen()` and `linkClose()`.
- Classable: Added the `hasClasses()` method to the interface and trait.
- Code quality: PHPStan recommendations and improvements throughout.
- PHPClassInfo: Fixed the class needlessly requiring the parsed files.
- PHPClassInfo: Added support for traits.
- PHPClassInfo: Fixed false positives found in comments.

### v1.8.1 - Bugfix & minor feature release.
- ThrowableInfo: Fixed exception on unserializing a PDO exception.
- ThrowableInfo: Added support for string-based codes.
- ThrowableInfo: Added serialized data validation.
- ThrowableInfo: Added Microtime support.
- ThrowableInfo: Split some parts into subclasses for maintainability.
- ThrowableInfo: Fixed accessing and persisting exception class name and details.
- Microtime: Added `createNow()`.
- Microtime: Added `createFromDate()`.
- Microtime: Added `createFromMicrotime()`.
- Microtime: Added `createFromString()`.
- Microtime: Added a specific exception class, `Microtime_Exception`.
- Microtime: Added getter methods for the relevant parts (year, month, date...)
- StringBuilder: Added `ifTrue()`, `ifFalse()`, `ifEmpty()`, `ifNotEmpty()`.
- DateInterval: Fixed an issue with negative hour values.

### v1.8.0 - Minor feature release.
- RGBAColor: Added the new color information handling tool.
- ThrowableInfo: Added `getFinalCall()` for information on the most recent call.
- ThrowableInfo: Added `hasDetails()` and `getDetails()`.
- ThrowableInfo: Added `renderErrorMessage()` for a human-readable error message.
- OperationResult: Added `makeException()` to use an exception as error message.
- URLInfo: Added filtering of URL-Encoded newlines and tabs.

### v1.7.1 - Maintenance release.
- NamedClosure: Added array support for the `$origin` parameter.
- Internals: Added type hints to improve static code analysis.

### v1.7.0 - Minor feature release.
- NumberInfo: Added the immutable variant with `parseNumberImmutable()`.
- NumberInfo: Added `ceilEven()` and `floorEven()`.
- NumberInfo: Modernized the class, now using strict typing.
- NumberInfo: Fixed some minor consistency issues with empty numbers.
- NumberInfo: Fixed the value not being updated when using `setNumber()`.
- NumberInfo: Fixed the value having units when the raw value did not have any.

### v1.6.0 - Dependency update release.
- OutputBuffering: Added the `OutputBuffering` helper class for simplifying output buffers.
- StringBuilder: Added `tex()` for translation with context information for translators.
- Dependencies: Translation now requires `mistralys/application-localization` v1.4.0.
- Composer: Added `ext-gd` to the list of requirements for the `ImageHelper`.

### v1.5.1 - Minor feature release.
- ConvertHelper: Added `boolStrict2string()`, a variant of `string2bool` that only accepts boolean values.
- DateTime: Added the `Microtime` class that extends `DateTime` to add millisecond and microsecond capability.
- Interfaces: Added the interface `Interface_Stringable` for objects that can be converted to string.

### v1.5.0 - Feature release. 
- HTMLHelper: Added the new helper.
- HTMLHelper: Added `stripComments()`.
- HTMLHelper: Added `injectAtEnd()`.
- NumberInfo: Added `hasDecimals()`.
- URLInfo: Added host IP address detection.
- URLInfo: Added the `hasIPAddress()` method to check if the host is an IP address.
- URLInfo: Fixed case sensitivity issues in scheme and hostname.
- URLInfo: Added a fallback scheme detection if `parse_url` did not detect any.
- URLFinder: Parser moved from a regex to a more reliable detection system.
- URLFinder: Added detection of relative paths in HTML documents.
- URLFinder: Added IPv4 address detection, with or without scheme.
- URLFinder: Added `tel:` link detection.
- URLFinder: Added a top level domain extensions lookup helper under `ConvertHelper_URLFinder_DomainExtensions`.
- RegexHelper: Added the `REGEX_IPV4` regex.
- Documentation: Added wiki pages for additional helpers.

Older changelog entries can be found on GitHub:
https://github.com/Mistralys/application-utils/releases