# API Surface — Request

> **Module:** HTTP request parameter access, validation, filtering, and URL building.
> **Namespace:** `AppUtils`
> **Files:** `src/Request.php` + 22 files under `src/Request/`

---

## `Request`

**File:** `src/Request.php`

Singleton for accessing, filtering, and validating HTTP request parameters. Also provides URL building and static helpers for sending JSON/HTML responses.

### Constants

| Constant | Type | Value |
|---|---|---|
| `ERROR_MISSING_OR_INVALID_PARAMETER` | int | `97001` |
| `ERROR_PARAM_NOT_REGISTERED` | int | `97002` |

### Methods

```php
public function __construct()
public static function getInstance(): self

// Parameter access
public function getParam(string $name, string|array|int|float|bool|null $default = null): mixed
public function getParams(): array
public function hasParam(string $name): bool
public function setParam(string $name, mixed $value): Request
public function removeParam(string $name): Request
public function removeParams(array $names): Request
public function getBool(string $name, bool $default = false): bool

// Registered parameters (with validation/filtering)
public function registerParam(string $name): RequestParam
public function getRegisteredParam(string $name): RequestParam
public function hasRegisteredParam(string $name): bool
public function validate(): void
public function getFilteredParam(string $name, mixed $default = null): mixed

// JSON access
public function getJSON(string $name, bool $assoc = true): array|object
public function getJSONAssoc(string $name): array
public function getJSONObject(string $name): object

// URL building
public function buildURL(array $params = array(), string $dispatcher = ''): string
public function buildRefreshURL(array $params = array(), array $exclude = array()): string
public function getRefreshParams(array $params = array(), array $exclude = array()): array
public function createRefreshParams(): Request_RefreshParams
public function getBaseURL(): string
public function setBaseURL(string $url): Request
public function getDispatcher(): string
public function getExcludeParams(): array
public function getCurrentURL(): string

// URL comparison
public function createURLComparer(string $sourceURL, string $targetURL, array $limitParams = array()): Request_URLComparer

// Accept headers
public static function getAcceptHeaders(): array
public static function parseAcceptHeaders(): Request_AcceptHeaders

// Response helpers
public static function sendJSON(array|string $data): void
public static function sendJSONAndExit(array|string $data): void
public static function sendHTML(string $html): void
public static function sendHTMLAndExit(string $html): void
```

---

## `RequestParam`

**File:** `src/Request/RequestParam.php`

A registered request parameter with chainable validation and filter configuration.

### Constants — Validation Types

| Constant | Value |
|---|---|
| `VALIDATION_TYPE_NONE` | `'none'` |
| `VALIDATION_TYPE_NUMERIC` | `'numeric'` |
| `VALIDATION_TYPE_INTEGER` | `'integer'` |
| `VALIDATION_TYPE_REGEX` | `'regex'` |
| `VALIDATION_TYPE_ALPHA` | `'alpha'` |
| `VALIDATION_TYPE_ALNUM` | `'alnum'` |
| `VALIDATION_TYPE_ENUM` | `'enum'` |
| `VALIDATION_TYPE_ARRAY` | `'array'` |
| `VALIDATION_TYPE_CALLBACK` | `'callback'` |
| `VALIDATION_TYPE_URL` | `'url'` |
| `VALIDATION_TYPE_VALUESLIST` | `'valueslist'` |
| `VALIDATION_TYPE_JSON` | `'json'` |

### Constants — Filter & Value Types

| Constant | Value |
|---|---|
| `FILTER_TYPE_CALLBACK` | `'callback'` |
| `FILTER_TYPE_CLASS` | `'class'` |
| `VALUE_TYPE_STRING` | `'string'` |
| `VALUE_TYPE_LIST` | `'ids_list'` |

### Constants — Error Codes

| Constant | Value |
|---|---|
| `ERROR_UNKNOWN_VALIDATION_TYPE` | `16301` |
| `ERROR_INVALID_FILTER_TYPE` | `16303` |
| `ERROR_INVALID_FILTER_CLASS` | `16304` |

### Methods

```php
public function __construct(Request $request, string $paramName)

// Validation presets
public function setNumeric(): self
public function setInteger(): self
public function setRegex(string $regex): self
public function setURL(): self
public function setAlpha(): self
public function setAlnum(): self
public function setEnum(): self
public function setValuesList(array $values): self
public function setArray(): self
public function setJSON(): self
public function setJSONObject(): self
public function setBoolean(): self
public function setMD5(): self
public function setIDList(): self
public function setAlias(): self
public function setNameOrTitle(): self
public function setLabel(): self
public function setCallback(callable $callback, array $args = array()): self
public function setValidation(string $type, array $params = array()): self

// Value access
public function get(mixed $default = null): mixed
public function getInt(int $default = 0): int
public function getString(string $default = ''): string
public function getBool(bool $default = false): bool
public function getFloat(float $default = 0.0): float
public function validate(mixed $value): mixed
public function isList(): bool
public function getName(): string

// Requirement
public function makeRequired(): RequestParam
public function isRequired(): bool

// Filters
public function addFilter(string $type, mixed $params = null): self
public function addFilterTrim(): self
public function addStringFilter(): self
public function addCallbackFilter(mixed $callback, array $params = array()): self
public function addStripTagsFilter(string $allowedTags = ''): self
public function addStripWhitespaceFilter(): self
public function addCommaSeparatedFilter(bool $trimEntries = true, bool $stripEmptyEntries = true): self
public function addHTMLSpecialcharsFilter(): self
```

