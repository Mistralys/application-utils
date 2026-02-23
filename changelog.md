# AppUtils Changelog

## v3.2.0 - GeShi Removal & MIT License
- Switched license to MIT ([#11](https://github.com/Mistralys/application-utils/issues/11)).
- Dependencies: Bumped up AppUtils Core to [v2.5.0](https://github.com/Mistralys/application-utils-core/releases/tag/2.5.0).

## v3.1.10 - URLBuilder fix
- URLBuilder: Fixed an internal error when rendering without setting any parameters.

## v3.1.9 - Request fix
- Request: Removed a stray echo statement that caused output before headers were sent.

## v3.1.8 - Request fix
- Request: Fixed the deprecated message when using the `htmlspecialchars` filter.
- Request: Fixed double-encoding of HTML entities in the `htmlspecialchars` filter.

## v3.1.7 - Pagination helper fixes
- PaginationHelper: Fixed incorrect next page value after using `setCurrentPage()`.
- PaginationHelper: Fixed uninitialized variable when calling `dump()` without calling `getPageNumbers()`.
- PaginationHelper: Added more information to the `dump()` method.
- PaginationHelper: Added the `getDump()` method to get the dump as an array.

## v3.1.6 - Request enhancements
- Request: Now sorting parameters in `buildURL()` to ensure consistent URLs.
- Request: Handling some edge cases of double slashes in URLs.
- URLBuilder: Added `keepOnly()` to remove all parameters except the ones specified.

## v3.1.5 - URLBuilder enhancements
- URLBuilder: Added overridable `init()` for any custom initializations.

## v3.1.4 - URLBuilder enhancements
- URLBuilder: Better support for extending the class.
- URLBuilder: Made the `createXXX()` methods work with extended classes.
- PHPStan: Moved the level to a config file for easier maintenance and better visibility.

## v3.1.3 - Added URLBuilder
- URLBuilder: Added the new helper class migrated from `mistralys/application_framework`.

## v3.1.2 - Pagination helper fix
- PaginationHelper: Fixed the current page returning `0` in some cases.

## v3.1.1 - Split off OperationResult
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

## v3.0.4 - Core update
- Core: Updated core to [v1.0.3](https://github.com/Mistralys/application-utils-core/releases/tag/1.0.3).

## v3.0.3 - Pagination helper update
- PaginationHelper: Added `getTotalItems()`.
- PaginationHelper: Added `getTotalPages()`.
- PaginationHelper: Added `getItemsPerPage()`.
- PaginationHelper: Added `factory()` method to make chaining easier.
- PaginationHelper: Added `setCurrentPage()`, so it is not mandatory to instantiate the class.
- Tests: Renamed the `testsuites` folder for correct namespacing.

## v3.0.2 - ImageHelper fix
- ImageHelper: Allow passing 0 to a `resample()` argument instead of `NULL`.

## v3.0.1 - ImageHelper SVG fix
- ImageHelper: Fixed SVG image size detection when no document size is present.

## v3.0.0 - Split core (deprecation)
- Code Base: Split core classes into a separate GIT repository.
- Dependencies: Added `mistralys/application-utils-core`.

## Structural changes

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

## Deprecation changes

- The `Interface_Stringable` has been renamed to `StringableInterface`.
- The `Interface_Optionable` has been renamed to `OptionableInterface`.
- The `Interface_Classable` has been renamed to `ClassableInterface`.
- The `ConvertHelper_ThrowableInfo` class has been renamed to `ThrowableInfo`.

For all of these interfaces and traits, the old versions are still
available to help with the migration. They will be removed in a future
release.

---

Older changelog entries can be found in the [Changelog History](/docs/changelog-history/).
