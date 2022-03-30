### v2.0.0 - PHP7.4 and code quality update
- FileHelper: Removed the deprecated `openUnserialized()` method.
- StringBuilder: Added `bool()`.
- StringBuilder: Added `setSeparator()` to customize the separator character.
- FileFinder: Added the `$enabled` parameter to `makeRecursive()`.
- FileHelper: Added the `FileInfo` and `FolderInfo` classes.
- FileHelper: Modernized the code, and split into subclasses.
- Traits: Added the `StylableTrait` trait and matching interface.
- Traits: Added the `AttributableTrait` trait and matching interface.
- Traits: Added the `ClassableAttribute` trait and matching interface.
- Classable: Removed type hints on `addClass()` for HTML_QuickForm compatibility.
- UnitTests: Tests are now included in the PHPStan analysis.
- Examples: Added a separate composer install for the examples UI.
- RGBAColor: Added the hexadecimal color channel.
- PHPStan: Analysis is now clean up to level 5.
- StyleCollection: Added the utility class `StyleCollection` to handle CSS styles.

#### Breaking changes
- 
- Minimum PHP version requirement is now v7.4.

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