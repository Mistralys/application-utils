# Tech Stack & Patterns

## Runtime & Language

| Item | Value |
|---|---|
| Language | PHP |
| Minimum Version | 7.4+ |
| Type System | `declare(strict_types=1)` in all source files |
| Namespace Root | `AppUtils` |

## Required PHP Extensions

`mbstring`, `curl`, `json`, `gd`, `zip`, `dom`, `simplexml`, `libxml`

## Dependencies (Runtime)

| Package | Version | Purpose |
|---|---|---|
| `mistralys/application-utils-core` | ≥ 2.2.3 | Core helper classes (FileHelper, ConvertHelper, StringBuilder, XMLHelper, etc.) |
| `mistralys/application-utils-image` | ≥ 1.1 | ImageHelper — GD-based image manipulation |
| `mistralys/application-utils-result-handling` | ≥ 1.0.1 | OperationResult and result collection classes |
| `parsecsv/php-parsecsv` | ≥ 1.3 | Low-level CSV parsing engine used by CSVHelper |

## Dependencies (Dev)

| Package | Version | Purpose |
|---|---|---|
| `phpunit/phpunit` | ≥ 9.6 | Unit testing |
| `phpstan/phpstan` | ≥ 1.10 | Static analysis |

## Optional / Suggested

| Package | Purpose |
|---|---|
| `mistralys/application-localization` | Translation support for localizable strings (date labels, file sizes, etc.). Enables the integrated translation UI. |

## Architectural Patterns

### Aggregation Package

This package is an **aggregation** of several separate libraries. The core utility classes (`FileHelper`, `ConvertHelper`, `StringBuilder`, `ClassHelper`, `URLInfo`, etc.) live in `mistralys/application-utils-core`. This package adds CSVHelper, IniHelper, Request, URLBuilder, PaginationHelper, ZIPHelper, LipsumHelper, and Value classes, and re-exports everything from its dependencies.

### Static Helper Pattern

Most utilities are **static helper classes** with no instantiation — you call `ClassName::methodName()` directly. Examples: `CSVHelper::parseFile()`, `LipsumHelper::containsLipsum()`.

Some classes (CSVHelper, IniHelper, PaginationHelper, ZIPHelper) use **factory methods** that return configured instances for fluent usage.

### Filter / Validator Strategy (Request)

The Request subsystem uses a **Strategy + Template Method** pattern:
- `Request_Param_Filter` (abstract) defines a `filter($value)` public method that delegates to the protected `_filter()` method in concrete subclasses.
- `Request_Param_Validator` (abstract) mirrors this with `validate($value)` → `_validate()`.
- Both implement the `Interface_Optionable` trait for configuration.

### Sticky Boolean Values

The Value subsystem implements a **one-way transition** pattern:
- `Value_Bool_True`: can only transition from `false` → `true` (irreversible).
- `Value_Bool_False`: can only transition from `true` → `false` (irreversible).

### Exception-Based Error Handling

All error conditions throw exceptions (subclasses of `AppUtils\BaseException`) instead of returning `false` or error codes. Each module has its own exception class with a set of integer error code constants.

## Autoloading

| Type | Configuration |
|---|---|
| Strategy | Classmap (`src/`) + file inclusion (`src/functions.php`) |
| Dev autoload | Classmap (`tests/TestClasses`) |
| Naming | PSR-0-style underscore-delimited class names mapped to directory paths within a PSR-4-like namespace root. E.g., `Request_Param_Filter_Boolean` → `Request/Param/Filter/Boolean.php`. |

## Build & QA Tools

| Tool | Command | Description |
|---|---|---|
| PHPUnit | `composer test` | Run test suite from `tests/AppUtilsTests/` |
| PHPStan | `composer analyze` | Static analysis at level 6 with 900 MB memory |
| PHPStan (save) | `composer analyze-save` | Saves output to `docs/phpstan/output.txt` |
| PHPUnit (save) | `composer test-save` | Saves output to `docs/phpunit/output.txt` |
| Batch runner | `run-tests.bat` | Windows batch file for running tests |

## License

GPL-3.0-or-later
