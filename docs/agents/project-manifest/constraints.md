# Constraints & Conventions

---

## Naming Conventions

### Class Names

Classes use **PSR-0-style underscore-delimited names** within the `AppUtils` namespace. The underscores map directly to the directory structure:

```
AppUtils\Request_Param_Filter_Boolean  →  src/Request/Param/Filter/Boolean.php
AppUtils\CSVHelper_Builder             →  src/CSVHelper/Builder.php
```

Newer classes (e.g., `IniHelper\INILine`, `URLBuilder\URLBuilder`) use proper PSR-4 sub-namespaces instead of underscores. Both styles coexist.

### Exception Classes

Every module has its own exception class named `<Module>_Exception` or `<Module>Exception`, always extending `AppUtils\BaseException` (from the core package). Error codes are defined as integer constants on the exception class or on the class that throws them.

### Factory Methods

- Static factory methods are named `create()`, `createFromXxx()`, or `factory()`.
- Instance-returning setters follow a fluent `set*(): self` pattern.
- Boolean checkers use `is*(): bool` or `has*(): bool`.

---

## Strict Typing

All source files use `declare(strict_types=1)`. Callers must pass correctly-typed values.

---

## Exception-Based Error Handling

The package **never** returns `false` or error sentinel values from public methods. All error conditions throw exceptions. This is a deliberate design choice documented in the README:

> *"It uses exception-based error handling, so there is no need for checking PHP function return values for `false`."*

Each error code constant is a unique integer, scoped to its module to avoid collisions.

---

## Autoloading Strategy

The package uses a **classmap** autoloader (not PSR-4 directory-based). Composer scans `src/` and builds a map of all classes. This accommodates the mixed PSR-0 (underscore) and PSR-4 naming styles.

The `src/functions.php` file is loaded via Composer's `files` directive so the global helper functions are always available.

---

## Aggregation Architecture

This package is an **aggregation layer**. The bulk of the utility classes (FileHelper, ConvertHelper, StringBuilder, URLInfo, XMLHelper, ClassHelper, ImageHelper, OperationResult, etc.) live in separate repositories:

| Package | Contains |
|---|---|
| `mistralys/application-utils-core` | FileHelper, ConvertHelper, StringBuilder, URLInfo, XMLHelper, ClassHelper, RegexHelper, Traits/Interfaces, and many more |
| `mistralys/application-utils-image` | ImageHelper, RGBAColor, HSVColor, ImageSize |
| `mistralys/application-utils-result-handling` | OperationResult, OperationResultCollection |

This package (`mistralys/application-utils`) adds: **CSVHelper**, **IniHelper**, **Request**, **URLBuilder**, **PaginationHelper**, **ZIPHelper**, **LipsumHelper**, and **Value** classes. It re-exports all dependencies so consumers only need to require this single package.

When modifying or extending the project, be aware that many commonly referenced classes (e.g., `FileHelper`, `ConvertHelper`) are **not in this repository** — they are in the core package under `vendor/mistralys/application-utils-core/`.

---

## Backward Compatibility Policy

The changelog shows a careful migration strategy:

1. **Deprecated classes/interfaces are kept** as aliases during a transition period (e.g., `Interface_Stringable` → `StringableInterface`).
2. **Modules split into separate repos** remain as Composer dependencies, maintaining full backward compatibility for existing consumers.
3. **Breaking changes** are reserved for major version bumps and are documented explicitly.

---

## Translation / Localization

Translatable strings exist in the package (e.g., date labels, file size units). Translation support is **optional** and requires installing `mistralys/application-localization` separately.

- Locale files live in `localization/` (`.ini` format, `de_DE` and `fr_FR` included).
- The `localization/index.php` file provides a web-based translation editor UI.
- The `StringBuilder` helper's translation methods (`t()`, `tex()`) depend on the localization package.

---

## Test Infrastructure

- **Base class:** `TestClasses\BaseTestCase` extends PHPUnit's `TestCase`. It clears `$_REQUEST`, `$_POST`, and `$_GET` superglobals in `setUp()` and `tearDown()` to ensure test isolation.
- **Request tests:** `AppUtilsTests\TestClasses\RequestTestCase` adds helpers for generating unique parameter names.
- **Web server tests:** Some Request tests require a running web server. The URL is configured in `tests/config.php` (copied from `config.dist.php`). Tests that need it call `skipWebserverURL()` to self-skip when unconfigured.
- **Test assets:** Fixture files live in `tests/assets/` (CSV samples, request helper data).

---

## PHPStan Configuration

- Analysis level: **6**
- Memory limit: **900 MB** (set via `composer analyze` script)
- Bootstrap: `tests/phpstan/constants.php` (defines test constants for analysis)
- Scanned paths: `src/` and `tests/`

---

## Gotchas

1. **Empty directories in `src/ConvertHelper/ThrowableInfo/`**: These are remnants from when ThrowableInfo was moved to the core package. They contain no code and can be ignored.
2. **SVNHelper**: Marked as deprecated in v2.2.0. It still exists in the core package but should not be used in new code.
3. **Request singleton**: `Request::getInstance()` is a true singleton. Tests must clear superglobals between tests (handled by `BaseTestCase`).
4. **Three separate `composer.json` files**: The root, `examples/`, and `localization/` directories each have their own `composer.json` and `vendor/` to isolate concerns.
5. **`keepOnly()` on URLBuilder**: Accepts variadic `string|string[]` parameters — you can pass individual names or arrays.
