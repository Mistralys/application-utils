# API Surface — Global Functions

> **File:** `src/functions.php`
> **Namespace:** `AppUtils`

These functions are auto-loaded via Composer's `files` directive and are available globally once the package is installed.

---

```php
/**
 * Creates a mutable boolean value object.
 */
function valBool(bool $initial = false): Value_Bool

/**
 * Creates a sticky-true boolean: starts false, irreversibly becomes true.
 */
function valBoolTrue(bool $initial = false): Value_Bool_True

/**
 * Creates a sticky-false boolean: starts true, irreversibly becomes false.
 */
function valBoolFalse(bool $initial = true): Value_Bool_False

/**
 * Whether the current request is running via the command line.
 */
function isCLI(): bool
```