---

## `Request_RefreshParams`

**File:** `src/Request/RefreshParams.php`
**Implements:** `Interface_Optionable`

Builds a filtered copy of the current request parameters for "refresh" URLs, with exclusion rules and overrides.

### Methods

```php
public function getDefaultOptions(): array
public function setExcludeSessionName(bool $exclude = true): Request_RefreshParams
public function setExcludeQuickform(bool $exclude): Request_RefreshParams
public function excludeParamByName(string $paramName): Request_RefreshParams
public function excludeParamByCallback(callable $callback): Request_RefreshParams
public function excludeParamsByName(array $paramNames): Request_RefreshParams
public function overrideParam(string $paramName, mixed $paramValue): Request_RefreshParams
public function overrideParams(array $params): Request_RefreshParams
public function getParams(): array
public function isExcluded(string $paramName, mixed $paramValue): bool
```

---

## `Request_URLComparer`

**File:** `src/Request/URLComparer.php`

Compares two URLs for equality, optionally limiting comparison to specific query parameters.

### Methods

```php
public function __construct(Request $request, string $sourceURL, string $targetURL)
public function isMatch(): bool
public function addLimitParam(string $name): Request_URLComparer
public function addLimitParams(array $names): Request_URLComparer
public function setIgnoreFragment(bool $ignore = true): Request_URLComparer
```

---

## `Request_AcceptHeaders`

**File:** `src/Request/AcceptHeaders.php`

Parses the HTTP `Accept` header into structured entries.

### Methods

```php
public function __construct()
public function getMimeStrings(): array  // string[]
```

---

## `Request\AcceptHeader`

**File:** `src/Request/AcceptHeader.php`
**Implements:** `ArrayAccess<string, mixed>`

A single parsed `Accept` header entry with MIME type, quality, and parameters.

### Methods

```php
public function __construct(string $type, int $position, array $parameters, float $quality)
public function getMimeType(): string
public function getPosition(): int
public function getParameters(): array
public function getQuality(): float
public function offsetExists(mixed $offset): bool
public function offsetGet(mixed $offset): array|int|float|string
public function offsetSet(mixed $offset, mixed $value): void
public function offsetUnset(mixed $offset): void
```

---

## Filter Classes

**Base:** `Request_Param_Filter` (abstract) — `src/Request/Param/Filter.php`
**Implements:** `Interface_Optionable` (via `Traits_Optionable`)

```php
public function __construct(RequestParam $param)
public function filter(mixed $value): mixed  // delegates to protected _filter()
```

### Concrete Filters

| Class | File | Behavior |
|---|---|---|
| `Request_Param_Filter_Boolean` | `Filter/Boolean.php` | Converts `'yes'`, `'true'`, `true` → `true` |
| `Request_Param_Filter_CommaSeparated` | `Filter/CommaSeparated.php` | Splits string by comma; options: `trimEntries`, `stripEmptyEntries` |
| `Request_Param_Filter_String` | `Filter/String.php` | Casts scalar to string; returns `''` for non-scalar |
| `Request_Param_Filter_StripWhitespace` | `Filter/StripWhitespace.php` | Removes all whitespace via `/\s/` regex |

---

## Validator Classes

**Base:** `Request_Param_Validator` (abstract) — `src/Request/Param/Validator.php`
**Implements:** `Interface_Optionable` (via `Traits_Optionable`)

```php
public function __construct(RequestParam $param, bool $subval)
public function validate(mixed $value): mixed  // delegates to protected _validate()
```

### Concrete Validators

| Class | File | Behavior |
|---|---|---|
| `Request_Param_Validator_Alnum` | `Validator/Alnum.php` | Validates `/\A[a-zA-Z0-9]+\z/` |
| `Request_Param_Validator_Alpha` | `Validator/Alpha.php` | Validates `/\A[a-zA-Z]+\z/` |
| `Request_Param_Validator_Array` | `Validator/Array.php` | Ensures value is an array |
| `Request_Param_Validator_Callback` | `Validator/Callback.php` | Invokes user callback; `false` → `null` |
| `Request_Param_Validator_Enum` | `Validator/Enum.php` | Strict `in_array` against allowed values |
| `Request_Param_Validator_Integer` | `Validator/Integer.php` | Validates via `ConvertHelper::isInteger()` |
| `Request_Param_Validator_Json` | `Validator/Json.php` | Validates JSON string; option: `arrays` |
| `Request_Param_Validator_Numeric` | `Validator/Numeric.php` | `is_numeric()` check, auto int/float coercion |
| `Request_Param_Validator_Regex` | `Validator/Regex.php` | Matches scalar against configurable regex |
| `Request_Param_Validator_Url` | `Validator/Url.php` | Parses and validates URL syntax |
| `Request_Param_Validator_Valueslist` | `Validator/Valueslist.php` | Filters array to allowed values |

---

## Exclusion Classes

**Base:** `Request_RefreshParams_Exclude` (abstract) — `src/Request/RefreshParams/Exclude.php`

```php
abstract public function isExcluded(string $paramName, mixed $paramValue): bool
```

| Class | File | Behavior |
|---|---|---|
| `Request_RefreshParams_Exclude_Callback` | `Exclude/Callback.php` | Exclusion via user-provided callable |
| `Request_RefreshParams_Exclude_Name` | `Exclude/Name.php` | Exclusion by exact parameter name match |

---

## `Request_Exception`

**File:** `src/Request/Exception.php`
**Extends:** `AppUtils\BaseException`

No additional public members.
